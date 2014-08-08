<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Messages Library
 *
 * Used to prepare messages for input to the table or output.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Messages
 * @author        BitWasp
 */
class Bw_messages
{

    /**
     * CI
     */
    public $CI;
    /**
     * This is set by the site configuration, determines whether RSA encryption
     * of message is done.
     */
    public $encrypt_private_messages;
    /**
     * Message Password
     *
     * This is used to store the users message password.
     */
    protected $message_password;


    /**
     * @var
     */
    public $for_email;

    /**
     * Constructor
     *
     * Load the CodeIgniter framework, and the OpenSSL/GPG libraries,
     * and the users model.
     */
    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('openssl');
        $this->CI->load->library('form_validation');
        $this->CI->load->library('gpg');
        $this->CI->load->model('users_model');
    }

    /**
     * Prepare Input
     *
     * Prepares a message for storage in the database. If $system is NULL,
     * then the message information is taken from POST data (ie, submitted
     * using the messages form). Otherwise, the message is taken from the
     * system array.
     *
     * Checks if the message is encrypted, or if the vendor has enabled
     * server side encryption of GPG messages.
     *
     * Prepares the content columns data - a JSON array containing the
     * message, subject, and sender ID. If encrypted private messages are
     * enabled, this message content will be encrypted with the receipients
     * RSA key to securely store the data.
     * Finally content is base64 encoded.
     *
     * @param   array $data
     * @param   array $system
     * @return  array
     */
    public function prepare_input($data, $system = NULL)
    {
        // If the message is being sent by the system, take what you're given.
        if (is_array($system)) {
            // If it's a system message, load from the array.
            $username = $system['username'];
            $subject = $system['subject'];
            $message = $system['message'];
            $remove_on_read = '0';
        } else {
            // Otherwise load data from POST
            $username = $this->CI->input->post('recipient');
            $subject = $this->CI->input->post('subject');
            if ($subject == '')
                $subject = '[no subject]';
            $message = $this->CI->input->post('message');
            $remove_on_read = ($this->CI->input->post('delete_on_read') == '1') ? '1' : '0';
        }

        // Load the account the message is being sentto.
        $to = $this->CI->users_model->get(array('user_name' => $username));

        $from = $data['from'];
        $content = array('from' => $from,
            'subject' => $subject,
            'message' => $message);

        $pgp_encrypted = $this->CI->form_validation->check_pgp_encrypted($content['message']);

        // If the message isn't already encrypted with PGP..
        if ($pgp_encrypted == false) {
            // If the recipient has forced it,
            // encrypt the message with the recipients public key.
            // This only happens if the recipient has NOT blocked non-pgp messages.
            if ($to['force_pgp_messages'] == '1') {
                $this->CI->load->model('accounts_model');
                $pgp = $this->CI->accounts_model->get_pgp_key($to['id']);
                $content['message'] = $this->CI->gpg->encrypt($pgp['fingerprint'], $content['message']);
                $pgp_encrypted = true;
            }
        }
        $this->for_email = $content['message'];

        // JSON encode the content array, and encrypt it if able.
        $content = json_encode($content);

        // Try encrypt the message if the user has a public key.
        switch ($to['public_key'] !== '') {
            case true:
                // Create a key for AES, and encrypt content
                $key = openssl_random_pseudo_bytes(32);
                $encrypt = $this->encrypt($content, $key);
                if ($encrypt) {
                    // Message successfully encrypted, store details
                    $rsa = '1';
                    $iv = $encrypt['aes_iv'];
                    $content = $encrypt['aes_message'];
                    $encrypted_key = $this->CI->openssl->encrypt($key, $to['public_key']);
                    break;
                }
            // Intentionally not breaking if the encryption fails
            default:
                // Content is untouched if encryption failed. Set defaults for no encryption.
                $rsa = '0';
                $encrypted_key = '';
                $iv = '';
                break;
        }

        $hash = $this->CI->general->unique_hash('messages', 'hash');

        $results = array('to' => $to['id'],
            'content' => base64_encode($content),
            'hash' => $hash,
            'remove_on_read' => $remove_on_read,
            'rsa_encrypted' => $rsa,
            'aes_iv' => $iv,
            'aes_key' => $encrypted_key,
            'encrypted' => (($pgp_encrypted) ? '1' : '0'),
            'time' => time()
        );

        return $results;
    }

    /**
     * Encrypt
     *
     * Take a $plaintext message, and a suitable key (256bit), and encrypt the
     * message. Returns an array containing the message, and the IV, both as
     * binary data.
     * @param $plaintext
     * @param $key
     * @return array|bool
     */
    public function encrypt($plaintext, $key)
    {
        $iv = mcrypt_create_iv(32);

        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        if (mcrypt_generic_init($cipher, $key, $iv) != -1) {
            $cipherText = mcrypt_generic($cipher, $plaintext);
            mcrypt_generic_deinit($cipher);

            return array('aes_iv' => $iv,
                'aes_message' => $cipherText); //base64 encoded later
        }
        return false;
    }

    /**
     * Prepare Output
     *
     * This function prepares database responses into a parsable array.
     * Used to display the inbox, or a single message.
     *
     * The content JSON string (which may be RSA encrypted) is base64 decoded
     * and decrypted if necessary.
     * $messages is an array containing all the messages. Loop through each
     * and prepare for output.
     *
     * @param        array $messages
     * @return        array
     */
    public function prepare_output($messages = NULL)
    {
        $this->CI->load->model('users_model');
        $this->CI->load->model('accounts_model');

        // If there are no messages, return false.
        if ($messages == NULL)
            return false;

        $key_data = $this->CI->users_model->message_data(array('user_hash' => $this->CI->current_user->user_hash));

        $results = array();
        $senders = array();

        // Loop through messages, decoding, decrypting the content.
        foreach ($messages as $message) {
            $content = base64_decode($message['content']);
            if ($message['rsa_encrypted'] == '1') {
                if ($this->CI->current_user->message_password == NULL) {
                    $this->CI->session->set_userdata('before_msg_pin', uri_string());
                    redirect('message/pin');
                    // redirect to pin page.
                }
                // decrypt AES key with openssl
                $aes_key = $this->CI->openssl->decrypt($message['aes_key'], $key_data['private_key'], $this->CI->current_user->message_password);
                $content = trim($this->decrypt($content, $aes_key, $message['aes_iv']));
            }

            $content = json_decode($content);

            // Build up vendors to save multiple queries.
            if (!isset($senders[$content->from]))
                $senders[$content->from] = $this->CI->accounts_model->get(array('id' => $content->from));

            $res = array('encrypted' => $message['encrypted'],
                'from_id' => $content->from,
                'hash' => $message['hash'],
                'id' => $message['id'],
                'message' => $content->message,
                'remove_on_read' => $message['remove_on_read'],
                'subject' => $content->subject,
                'time' => $message['time'],
                'time_f' => $this->CI->general->format_time($message['time']),
                'viewed' => $message['viewed']);

            unset($message);
            unset($content);
            array_push($results, $res);
            unset($res);
        }

        foreach ($results as &$res) {
            $res['from'] = $senders[$res['from_id']];
        }

        return $results;
    }

    /**
     * Decrypt
     *
     * Accept a $ciphertext, $key, and $iv, all as binary data. Returns
     * decrypted plaintext if successful, otherwise returns false.
     *
     * @param $ciphertext
     * @param $key
     * @param $iv
     * @return bool|string
     */
    public function decrypt($ciphertext, $key, $iv)
    {
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        if (mcrypt_generic_init($cipher, $key, $iv) != -1) {
            $plaintext = mdecrypt_generic($cipher, $ciphertext);
            mcrypt_generic_deinit($cipher);

            return $plaintext;
        }
        return false;
    }
}

;

/* End of file Bw_messages.php */
/* Location: application/libraries/Bw_messages.php */