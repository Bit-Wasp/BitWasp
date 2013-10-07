<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Items extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('items_model');
	}
	
	// Load all items.
	public function index(){				
		$data['title'] = 'Items';
		$data['page'] = 'items/index';
		$data['items'] = $this->items_model->get_list();		
		$this->load->library('Layout', $data);
	}
	
	// Load all items in a category.
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
	
	// Load a specific item.
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

