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
	public function index(){				
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
	public function category($hash){		
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
	 * Load a specific item
	 * URI: /item/$hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * 
	 * @param	string
	 * @return	void
	 */	
	public function get($hash) {
		$data['item'] = $this->items_model->get($hash);
		if($data['item'] == FALSE) 
			redirect('items');

		$data['logged_in'] = $this->current_user->logged_in();
		$data['page'] = 'items/get';
		$data['title'] = $data['item']['name'];
		$data['user_role'] = $this->current_user->user_role;
		$this->load->library('Layout', $data);
	}
};

/* End of File: Items.php */
