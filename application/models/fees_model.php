<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Fees Model
 *
 * Model to contain database queries for dealing with fee's
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Fees
 * @author		BitWasp
 * 
 */

class Fees_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Fees List
	 * 
	 * This function loads an array containing information about the 
	 * fee's. fee[low] < order_price <= fee[high] will have rate fee[rate]
	 * Returns an array on success, and FALSE on failure.
	 * 
	 * @return	array/FALSE
	 */
	public function fees_list() {
		$this->db->order_by('low','ASC');
		$query = $this->db->get('fees');
		return ($query->num_rows > 0) ? $query->result_array() : FALSE;
	}
	
	/**
	 * Delete Fee
	 * 
	 * This function deletes an entry in the fee's table, based on the $id.
	 * 
	 * @param	int	$id
	 * @return	boolean
	 */
	public function delete_fee($id) {
		$this->db->where('id', $id);
		return ($this->db->delete('fees') == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Add Fee
	 * 
	 * This function adds an entry to the fee's table. $fee is an array
	 * which contains keys for 'low', the lower limit of the range, 
	 * 'high', the upper limit of the range, and 'rate', the range. 
	 * 
	 * @param	array
	 * @return	boolean
	 */
	public function add_fee($fee) {
		return ($this->db->insert('fees', $fee) == TRUE) ? TRUE : FALSE;
	}
	

};

/* End of File: fees_model.php */
