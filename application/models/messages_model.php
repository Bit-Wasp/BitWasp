<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Messages Model
 *
 * This class handles the database queries relating to messages.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Messages
 * @author		BitWasp
 * 
 */

class Messages_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Libraries/Current_User
	 * @see		Libraries/Bw_Messages
	 */		
	public function __construct() {	
		$this->load->library('current_user');
		$this->load->library('bw_messages');
		parent::__construct();
	}
	
	// Load a specific message.
	public function get($hash) {
		$this->db->select('id, content, hash, viewed, encrypted, viewed, remove_on_read, time')
				 ->where('to', $this->current_user->user_id)
				 ->where('hash', $hash);
				
		$query = $this->db->get('messages');
		if($query->num_rows() > 0)
			return $query->row_array();
			
		return FALSE;
	}
	
	// Load a users inbox.
	public function inbox($limit = 0) {
		$this->db->select('id, content, hash, viewed, encrypted, viewed, remove_on_read, time')
				 ->where('to', $this->current_user->user_id)
		  		 ->order_by('time','desc'); 		

		if($limit !== 0)
			$this->db->limit($limit);

		$query = $this->db->get('messages');
	
		if($query->num_rows() > 0) 
			return $query->result_array();

		return NULL;
	}
	
	// Delete a message, restrict ownership to the currently logged in user.
	public function delete($id) {
		$this->db->where('to', $this->current_user->user_id);
		$this->db->where('id', $id);
		if($this->db->delete('messages') == TRUE)
			return TRUE;
		
		return FALSE;
	}
	
	// Delete all messages.
	public function delete_all() {
		$this->db->where('to', $this->current_user->user_id);
		if($this->db->delete('messages') == TRUE)
			return TRUE;
		
		return FALSE;
	}	
	
	// Function to gather information about a user/message so we can build the send-message form.
	public function reply_info($identifier) {		
		if($identifier == NULL)
			return FALSE;
		
		$this->load->model('accounts_model');
		$message = $this->get($identifier);
		if($message !== FALSE) { 
			$info = $this->bw_messages->prepare_output(array($message));
			$info = $info[0];
			
			$res = array('to_name' => $info['from']['user_name'],
						 'to_id' => $info['from']['id'],
						 'force_pgp_messages' => $info['from']['force_pgp_messages'],
						 'subject' => $info['subject']);
			if(isset($info['from']['pgp'])){
				$res['public_key'] = $info['from']['pgp']['public_key'];		
				$res['fingerprint'] = $info['from']['pgp']['fingerprint'];
				$res['fingerprint_f'] = $info['from']['pgp']['fingerprint'];
			}
		}
		
		$user = $this->accounts_model->get(array('user_hash' => $identifier));
		if($user !== FALSE) {
			$res = array('to_name' => $user['user_name'],
						 'to_id' => $user['id'],
						 'force_pgp_messages' => $user['force_pgp_messages'],
						 'subject' => NULL);
			if(isset($user['pgp'])){
				$res['public_key'] = $user['pgp']['public_key'];
				$res['fingerprint'] = $user['pgp']['fingerprint'];
				$res['fingerprint_f'] = $user['pgp']['fingerprint_f'];
			}
		}
		
		if(isset($res))
			return $res;

		return NULL;
	}
	
	// Add the message to the table.
	public function send($data) {
		if($this->db->insert('messages', $data))
			return TRUE;
	
		return FALSE;
	}
	
	// Set whether the message has been viewed.
	public function set_viewed($id) {
		$update = array('viewed' => '1');
		$this->db->where('id', $id);
		if($this->db->update('messages', $update))
			return TRUE;
		
		return FALSE;
	}
};
