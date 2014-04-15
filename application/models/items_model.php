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
	 * Get Count
	 * 
	 * Accepts an array containing the options for items, and returns
	 * the total number of requests. 
	 * 
	 * @param	$opt
	 * @return	int
	 */
	public function get_count($opt = array()){
		$this->db->select('id')
				 ->where('hidden', '0')
				 ->order_by('add_time DESC');
				 
		// Add on extra options.
		if(count($opt) > 0) {
			// If there is a list of item ID's to load..
			if(isset($opt['item_id_list'])) {
				if(is_array($opt['item_id_list']) && count($opt['item_id_list']) > 0) {
					$this->db->where_in('id', $opt['item_id_list']);
				} else {
					return 0;
				}
				
				// Remove this option to avoid issues with the next step.
				unset($opt['item_id_list']);
			}
			
			foreach($opt as $key => $val) {
				$this->db->where("$key", "$val");
			}
		}
		$this->db->from("items");
		return $this->db->count_all_results();
	}
	
	/**
	 * Pagination Links
	 * 
	 * @param	array	$items_config
	 * @param	string	base_url
	 * @param	int	
	 */
	public function pagination_links($items_config, $base_url, $per_page, $url_segment) {
		$this->load->library('pagination');
		$pagination = array();
		$pagination["base_url"] = $base_url;
		$pagination["total_rows"] = $this->get_count($items_config);
		$pagination["per_page"] = $per_page;
		$pagination["uri_segment"] = $url_segment;
		$pagination["num_links"] = round($pagination["total_rows"] / $pagination["per_page"]);
		$this->pagination->initialize($pagination);
		return $this->pagination->create_links();
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
	public function get_list_pages($opt = array(), $start, $per_page) {
		$limit = $per_page;
		$this->db->select('items.id, items.hash, price, vendor_hash, currency, description, hidden, category, items.name, add_time, update_time, description, main_image, users.user_hash, users.user_name, users.banned, images.hash as image_hash, images.encoded as image_encoded, images.height as image_height, images.width as image_width, currencies.code as currency_code')
				 ->where('hidden', '0')
				 ->order_by('add_time DESC')
				 ->join('users', 'users.user_hash = items.vendor_hash AND bw_users.banned = \'0\'')
				 ->join('images', 'images.hash = items.main_image')
				 ->join('currencies', 'currencies.id = items.currency')
				 ->limit($limit, $start);
				 
		// Add on extra options.
		if(count($opt) > 0) {
			// If there is a list of item ID's to load..
			if(isset($opt['item_id_list'])) {
				if(is_array($opt['item_id_list']) && count($opt['item_id_list']) > 0) {
					$this->db->where_in('items.id', $opt['item_id_list']);
				}
				else {
					return FALSE;
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
		$results = array();
		
		if($query->num_rows() > 0) {
			$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
			$exchange_rates = array();

			foreach($query->result_array() as $row) {
				// get vendor information
				$row['vendor'] = array();
				$row['vendor']['user_name'] = $row['user_name'];
				$row['vendor']['user_hash'] = $row['user_hash'];

				// get main image information
				$row['main_image'] = array();
				$row['main_image']['hash'] = $row['image_hash'];
				$row['main_image']['encoded'] = $row['image_encoded'];
				$row['main_image']['height'] = $row['image_height'];
				$row['main_image']['width'] = $row['image_width'];
				
				$row['description_s'] = strip_tags($row['description']);
				$row['description_s'] = strlen($row['description_s']) > 70 ? substr($row['description_s'], 0, 70) . "..." : $row['description_s'];

				$row['currency'] = strtolower($row['currency_code']);

				// save the exchange information so we don't have to call the database more than needed
				if (!isset($exchange_rates[$row['currency']])) {
					$rate = $this->currencies_model->get_exchange_rate($row['currency']);
					$exchange_rates[$row['currency']] = $rate;
				}
				else {
					$rate = $exchange_rates[$row['currency']];
				}

				// Load vendor information. Skip item if the user is banned.
				$row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
				if($row['vendor']['banned'] == '1')
					continue;
					
				// Load information about the items.
				$row['description_s'] = substr(strip_tags($row['description']),0,70);
				if(strlen($row['description']) > 70) $row['description_s'] .= '...';
				$row['price_b'] = number_format(($row['price']/$rate), 8);
				$row['add_time_f'] = $this->general->format_time($row['add_time']);
				
				$row['update_time_f'] = $this->general->format_time($row['update_time']);
				$price_l = (float)($row['price_b']*$local_currency['rate']);
				$price_l = ($this->current_user->currency['id'] !== '0') ? number_format($price_l, 2) : number_format($price_l, 8);
				$row['price_l'] = $price_l;
				$row['price_f'] = $local_currency['symbol'].' '.$row['price_l'];

				// being used anywhere?
				// $row['images'] = $this->images_model->by_item($row['id']);
				$results[] = $row;
				
			}
		}
		
		return $results;
		
	}
	
	public function get_list($opt = array()) {
		return $this->get_list_pages($opt, 0, 10000);
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
			$row['add_time_f'] = $this->general->format_time($row['add_time']);
			$row['update_time_f'] = $this->general->format_time($row['update_time']);
			$row['main_image'] = $this->images_model->get($row['main_image']);
			$row['ship_from_f'] = $this->location_model->location_by_id($row['ship_from']);
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
				
				$row['description_s'] = strip_tags($row['description_s']);
				$row['description_s'] = strlen($row['description_s']) > 50 ? substr($row['description_s'], 0, 50) . "..." : $row['description_s'];
				
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
