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
	}
	
	/**
	 * Load all items.
	 * NOTE: pagination to come soon.
	 * URI: /items
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 */
	public function index() {
		$data['title'] = 'Items';
		$data['page'] = 'items/index';
		$data['items'] = $this->items_model->get_list();		
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
	public function category($hash) {
		$this->load->model('categories_model');
		
		$data['category'] = $this->categories_model->get(array('hash' => "$hash"));
		if($data['category'] == FALSE)
			redirect('items');
		
		$data['title'] = 'Items by Category: '.$data['category']['name'];
		$data['page'] = 'items/index';
		$data['items'] = $this->items_model->get_list( array('category' => $data['category']['id']) );
		$this->load->library('Layout', $data);
	}

	/**
	 * Location
	 * 
	 * Load all items that are shipped to a specific location ID.
	 * URI: /location/$hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Categories_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function location($location = NULL) {
		$this->load->model('shipping_costs_model');
	
		// Load any posted location information.
		if($this->input->post('location_submit') == 'Go') {
			$post_location = $this->input->post('location');
			$to = ($this->general_model->location_by_id($post_location) !== FALSE) ? "location/$post_location" : "items";
			redirect($to);
		}
		
		// If the user requests an undeclared location, redirect to main page.
		if($location == '1')
			redirect('items');	

		// If they request a domestic location, roll with that. 
		if($location == 'domestic') {
			$this->load->model('accounts_model');
			$user = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash));
			$opt['ship_from'] = $user['location'];
			$data['location'] = $this->general_model->location_by_id($user['location']);

			$data['items'] = $this->items_model->get_list($opt);
		} else {

			// Load the location.
			$data['location'] = $this->general_model->location_by_id($location);
			if($data['location'] == FALSE)
				redirect('items');
				
			// Generate a list of items using a list
			$opt['item_id_list'] = $this->shipping_costs_model->list_by_location($location);
			$data['items'] = $this->items_model->get_list($opt);
		}
		$data['title'] = 'Items shipped to '.$data['location'];
		$data['page'] = 'items/index';
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

		$info = (array)json_decode($this->session->flashdata('returnMessage'));
		if(count($info) !== 0)
			$data['returnMessage'] = $info['message'];

		$data['logged_in'] = $this->current_user->logged_in();
		$data['page'] = 'items/get';
		$data['title'] = $data['item']['name'];
		$data['user_role'] = $this->current_user->user_role;
		$data['shipping_costs'] = $this->shipping_costs_model->for_item($data['item']['id']);
		$this->load->library('Layout', $data);
	}
};

/* End of File: Items.php */
