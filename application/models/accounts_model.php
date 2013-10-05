<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// More public attributes of an account. 

class Accounts_model extends CI_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	// Load an account.
	public function get($identifier, $opt = array()) {

		if($identifier == NULL) 
			return FALSE;
		
		if(count($opt) == 0) {
			$this->db->select('id, display_login_time, force_pgp_messages, login_time, location, register_time, user_name, user_hash, user_role');
		} else if($opt['own'] == TRUE) {
			$this->db->select('id, display_login_time, local_currency, force_pgp_messages, login_time, location, register_time, two_factor_auth, user_name, user_hash, user_role');
		}

		if (isset($identifier['user_hash'])) {
			$query = $this->db->get_where('users', array('user_hash' => $identifier['user_hash']));
		} elseif (isset($identifier['id'])) {
			$query = $this->db->get_where('users', array('id' => $identifier['id']));
		} elseif (isset($identifier['user_name'])) {
			$query = $this->db->get_where('users', array('user_name' => $identifier['user_name']));
		} else {
			return FALSE; //No suitable field found.
		}
		
		if($query->num_rows() > 0) {
			$result = $query->row_array();
			$result['user_hash'] = $result['user_hash'];
			$result['register_time_f'] = $this->general->format_time($result['register_time']);
			$result['login_time_f'] = $this->general->format_time($result['login_time']);
			$result['location_f'] = $this->general_model->location_by_id($result['location']);
			if(isset($opt['own']) && $opt['own'] == TRUE)
				$result['currency'] = $this->currencies_model->get($result['local_currency']);
			
			$pgp = $this->get_pgp_key($result['id']);
			if($pgp !== FALSE)
				$result['pgp'] = $pgp;
				
			return $result;
		}
		
		return FALSE;
	}
	
	// Load the public PGP key of a user.
	public function get_pgp_key($user_id) {
		$this->db->select('fingerprint, public_key');
		$query = $this->db->get_where('pgp_keys', array('user_id' => $user_id));
		
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$row['fingerprint_f'] = '0x'.substr($row['fingerprint'], (strlen($row['fingerprint'])-16), 16);
			return $row;
		}
		
		return FALSE;
	}
	
	// Add a PGP key.
	public function add_pgp_key($config) {
		if($this->db->insert('pgp_keys', $config))
			return TRUE;
		
		return FALSE;
	}
	
	// Delete a PGP key.
	public function delete_pgp_key($user_id) {
		$this->db->where('user_id', $user_id);
		if($this->db->delete('pgp_keys') == TRUE) {
			$changes = array('two_factor_auth' => '0',
							 'force_pgp_messages' => '0');
			$this->update($changes);
			return TRUE;
		}
			
			
		return FALSE;
	}
	
	// Update a users account.
	public function update($changes) {
		$this->db->where('id', $this->current_user->user_id);
		if($this->db->update('users', $changes)) 
			return TRUE;
		
		return FALSE;
	}

};

/* End of file Accounts_model.php */
