<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Items Controller
 *
 * This class handles with requests for items.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Items
 * @author		BitWasp
 * 
 */
class Items extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Items_Model
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('items_model');
		$this->items_per_page = 44;
	}
	
	/**
	 * Load all items.
	 * NOTE: pagination to come soon.
	 * URI: /items
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 */
	public function index($page = 0) {
		if(!(is_numeric($page) && $page >= 0))
			$page = 0;
			
		$data['title'] = 'Items';
		$data['page'] = 'items/index';
		
		$items_config = array();
		$per_page = $this->items_per_page;
		$data['links'] = $this->items_model->pagination_links($items_config, site_url('items'), $per_page, 2);
		$data['items'] = $this->items_model->get_list_pages($items_config, $page, $per_page);
		
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Load all items in a category.
	 * URI: /category/$hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Categories_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function category($hash, $page = 0) {
		if(!(is_numeric($page) && $page >= 0))
			$page = 0;
						
		$this->load->model('categories_model');
		$data['category'] = $this->categories_model->get(array('hash' => "$hash"));
		if($data['category'] == FALSE)
			redirect('items');

		$data['title'] = 'Items by Category: '.$data['category']['name'];
		$data['custom_title'] = 'Category: '.$data['category']['name'];
		$data['page'] = 'items/index';
		
		$per_page = $this->items_per_page;
		$items_config = array('category' => $data['category']['id']);
		$data['links'] = $this->items_model->pagination_links($items_config, site_url("category/$hash"), $per_page, 3);
		$data['items'] = $this->items_model->get_list_pages( array('category' => $data['category']['id']), $page, $per_page );
		
		$this->load->library('Layout', $data);
	}

	/**
	 * Location
	 * 
	 * This function requires that $source is either 'ship-to' or 'ship-from'.
	 * If not, it will redirect the user to the items page. It will then 
	 * check if a form was posted, and will then load the appropriate items,
	 * or if the requested post $location doesn't exist, will redirect to items.
	 * Otherwise, it can be called by supplying the parameters via GET.
	 * URI: /location/$source/$location
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Categories_Model
	 * 
	 * @param	string	$source(optional)
	 * @param	string	$location 
	 * @return	void
	 */
	public function location($source = '', $location = NULL, $page = 0) {
		if(!(is_numeric($page) && $page >= 0))
			$page = 0;
			
		if(!(is_numeric($location) && $location >= 0))
			$location = NULL;
				
		// If the $source is invalid, redirect to the items page.
		if(!$this->general->matches_any($source, array('ship-to','ship-from')))
			redirect('items');

		$this->load->library('form_validation');
		$this->load->model('location_model');
		$this->load->model('shipping_costs_model');

		// Load any posted location information.
		if($this->input->post('ship_to_submit') == 'Go') {
			if($this->form_validation->run('ship_to_submit') == TRUE) {
				$data['location_name'] = $this->location_model->location_by_id($this->input->post('location'));
				if($data['location_name'] == FALSE)
					redirect('items');
				$items_config = array('item_id_list' => $this->shipping_costs_model->list_IDs_by_location($this->input->post('location')));
			} else {
				$data['ship_to_error'] = 'Please make a valid selection.';
			}
		} else if($this->input->post('ship_from_submit') == 'Go') {
			if($this->form_validation->run('ship_from_submit') == TRUE) {
				$data['location_name'] = $this->location_model->location_by_id($this->input->post('location'), TRUE);
				if($data['location_name'] == FALSE)
					redirect('items');
				$items_config = array('ship_from' => $this->input->post('location'));
			} else {
				$data['ship_from_error'] = 'Please make a valid selection.';
			}
		} else {
			$data['location_name'] = $this->location_model->location_by_id($location);
			if($data['location_name'] == FALSE)
				redirect('items');

			if($source == 'ship-to') {
				// Load the id's of items which are available in the $location
				$items_config = array('item_id_list' => $this->shipping_costs_model->list_IDs_by_location($location));
			} else if($source == 'ship-from') {
				// Simply specify the item has ship_from=$location.
				$items_config = array('ship_from' => $location);
			}
		}

		if(isset($items_config)) {
			$per_page = $this->items_per_page;
			$data['links'] = $this->items_model->pagination_links($items_config, site_url("location/{$source}/{$location}"), $per_page, 4);
			$data['items'] = $this->items_model->get_list_pages($items_config, $page, $per_page);	
			
			// Set the appropriate titles.
			if($source == 'ship-from') {
				$data['title'] = 'Items shipped from '.$data['location_name'];
				$data['custom_title'] = 'Shipping From: '.$data['location_name'];
			} else if($source == 'ship-to') {
				$data['title'] = 'Items shipped to '.$data['location_name'];
				$data['custom_title'] = 'Shipping To: '.$data['location_name'];
			}
			
			$data['page'] = 'items/index';
		} else {
			$data['page'] = 'welcome_message';
		}
		$this->load->library('Layout', $data);
	}

	/**
	 * Load a specific item
	 * URI: /item/$hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * 
	 * @param	string	$hash
	 * @return	void
	 */	
	public function get($hash) {
		$data['item'] = $this->items_model->get($hash);
		if($data['item'] == FALSE) 
			redirect('items');

		$this->load->model('shipping_costs_model');
		$this->load->model('review_model');
			
		$data['logged_in'] = $this->current_user->logged_in();
		$data['page'] = 'items/get';
		$data['title'] = $data['item']['name'];
		$data['user_role'] = $this->current_user->user_role;
		$data['local_currency'] = $this->current_user->currency;
		$data['coin'] = $this->bw_config->currencies[0];
		$data['shipping_costs'] = $this->shipping_costs_model->for_item($data['item']['id']);

		$data['reviews'] = $this->review_model->random_latest_reviews(8, 'item', $hash);
		$data['review_count']['all'] = $this->review_model->count_reviews('item', $hash);
		$data['review_count']['positive'] = $this->review_model->count_reviews('item', $hash, 0);
		$data['review_count']['disputed'] = $this->review_model->count_reviews('item', $hash, 1);
		$data['average_rating'] = $this->review_model->current_rating('item', $hash);
		$data['vendor_rating'] = $this->review_model->current_rating('user', $data['item']['vendor']['user_hash']);

		$this->load->library('Layout', $data);
	}
	
	// Callback functions
	
	/**
	 * Check Ship From
	 * 
	 * This function checks that the ship-from form is submitted correctly,
	 * and input only contains a positive natural number.
	 * 
	 * @param	int	$param	
	 * @return boolean
	 */
	public function check_ship_from($param) {
		return (is_numeric($param) && $param >= 0) ? TRUE : FALSE;
	}
	
	/**
	 * Check Ship To
	 * 
	 * This function checks that the ship-to form is submitted correctly,
	 * such that the input is either 'worldwide' or a natural positive 
	 * number.
	 * 
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_ship_to($param) {
		return ($param == 'worldwide' || $this->check_ship_from($param) == TRUE) ? TRUE : FALSE;
	}
};

/* End of File: Items.php */
