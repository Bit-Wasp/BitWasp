<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class General_model extends CI_Model {

	public function __construct() {}

	// Test to see if the hash is unique in the table/column.
	public function check_unique_entry($table, $column, $hash){
		$this->db->where($column, $hash);
		$query = $this->db->get($table);
		if($query->num_rows() < 1){
			// Success; hash is unique.
			return TRUE;
		} else {
			// Failure; hash is not unique.
			return FALSE;
		}
	}
	
	// Count the number of entries in a table.
	public function count_entries($table) {
		return $this->db->count_all($table);
	}

	public function count_transactions() {
		$this->db->select('id');
		$this->db->where('address !=', '[payment]');
		$query = $this->db->get('pending_txns');
		return $query->num_rows();
	}
	
	public function count_orders() {
		$this->db->select('id');
		$this->db->where('address', '[payment]');
		$query = $this->db->get('pending_txns');
		return $query->num_rows();
	}

	public function count_unread_messages() {
		$this->db->select('id');
		$this->db->where('to', $this->current_user->user_id);
		$this->db->where('viewed', '0');
		$query = $this->db->get('messages');
		return $query->num_rows();
	}
	
	public function count_new_orders() {
		$this->db->select('id');
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		$this->db->where('progress', '1');
		$query = $this->db->get('orders');
		return $query->num_rows();
	}
		
	
	// Load ID and country names.
	public function locations_list() {
		$query = $this->db->get('country_codes');
		if($query->num_rows() > 0) 
			return $query->result_array();
			
		return array();
	}
	
	// Load location name by id. 
	public function location_by_id($id){
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

