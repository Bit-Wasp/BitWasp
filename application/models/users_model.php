<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users Model
 *
 * This class handles the database queries relating to users.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Users
 * @author		BitWasp
 * 
 */
class Users_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */	
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add User.
	 * 
	 * Add a user to the table. Use prepared statements..
	 *
	 * @access	public
	 * @param	array
	 * @param	string
	 * @return	boolean
	 */					
	public function add($data, $token_info = NULL) {
		$sql = "INSERT INTO bw_users (user_name, password, salt, user_hash, user_role, register_time, public_key, private_key, location) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$query = $this->db->query($sql, array($data['user_name'],$data['password'],$data['salt'], $data['user_hash'], $data['user_role'], time(), $data['public_key'], $data['private_key'], $data['location'])); 
		if($query) {
			if($token_info !== FALSE)			$this->delete_registration_token($token_info['id']);
			
			return TRUE;
		} 
		return FALSE;
		
	}
	
	/**
	 * Get
	 * 
	 * Get a user, based on $user['user_hash'], $user['id'], $user['user_name']
	 *
	 * @access	public
	 * @param	array	$user
	 * @return	array/FALSE
	 */					
	public function get(array $user) {

		if (isset($user['user_hash'])) {
			// Duplicate the select statement to prevent weird errors later on.
			$this->db->select('id, banned, user_hash, user_name, local_currency, user_role, salt, force_pgp_messages, two_factor_auth, entry_paid');			
			$query = $this->db->get_where('users', array('user_hash' => $user['user_hash']));
		} elseif (isset($user['id'])) {
			$this->db->select('id, banned, user_hash, user_name, local_currency, user_role, salt, force_pgp_messages, two_factor_auth, entry_paid');
			$query = $this->db->get_where('users', array('id' => $user['id']));
		} elseif (isset($user['user_name'])) {
			$this->db->select('id, banned, user_hash, user_name, local_currency, user_role, salt, force_pgp_messages, two_factor_auth, entry_paid');			
			$query = $this->db->get_where('users', array('user_name' => $user['user_name']));
		} else {
			return FALSE; //No suitable field found.
		}
		
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}
	

	/**
	 * Deletes
	 * 
	 * Deletes a user account. 
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */				
	public function delete($user_hash) {
		$user = $this->get(array('user_hash' => $user_hash));
		if($user == FALSE)
			return FALSE;
			
		$this->db->where('user_hash', $user_hash);
		$delete_user = $this->db->delete('users');
		
		return ($delete_user == TRUE) ? TRUE : FALSE;
		//$this->db->where('user_hash', $user_hash);
		//$delete_user = $this->db->delete('users');
		
		//$this->db->where('user_hash', $user_hash);
		//$delete_user = $this->db->delete('users');
		
	}	
	
	/**
	 * Message Data
	 * 
	 * Load information regarding the users RSA encryption keys.
	 *
	 * @access	public
	 * @param	array	$user
	 * @return	array/FALSE
	 */					
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
			
			$results = array('salt' => $row['salt'],
							 'public_key' => base64_decode($row['public_key']),
							 'private_key' => base64_decode($row['private_key']));
			return $results;
		}
			
		return FALSE;
	}
	
	/**
	 * Check Password.
	 * 
	 * Returns userdata when a users username, password and salt are entered correctly.
	 *
	 * @access	public
	 * @param	string	$user_name
	 * @param	string	$salt
	 * @param	string	$password
	 * @return	array/FALSE
	 */					
	public function check_password($user_name, $password) {
		$this->db->select('id')
				 ->where('user_name',$user_name)
				 ->where('password', $password);
				 
		$query = $this->db->get('users');
		
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}
	
	/**
	 * Add Registration Token
	 * 
	 * Add an array describing a registration token.
	 *
	 * @access	public
	 * @param	string $token
	 * @return	boolean
	 */					
	public function add_registration_token($token) {
		return ($this->db->insert('registration_tokens', $token) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * List Registration Tokens
	 * 
	 * This function loads a list of the current registration tokens 
	 * on record.
	 * 
	 * @return		array/FALSE
	 */
	public function list_registration_tokens() {
		$query = $this->db->get('registration_tokens');
		if ($query->num_rows() > 0) {
			$array = $query->result_array();
			foreach($array as &$entry) {
				$entry['role'] = $this->general->role_from_id($entry['user_type']);
			}
			return $array;
		}
		return FALSE;
	}
	
	/**
	 * Check Registration Token
	 * 
	 * This function checks whether a registration token is valid or now.
	 * Returns info about the token on success, FALSE on failure.
	 * 
	 * @param	string	$token
	 * @return	array/FALSE
	 */
	public function check_registration_token($token) {
		
		$this->db->select('id, user_type, token_content, entry_payment');
		$query = $this->db->get_where('registration_tokens', array('token_content' => $token));
		
		if($query->num_rows() > 0) {
			$info = $query->row_array();
			$info['user_type'] = array( 'int' => $info['user_type'],
										'txt' => $this->general->role_from_id($info['user_type']));
			
			return $info;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Delete Registration Token
	 * 
	 * Delete a registration token as specified by $id.
	 * 
	 * @param	int	$id
	 * @return	boolean
	 */
	public function delete_registration_token($id) {
		return ($this->db->delete('registration_tokens', array('id' => $id)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Login
	 * 
	 * Set the users login time (user specified by $id)
	 * @param	int $id
	 * @return	boolean
	 */
	public function set_login($id) {
		$change = array('login_time' => time());
		
		$this->db->where('id', $id);
		$query = $this->db->update('users', $change);
		return ($query) ? TRUE : FALSE;
	}
	
	/**
	 * Set Entry Fee
	 * 
	 * This function is used to record a figure that the user must pay
	 * in order to register on the website. $info['user_hash'], $info['amount']
	 * and $info['bitcoin_address'] must be supplied. 
	 * 
	 * @param	array	info
	 * @return	boolean
	 */
	 public function set_entry_payment($info) {
		 $info['time'] = time();
		 return ($this->db->insert('entry_payment', $info)) ? TRUE : FALSE;
	 }
	 
	/** 
	 * Set Entry Paid
	 * 
	 * This function is run when entry is free or when the user has
	 * paid for their site. If this entry is not set in the table, when
	 * they try to log in, users will be directed to the intermediary 
	 * payment page.
	 * 
	 * @param	string	$user_hash
	 * @return	boolean
	 */
	public function set_entry_paid($user_hash) {
		$array = array('entry_paid' => '1');
		$this->db->where('user_hash', $user_hash);
		return ($this->db->update('users', array('entry_paid' => '1'))) ? TRUE : FALSE;
	}
	
	/**
	 * Set Payment Address
	 * 
	 * If the bitcoin daemon is offline, when they register, we'll need
	 * to add a proper address.
	 * 
	 * @param	string	$user_hash
	 * @param	string	$address
	 * return	boolean
	 */
	public function set_payment_address($user_hash, $address) {
		if($address == NULL)
			return FALSE;
		$this->db->where('user_hash', $user_hash);
		return ($this->db->update('entry_payment', array('bitcoin_address' => $address))) ? TRUE : FALSE;
	}
	
	/**
	 * Get Payment Address Owner
	 * 
	 * Function to return the user has associated with the particular
	 * $address. Called in the bw_bitcoin->walletnotify() function to see
	 * if the address belongs to a fee's account. If so, it will check the
	 * amount received on that address. When it's >= to the required amount
	 * the user can log in. Excesses are added to the user account!
	 * 
	 * @param	string	$address
	 * return	string/FALSE
	 */
	public function get_payment_address_owner($address) {
		$this->db->select('user_hash');
		$this->db->where('bitcoin_address', $address);
		$query = $this->db->get('entry_payment');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return($row['user_hash']);
		}
		return FALSE;
	}
	
	/**
	 * Get Entry Payment 
	 * 
	 * This function will load the details of the required entry
	 * payment for this user. 
	 * 
	 * @param	string	$user_hash
	 * @return 	array/FALSE
	 */
	 public function get_entry_payment($user_hash) { 
		 $this->db->where('user_hash', $user_hash);
		 $query = $this->db->get('entry_payment');
		 return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	 }
	 
	 /**
	  * Delete Entry Payment
	  * 
	  * This function deletes the record of the users entry payment once
	  * they have registered their account.
	  * 
	  * @param	string	$user_hash
	  * @return	boolean
	  */
	public function delete_entry_payment($user_hash) {
		 $this->db->where('user_hash', $user_hash);
		 return ($this->db->delete('entry_payment') == TRUE) ? TRUE : FALSE;
	} 
	
	/**
	 * List Users
	 * 
	 * Display a list of users. Can supply a list of parameters, 
	 * 
	 * @param	array	(opt)$params
	 * @return	array/FALSE
	 */
	public function user_list($params = array()) {
		if(isset($params['order_by'])) {
			$this->db->order_by("{$params['order_by']}", "{$params['list']}");
			unset($params['order_by']);
			unset($params['list']);
		}	
		
		foreach($params as $column => $value) {
			$this->db->where("{$column}", "{$value}");
		}
		
		$query = $this->db->get('users');
		if($query->num_rows() > 0) {
			$results = array();
			foreach($query->result_array() as $result) {
				$tmp = $result;
				$tmp['register_time_f'] = $this->general->format_time($tmp['register_time']);
				$tmp['login_time_f'] = $this->general->format_time($tmp['login_time']);
				array_push($results, $tmp);
			}
			return $results;
		}
		return FALSE;
	}
	
	/**
	 * Search User
	 * 
	 * Search for a user specified by $user_name. Returns an array with information
	 * if the search is successful, otherwise returns FALSE. On failure,
	 * FALSE will make the admin user list appear again, and declare the
	 * user was not found.
	 * 
	 * @param	string $user_name
	 * @return	array/FALSE
	 */
	public function search_user($user_name) {
		$this->db->like('user_name',$user_name);
		$query = $this->db->get('users');
		if($query->num_rows() > 0) {
			$users = array();
			foreach($query->result_array() as $result) {
				$user = $result;
				$user['register_time_f'] = $this->general->format_time($user['register_time']);
				$user['login_time_f'] = $this->general->format_time($user['login_time']);
				array_push($users, $user);
			}
			return $users;
		}
		return FALSE;
	}
};

/* End of File: Users_Model.php */
