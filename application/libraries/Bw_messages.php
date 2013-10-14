<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Messages Library
 * 
 * Used to prepare messages for input to the table or output.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Messages
 * @author		BitWasp
 */ 
class Bw_messages {

	/**
	 * Message Password
	 * 
	 * This is used to store the users message password.
	 */
	protected $message_password;
	public $CI;
	
	/**
	 * This is set by the site configuration, determines whether RSA encryption
	 * of message is done.
	 */
	public $encrypt_private_messages;
	
	/**
	 * Constructor
	 * 
	 * Load the CodeIgniter framework, and the OpenSSL/GPG libraries,
	 * and the users model.
	 */
	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->library('openssl');
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
	 * @param	array	$data
	 * @param	array	$system
	 */
	public function prepare_input($data, $system = NULL) {
		
		if($system == NULL) {
			$username = $this->CI->input->post('recipient');
			$subject = $this->CI->input->post('subject');
			if($subject == '')
				$subject = '[no subject]';
			$message = $this->CI->input->post('message');
			$remove_on_read = ($this->CI->input->post('delete_on_read') == '1') ? '1' : '0';
		} else if(is_array($system)){
			$username = $system['username'];
			$subject = $system['subject'];
			$message = $system['message'];
			$remove_on_read = '0';
		} 
		
		$from = $data['from'];
		$to = $this->CI->users_model->get(array('user_name' => $username));
				
		$content = array('from' => $from,
						'subject' => $subject,
						'message' => $message);

		$check_encrypted[0] = stripos($message, '-----BEGIN PGP MESSAGE-----'); 
		$check_encrypted[1] = stripos($message, '-----END PGP MESSAGE-----');
		$encrypted = (($check_encrypted[0] !== FALSE) && ($check_encrypted[1] !== FALSE)) ? '1' : '0' ;
		
		// If the message isn't already encrypted with PGP..
		if($encrypted == '0'){
			// If the sender has requested it, or the recipient has forced it,
			// encrypt the message with the recipients public key.
			if( ($this->CI->input->post('pgp_encrypt') == '1') ||
			    ($to['force_pgp_messages'] == '1') ){
					$this->CI->load->library('gpg');
					$this->CI->load->model('accounts_model');
					$pgp = $this->CI->accounts_model->get_pgp_key($to['id']);
					$content['message'] = $this->CI->gpg->encrypt($pgp['fingerprint'], $content['message']);
					$encrypted = '1';
			}		
		}
		
		$content = json_encode($content);
		if($this->encrypt_private_messages)
			$content = $this->CI->openssl->encrypt($content, $to['public_key']);
			
		$content = base64_encode($content);
		$hash = $this->CI->general->unique_hash('messages','hash');
	
		$results = array('to' => $to['id'],
						 'content' => $content,
						 'hash' => $hash,
						 'remove_on_read' => $remove_on_read,
						 'encrypted' => $encrypted,
						 'time' => time()
						 );
						 
		return $results;
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
	 * @param		array	$messages
	 * @return		array
	 */
	public function prepare_output($messages = NULL){
		$this->CI->load->model('users_model');
		$this->CI->load->model('accounts_model');
		
		if($messages == NULL)
			return FALSE;
			
		if($this->encrypt_private_messages == TRUE)
			$this->private_key = $this->CI->users_model->load_message_key('private');
			
		$results = array();			

		foreach($messages as $message) {
			$tmp = base64_decode($message['content']);
			$content = ($this->encrypt_private_messages == TRUE) ? $this->CI->openssl->decrypt($tmp, $this->private_key, $this->current_user->message_password) : $tmp;
			$content = json_decode($content);
			
			$res = array('encrypted' => $message['encrypted'],
						 'from' => $this->CI->accounts_model->get(array('id' => $content->from)),
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
			
		return $results;
	}
	
};

/* End of file Messages.php */
