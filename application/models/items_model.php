<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Listings management/administration only. 
// For items by user, go to items!

class Items_model extends CI_Model {
	
	public function __construct() {
		$this->load->model('currencies_model');
		$this->load->model('accounts_model');
		$this->load->model('images_model');
		$this->load->model('users_model');
	}
	
	// Get all items (will soon have pagination)
	public function get_list($opt = array()) {
		$results = array();
		
		$this->db->select('id, hash, price, vendor_hash, currency, hidden, category, name, description, main_image')
				 ->where('hidden !=', '1')
				 ->order_by('add_time ASC');
				 
		if(count($opt) > 0) {
			foreach($opt as $key => $val) {
				$this->db->where("$key", "$val");
			}
		}
				 
		$query = $this->db->get('bw_items');
		
		if($query->num_rows() > 0){
			foreach($query->result_array() as $row){
					
				$row['description_s'] = substr(strip_tags($row['description']),0,70);
				if(strlen($row['description']) > 70) $row['description_s'] .= '...';
				$row['main_image'] = $this->images_model->get($row['main_image']);
				$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
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
	
	// Get any item.
	public function get($hash) {
		$this->db->where('hash', $hash);
		$query = $this->db->get('items');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
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
			$row['images'] = $this->images_model->by_item($hash);
			return $row;
		}
		return FALSE;
	}
	
	// List all items by a user.
	public function by_user($user_hash) {		
		$this->db->select('id, hash, price, currency, hidden, category, name, description, main_image');
		$this->db->where('vendor_hash', $user_hash);
		$this->db->order_by('add_time', 'asc');
		$query = $this->db->get('bw_items');
		
		if($query->num_rows() > 0){
			$results = array();
			foreach($query->result_array() as $row){
				
				$row['description_s'] = substr(strip_tags($row['description']),0,50);
				if(strlen($row['description']) > 70) $row['description_s'] .= '...';
				
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
	
	// Count the full number of items in the table (for pagination, later)
	public function get_list_count(){ 
		$this->db->select('id')
				 ->where('hidden !=', '1')
				 ->order_by('add_time ASC')
				 ->limit($limit, $start);
		$query = $this->db->get('bw_items');
	
		return $query->num_rows();
	}
};
