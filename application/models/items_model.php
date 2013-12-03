<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Listings Model
 *
 * Model to contain database queries for dealing with vendor listings.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Items
 * @author		BitWasp
 * 
 */
class Items_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Currencies_Model
	 * @see		Models/Accounts_Model
	 * @see		Models/Images_Model
	 * @see		Models/Users_Model
	 */		
	public function __construct() {
		parent::__construct();
		$this->load->model('currencies_model');
		$this->load->model('accounts_model');
		$this->load->model('images_model');
		$this->load->model('users_model');
	}

	/**
	 * Delete
	 * 
	 * Insert a new row of information about exchange rates.
	 *
	 * @access	public
	 * @param	int	$id
	 * @return	bool
	 */				
	public function delete($id) {
		$this->db->where('id', $id);
		return ($this->db->delete('items') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Get list of items. (need to build in pagination).
	 * 
	 * Display all items which are not hidden, or for a banned user.
	 *
	 * @access	public
	 * @param	array	$opt
	 * @return	bool
	 */					
	public function get_list($opt = array()) {
		$results = array();
		
		$this->db->select('id, hash, price, vendor_hash, currency, hidden, category, name, description, main_image')
				 ->where('hidden !=', '1')
				 ->order_by('add_time ASC');
				 
		// Add on extra options.
		if(count($opt) > 0) {
			
			// If there is a list of item ID's to load..
			if(isset($opt['item_id_list'])) {
				$use_id_list = TRUE;
				$use_id_count = 0;
				if($opt['item_id_list'] !== FALSE) {
					foreach($opt['item_id_list'] as $item_id) {
						($use_id_count == 0) ? $this->db->where('id', $item_id) : $this->db->or_where('id', $item_id);
						$use_id_count++;
					}
				}
				
				// Remove this option to avoid issues with the next step.
				unset($opt['item_id_list']);
			}
			
			foreach($opt as $key => $val) {
				$this->db->where("$key", "$val");
			}
		}
				 
		// Get the list of items.
		$query = $this->db->get('items');

		// Check that if we were meant to load a list that it was successful.
		if(isset($use_id_list) && $use_id_count == 0)
			return FALSE;
		
		if($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {

				// Load vendor information. Skip item if the user is banned.
				$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
				if($row['vendor']['banned'] == '1')
					continue;
					
				// Load information about the items.
				$row['description_s'] = substr(strip_tags($row['description']),0,70);
				if(strlen($row['description']) > 70) $row['description_s'] .= '...';
				$row['main_image'] = $this->images_model->get($row['main_image']);
				$row['currency'] = $this->currencies_model->get($row['currency']);
				$row['price_b'] = round(($row['price']/$row['currency']['rate']), '8', PHP_ROUND_HALF_UP);
				$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
				$price_l = (float)($row['price_b']*$local_currency['rate']);
				$price_l = ($this->current_user->currency['id'] !== '0') ? round($price_l, '2', PHP_ROUND_HALF_UP) : round($price_l, '8', PHP_ROUND_HALF_UP);
				$row['price_l'] = $price_l;
				$row['price_f'] = $local_currency['symbol'].''.$row['price_l'];

				$row['images'] = $this->images_model->by_item($row['id']);
				array_push($results, $row);
				
			}
		} else {
			$results = array();
		}	
		
		return $results;
		
	}
	
	/**
	 * Get
	 * 
	 * Get information about an item (by $hash).
	 * 
	 * @access	public
	 * @param	string	$hash
	 * @return	array/FALSE
	 */					
	public function get($hash) {
		$this->db->where('hash', $hash);
		$query = $this->db->get('items');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			
			// Check if the vendor is banned. Fail if that is the case.
			$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
			if($row['vendor']['banned'] == '1')
				return FALSE;
			
			$row['description_f'] = nl2br($row['description']);
			$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
			$row['currency'] = $this->currencies_model->get($row['currency']);
			
			$row['price_b'] = $row['price']/$row['currency']['rate'];
			$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
			$price_l = (float)($row['price_b']*$local_currency['rate']);
			$price_l = ($this->current_user->currency['id'] !== '0') ? round($price_l, '2', PHP_ROUND_HALF_UP) : round($price_l, '8', PHP_ROUND_HALF_UP);
			$row['price_l'] = $price_l;
			$row['price_f'] = $local_currency['symbol'].''.$row['price_l'];
							
			$row['main_image'] = $this->images_model->get($row['main_image']);
			$row['ship_from_f'] = $this->general_model->location_by_id($row['ship_from']);
			$row['images'] = $this->images_model->by_item($hash);
			return $row;
		}
		return FALSE;
	}
	
	/**
	 * By User
	 * 
	 * Load listings as displayed by a user.
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @return	bool
	 */					
	public function by_user($user_hash) {		
		$this->db->select('id, hash, price, currency, hidden, category, name, description, main_image');
		$this->db->where('vendor_hash', $user_hash);
		$this->db->where('hidden !=', '1');
		$this->db->order_by('add_time', 'asc');
		$query = $this->db->get('bw_items');
		
		if($query->num_rows() > 0) {
			$results = array();
			foreach($query->result_array() as $row) {
				
				$row['description_s'] = substr(strip_tags($row['description']),0,50);
				if(strlen($row['description']) > 50) $row['description_s'] .= '...';
				
				$row['main_image'] = $this->images_model->get($row['main_image']);
				$row['currency'] = $this->currencies_model->get($row['currency']);			
							
				$row['price_b'] = $row['price']/$row['currency']['rate'];
				$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
				$row['price_l'] = (float)($row['price_b']*$local_currency['rate']);
				$row['price_f'] = $local_currency['symbol'].''.$row['price_l'];	
				array_push($results, $row);
			}
			return $results;
		}
			
		return FALSE;
	}
		
	/**
	 * Get List Count
	 * 
	 * Will be used when implementing pagination.
	 *
	 * @access	public
	 * @return	int
	 */					
	public function get_list_count() { 
		$this->db->select('id')
				 ->where('hidden !=', '1')
				 ->order_by('add_time ASC')
				 ->limit($limit, $start);
		$query = $this->db->get('bw_items');
	
		return $query->num_rows();
	}
};

/* End of File: Items_Model.php */
