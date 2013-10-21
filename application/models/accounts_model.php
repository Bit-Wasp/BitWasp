<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accounts Model
 *
 * This class handles the database queries relating to orders.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Accounts
 * @author		BitWasp
 * 
 */
class Accounts_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * Load an account
	 * 
	 * Will determine which columns to select from the database. If $opt['own'] == TRUE,
	 * we get more details than a normal account. Then $identifier is used to
	 * select the information information using either the hash/name/id. If the record is
	 * found, format and load more information. If the user has PGP, then
	 * add that to the returned array if successful. If there's no record, return false.
	 *
	 * @access	public
	 * @param	string
	 * @param	array optional
	 * @return	array / FALSE
	 */	
	public function get($identifier, $opt = array()) {

		if($identifier == NULL) 
			return FALSE;
		
		if(count($opt) == 0) {
			$this->db->select('id, banned, display_login_time, force_pgp_messages, block_non_pgp, login_time, location, register_time, user_name, user_hash, user_role');
		} else if($opt['own'] == TRUE) {
			$this->db->select('id, banned, display_login_time, local_currency, block_non_pgp, force_pgp_messages, login_time, location, register_time, two_factor_auth, user_name, user_hash, user_role');
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
	
	/**
	 * Get PGP Key
	 * 
	 * Load a PGP key based on the $user_id. If the record exists, format 
	 * the fingerprint for display. If the record exists, the results get 
	 * returned as an array. If not, return FALSE;
	 * 
	 * @access	public
	 * @param	int
	 * @return	array / FALSE
	 */		
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
	
	/**
	 * Add PGP Key
	 * 
	 * Add a PGP key to the database. Returns TRUE if successful,
	 * FALSE if unsuccessful.
	 * $config = array(	'user_id' => '...',
	 * 					'fingerprint' => '...',
	 * 					'public_key' => '...');
	 * @access	public
	 * @param	array
	 * @return	bool
	 */	
	public function add_pgp_key($config) {
		return ($this->db->insert('pgp_keys', $config)) ? TRUE : FALSE;
	}
	
	/**
	 * Delete PGP key.
	 * 
	 * Delete a PGP public key for $user_id.
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */		
	public function delete_pgp_key($user_id) {
		$this->db->where('user_id', $user_id);
		
		if($this->db->delete('pgp_keys') == TRUE) {
			// When deleting the PGP key, 
			$changes = array('two_factor_auth' => '0',
							 'force_pgp_messages' => '0',
							 'block_non_pgp' => '0');
			$this->update($changes);
			return TRUE;
		}
			
		return FALSE;
	}
	
	/**
	 * Replace PGP key.
	 * 
	 * Replace a PGP public key for $user_id. Return TRUE if successful,
	 * FALSE on failure.
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */		
	public function replace_pgp_key($user_id, $data) {
		$this->db->where('user_id', $user_id);
		return ($this->db->update('pgp_keys', array('public_key' => $data['public_key'],
												    'fingerprint' => $data['fingerprint'])) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Toggle Ban
	 * 
	 * Change the banned setting for $user_id. Can be set by the Autorun 
	 * script, or manually toggled by an Admin.
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @return	bool
	 */	
	public function toggle_ban($user_id, $value) {
		$this->db->where('id', $user_id);
		return ($this->db->update('users', array('banned' => $value)) == TRUE) ? TRUE : FALSE;
	}		
	
	/**
	 * Update 
	 * 
	 * Updates a user row with the indexes supplied in $changes. Make
	 * the changes to the table.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */		
	public function update($changes) {
		$this->db->where('id', $this->current_user->user_id);
		return ($this->db->update('users', $changes)) ? TRUE : FALSE;
	}

};

/* End of file Accounts_model.php */
