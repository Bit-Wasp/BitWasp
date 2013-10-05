<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Listings management/administration only. 
// For items by user, go to items!

class Listings_model extends CI_Model {
	
	public function __construct(){
		$this->load->model('accounts_model');
	}
	
	// Add an Listing
	public function add($properties) {
		if($this->db->insert('items', $properties) == TRUE)
			return TRUE;
		
		return FALSE;
	}
	
	// Remove a Listing
	public function delete($hash) {
		$this->db->where('hash', $hash);
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		if($this->db->delete('items') == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	// Get an item, must be the current owner.
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
			$row['price_l'] = (float)($row['price_b']*$local_currency['rate']);
			$row['price_f'] = $local_currency['symbol'].' '.$row['price_l'];

			$row['main_image_f'] = $this->images_model->get($row['main_image']);
			$row['images'] = $this->images_model->by_item($hash);
			return $row;
		}
		
		return FALSE;
	}
	
	// Update a listing
	public function update($item_hash, $changes) {
		$this->db->where('hash', $item_hash);
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		
		if($this->db->update('items', $changes)) 
			return TRUE;
		
		return FALSE;
	}	


};

/* End of file listings_model.php */
