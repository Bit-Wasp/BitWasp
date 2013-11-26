<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * General Model
 *
 * General model with some small functions.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	General
 * @author		BitWasp
 */
class General_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */	
	public function __construct() {}

	/**
	 * Check Unique Entry
	 * 
	 * This function will check that the supplied $entry is unique in
	 * the $table $column. Returns a boolean indicating Success/Failure.
	 *
	 * @param	string	$table
	 * @param	string	$column
	 * @param	string	$hash
	 * @return	bool
	 */
	public function check_unique_entry($table, $column, $entry){
		$this->db->where($column, $entry);
		$query = $this->db->get($table);
		return ($query->num_rows() < 1) ? TRUE : FALSE;
	}

	/**
	 * Get Stale Users
	 * 
	 * Stale users are any user who has not logged in for a defined 
	 * period of time. If the login time is less than $threshold then
	 * the user is included in the list. Does not include banned members.
	 *
	 * @param	int	$threshold
	 * @return	array/FALSE
	 */
	public function get_stale_users($threshold) {
		$this->db->where('login_time <', $threshold);
		$this->db->where('banned !=', '1');
		$query = $this->db->get('users');
		if($query->num_rows() > 0){
			$array = $query->result_array();
			$results = array();
			foreach($array as $user){
				if($user['login_time'] == '0') {
					if($user['register_time'] < $threshold)
						array_push($results, $user);
				} else {
					array_push($results, $user);
				}
			}
			return $results;
		}
		return FALSE;
	}

	/**
	 * Rows Before Time
	 * 
	 * Return rows in $table with a timestamp before $time.
	 *
	 * @param	string	$table
	 * @param	int	$time
	 * @return	bool
	 */
	public function rows_before_time($table, $time) {
		$this->db->where("time <", "$time");
		$query = $this->db->get($table);
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE ;
	}
	
	/**
	 * Drop ID
	 * 
	 * Drop a row by the specified $table and $id.
	 *
	 * @param 	string	$table
	 * @param	int	$id
	 * @return	bool
	 */
	public function drop_id($table, $id) {
		$this->db->where('id', "$id");
		return ($this->db->delete($table) == TRUE) ? TRUE : FALSE ;
	}
	
	/**
	 * Count Entries
	 * 
	 * Count the total number of entries in a table, specified by $table.
	 *
	 * @param	string	$table
	 * @return	int/FALSE
	 */
	public function count_entries($table) {
		return $this->db->count_all($table);
	}

	/**
	 * Count Transactions 
	 * 
	 * Count the total number of bitcoin transactions.
	 *
	 * @return	int
	 */	
	public function count_transactions() {
		$this->db->select('id');
		$this->db->where('address !=', '[payment]');
		$query = $this->db->get('pending_txns');
		return $query->num_rows();
	}
	
	/**
	 * Count Orders
	 * 
	 * Count the total number of orders on record.
	 *
	 * @return	int
	 */
	public function count_orders() {
		$this->db->select('id');
		$this->db->where('address', '[payment]');
		$query = $this->db->get('pending_txns');
		return $query->num_rows()/2;
	}

	/**
	 * Count Unread Messages
	 * 
	 * Count the number of unread messages for the current user.
	 *
	 * @return	int
	 */
	public function count_unread_messages() {
		$this->db->select('id');
		$this->db->where('to', $this->current_user->user_id);
		$this->db->where('viewed', '0');
		$query = $this->db->get('messages');
		return $query->num_rows();
	}
	
	/**
	 * Count New Orders
	 * 
	 * Count orders at progress=1 for the currently logged in user.
	 *
	 * @return	int
	 */	
	public function count_new_orders() {
		$this->db->select('id');
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		$this->db->where('progress', '1');
		$query = $this->db->get('orders');
		return $query->num_rows();
	}
		
	/**
	 * Locations List
	 * 
	 * List all the locations stored in the table. Return an array whether
	 * true or false.
	 *
	 * @return	array
	 */
	public function locations_list() {
		$query = $this->db->get('country_codes');
		return ($query->num_rows() > 0) ? $query->result_array() : array(); 
	}
	
	/**
	 * Location by ID
	 * 
	 * Load the name of the location specified by $id. Returns a string
	 * if successful or FALSE on failure.
	 *
	 * @param	int	$id
	 * @return	string/FALSE
	 */
	public function location_by_id($id){
		if($id == 'worldwide')
			return 'Worldwide';
			
		$this->db->select('country')
		         ->where('id', $id);
		$query = $this->db->get('country_codes');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['country'];
		} 
		
		return FALSE;
	}
};

