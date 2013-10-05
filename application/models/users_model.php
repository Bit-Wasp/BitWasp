<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends CI_Model {
	
	public function __construct(){	}

	// Add User
	public function add($data, $token_info = NULL) {
		
		$sql = "INSERT INTO bw_users (user_name, password, salt, user_hash, user_role, register_time, public_key, private_key, location) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$query = $this->db->query($sql, array($data['user_name'],$data['password'],$data['salt'], $data['user_hash'], $data['user_role'], time(), $data['public_key'], $data['private_key'], $data['location'])); 
		if($query){
			if($token_info !== FALSE)
				$this->delete_registration_token($token_info['id']);
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	// Remove User
	public function remove($userHash) {}
	
	// Load a users information, by hash/name/id.
	public function get(array $user) {
		$this->db->select('id, user_hash, user_name, local_currency, user_role, salt, force_pgp_messages, two_factor_auth');

		if (isset($user['user_hash'])) {
			$query = $this->db->get_where('users', array('user_hash' => $user['user_hash']));
		} elseif (isset($user['id'])) {
			$query = $this->db->get_where('users', array('id' => $user['id']));
		} elseif (isset($user['user_name'])) {
			$query = $this->db->get_where('users', array('user_name' => $user['user_name']));
		} else {
			return FALSE; //No suitable field found.
		}
		
		if($query->num_rows() > 0) 
			return $query->row_array();
		
		return FALSE;
	}
	
	// Load a users RSA public and pw-protected private key.
	public function message_data(array $user) {
		$this->db->select('public_key, private_key, salt');

		if (isset($user['user_hash'])) {
			$query = $this->db->get_where('users', array('user_hash' => $user['user_hash']));
		} elseif (isset($user['id'])) {
			$query = $this->db->get_where('users', array('id' => $user['id']));
		} elseif (isset($user['user_name'])) {
			$query = $this->db->get_where('users', array('user_name' => $user['user_name']));
		} else {
			return FALSE; //No suitable field found.
		}

		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$pubkey = base64_decode($row['public_key']);
			$privkey = base64_decode($row['private_key']);
			
			$results = array('salt' => $row['salt'],
							 'public_key' => $pubkey,
							 'private_key' => $privkey);
			return $results;
		}
			
		return FALSE;
	}
	
	// Return valid data when a users username, password, salt are specified. 
	public function check_password($user_name, $salt, $password){
		$this->db->select('id')
				 ->where('user_name',$user_name)
				 ->where('password', $this->general->hash($password, $salt));
		$query = $this->db->get('users');
		
		if($query->num_rows() > 0)
			return $query->row_array();
			
		return FALSE;
	}
	
	// Add a new registration token.
	public function add_registration_token($token) {
		if($this->db->insert('registration_tokens', $token) == TRUE)
			return TRUE;
		return FALSE;
	}
	
	// Load a list of registration tokens.
	public function list_registration_tokens() {
		$query = $this->db->get('registration_tokens');
		if($query->num_rows() > 0)
			return $query->result_array();
			
		return FALSE;
	}
	
	// Check Registration Token
	public function check_registration_token($token) {
		
		$this->db->select('id, user_type, token_content');
		$query = $this->db->get_where('registration_tokens', array('token_content' => $token));
		
		if($query->num_rows() > 0){
			$info = $query->row_array();
			$info['user_type'] = array( 'int' => $info['user_type'],
										'txt' => $this->general->role_from_id($info['user_type']));
			
			return $info;
		} else {
			return FALSE;
		}
	}
	
	// Remove Registration Token
	public function delete_registration_token($id) {
		$delete = $this->db->delete('registration_tokens', array('id' => $id)); 
		return $delete;
	}
	
	// Record the users login time.
	public function set_login($id) {
		$change = array('login_time' => time());
		
		$this->db->where('id', $id);
		$query = $this->db->update('users', $change);
		if($query)
			return TRUE;
			
		return FALSE;
	}
};
