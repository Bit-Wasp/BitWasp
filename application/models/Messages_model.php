<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Messages Model
 *
 * This class handles the database queries relating to messages.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Messages
 * @author        BitWasp
 *
 */
class Messages_model extends CI_Model
{

    /**
     * Constructor
     *
     * @access    public
     * @see        Libraries/Current_User
     * @see        Libraries/Bw_Messages
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('bw_messages');
    }

    // Load a specific message.

    /**
     * Inbox
     *
     * Load a users inbox. Based on current_user->user_id. User can
     * optionally set a limit for messages.
     *
     * @param        int $limit
     * @return        array / NULL
     */
    public function inbox($limit = 0)
    {
        $this->db->select('id, content, hash, viewed, rsa_encrypted, aes_key, aes_iv, encrypted, viewed, remove_on_read, time')
            ->where('to', $this->current_user->user_id)
            ->order_by('time', 'desc');

        if ($limit !== 0)
            $this->db->limit($limit);

        $query = $this->db->get('messages');

        return ($query->num_rows() > 0) ? $query->result_array() : NULL;

    }

    /**
     * Delete
     *
     * Delete a message, restrict the scope of this function to messages
     * that the currently logged in user has access to.
     *
     * @param        int $id
     * @return        bool
     */
    public function delete($id)
    {
        $this->db->where('to', $this->current_user->user_id);
        $this->db->where('id', $id);
        return $this->db->delete('messages') == TRUE;
    }

    /**
     * Delete Autorun
     *
     * This function is called by an autorun job to delete messages older
     * than a specific age. Unlike delete(), this function can delete any
     * users messages (rather than being restricted to the currently
     * authenticated user)
     *
     * @param    int $id
     * @return    bool
     */
    public function delete_autorun($id)
    {
        $this->db->where('id', "$id");
        return $this->db->delete('messages') == TRUE;
    }

    /**
     * Delete All
     *
     * Delete all messages sent to the current user. Returns TRUE on
     * success, and FALSE on failure.
     *
     * @return        bool
     */
    public function delete_all()
    {
        $this->db->where('to', $this->current_user->user_id);
        return $this->db->delete('messages') == TRUE;
    }

    /**
     * Reply Info
     *
     * This function fathers information about a supplied userhash/messagehash
     * so we can build the send-message form in the case of replying,
     * or directly messaging a user.
     *
     * @param        string $identifier
     * @return        array / NULL
     */
    public function reply_info($identifier)
    {
        if ($identifier == NULL)
            return NULL;

        $this->load->model('accounts_model');
        $message = $this->get($identifier);
        if ($message !== FALSE) {
            $info = $this->bw_messages->prepare_output(array($message));
            $info = $info[0];

            $res = array('to_name' => $info['from']['user_name'],
                'to_id' => $info['from']['id'],
                'force_pgp_messages' => $info['from']['force_pgp_messages'],
                'subject' => $info['subject']);
            if (isset($info['from']['pgp'])) {
                $res['public_key'] = $info['from']['pgp']['public_key'];
                $res['fingerprint'] = $info['from']['pgp']['fingerprint'];
            }
        }

        $user = $this->accounts_model->get(array('user_hash' => $identifier));
        if ($user !== FALSE) {
            $res = array('to_name' => $user['user_name'],
                'to_id' => $user['id'],
                'force_pgp_messages' => $user['force_pgp_messages'],
                'subject' => NULL);
            if (isset($user['pgp'])) {
                $res['public_key'] = $user['pgp']['public_key'];
                $res['fingerprint'] = $user['pgp']['fingerprint'];
            }
        }

        // If the result is set, return it, otherwise, return NULL.
        return (isset($res)) ? $res : NULL;
    }

    /**
     * Get
     *
     * Load a specific message, based on the message hash. Limit
     * messages to those that can be read by the currently logged in user.
     *
     * @param        string $hash
     * @return        array / FALSE
     */
    public function get($hash)
    {
        $query = $this->db->select('id, content, hash, viewed, rsa_encrypted, aes_key, aes_iv, encrypted, viewed, remove_on_read, time')
            ->where('to', $this->current_user->user_id)
            ->where('hash', $hash)
            ->get('messages');

        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Send
     *
     * Send a message to a user.
     * Takes the entered array and inserts it to the database.
     * Array keys can be any of the columns in the database.
     *
     * @param        array $data
     * @return        bool
     */
    public function send($data)
    {
        return ($this->db->insert('messages', $data)) ? TRUE : FALSE;
    }

    /**
     * Set Viewed
     *
     * This function takes the supplied message $id and sets it
     * as viewed.
     *
     * @param        int $id
     * @return        bool
     */
    public function set_viewed($id)
    {
        $this->db->where('id', $id);
        return ($this->db->update('messages', array('viewed' => '1'))) ? TRUE : FALSE;
    }
}

;

/* End of File: Messages_Model.php */
/* Location: application/models/Messages_model.php */