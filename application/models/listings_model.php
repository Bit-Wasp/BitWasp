<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Listings Model
 *
 * This class handles the database queries relating to listings.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Listings
 * @author		BitWasp
 * 
 */

class Listings_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @see 	Models/Accounts_Model
	 */	
	public function __construct() {
		$this->load->model('accounts_model');
	}
	
	/**
	 * Add
	 * 
	 * Add a new listing to the database.
	 *
	 * @access	public
	 * @param	string	$properties
	 * @return	bool
	 */					
	public function add($properties) {
		return ($this->db->insert('items', $properties) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Delete
	 * 
	 * Delete a listing, where the listing belongs to the current user.
	 *
	 * @access	public
	 * @param	string	$hash
	 * @return	bool
	 */					
	public function delete($hash) {
		$this->db->where('hash', $hash);
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		return ($this->db->delete('items') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Get 
	 * 
	 * Load an item if it belongs to the current user.
	 *
	 * @access	public
	 * @param	string	$hash
	 * @return	array/FALSE
	 */					
	public function get($hash) {
		$this->load->model('currencies_model');
	
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		$this->db->where('hash', $hash);
		$query = $this->db->get('items');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$row['description'] = str_replace('<br />','',$row['description']);
			$row['description_f'] = nl2br($row['description']);
			$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
			$row['currency'] = $this->currencies_model->get($row['currency']);
			
			$row['price_b'] = $row['price']/$row['currency']['rate'];
			
			$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
			$row['price_l'] = (float)round(($row['price_b']*$local_currency['rate']), 8, PHP_ROUND_HALF_UP);
			$row['price_f'] = $local_currency['symbol'].' '.$row['price_l'];

			$row['main_image_f'] = $this->images_model->get($row['main_image']);
			$row['images'] = $this->images_model->by_item($hash);
			return $row;
		}
		
		return FALSE;
	}
	
	/**
	 * Update
	 * 
	 * Update an item with an array of changes (index as column, val as val)
	 *
	 * @access	public
	 * @param	string	$item_hash
	 * @param	array	$changes
	 * @return	bool
	 */					
	public function update($item_hash, $changes) {
		$this->db->where('hash', $item_hash);
		$this->db->where('vendor_hash', $this->current_user->user_hash);		
		return ($this->db->update('items', $changes)) ? TRUE : FALSE;
	}	

};

/* End of file listings_model.php */
