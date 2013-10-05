<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	public $nav;

	public function __construct() {
		parent::__construct();
		$this->load->model('config_model');
		$this->load->model('categories_model');
		
		$this->nav = array(	'' => 			array(	'panel' => '',
													'title' => 'General',
													'heading' => 'Admin Panel'),
							'bitcoin' => 	array(  'panel' => '/bitcoin',
													'title' => 'Bitcoin',
													'heading' => 'Bitcoin Panel'),
							'items' =>		array(	'panel' => '/items',
													'title' => 'Items',
													'heading' => 'Items Panel'),
							'users' => 		array(	'panel' => '/users',
													'title' => 'Users',
													'heading' => 'User Panel')
						);
	}
	
	public function index() {
		$this->load->library('gpg');
		if($this->gpg->have_GPG == TRUE) 
			$data['gpg'] = 'gnupg-'.$this->gpg->version;
		$data['openssl'] = OPENSSL_VERSION_TEXT;
		$data['config'] = $this->bw_config->load_admin('');
		
		$data['page'] = 'admin/index';
		$data['title'] = $this->nav['']['heading'];
		$data['nav'] = $this->generate_nav();
		$this->load->library('Layout', $data);
	}

	public function edit_general() {
		$this->load->library('form_validation');
		$data['config'] = $this->bw_config->load_admin('');
		if($this->form_validation->run('admin_edit_') == TRUE) {
			$changes['site_description'] = ($this->input->post('site_descrpition') !== $data['config']['site_description']) ? $this->input->post('site_description') : NULL;
			$changes['site_title'] = ($this->input->post('site_title') !== $data['config']['site_title']) ? $this->input->post('site_title') : NULL;
			$changes['openssl_keysize'] = ($this->input->post('openssl_keysize') !== $data['config']['openssl_keysize']) ? $this->input->post('openssl_keysize') : NULL;
			$changes['allow_guests'] = ($this->input->post('allow_guests') !== $data['config']['allow_guests']) ? $this->input->post('allow_guests') : NULL;
			$changes = array_filter($changes, 'strlen');
	
			$this->config_model->update($changes);
			redirect('admin');			
		}
		$data['page'] = 'admin/edit_';
		$data['title'] = $this->nav['']['heading'];
		$data['nav'] = $this->generate_nav();
		$this->load->library('Layout', $data);
	}
	
	public function bitcoin() {
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
		$data['latest_block'] = $this->bitcoin_model->latest_block();
		$data['transaction_count'] = $this->general_model->count_transactions();
		$data['accounts'] = $this->bw_bitcoin->listaccounts(0);
		$data['bitcoin_index'] = $this->bw_config->price_index;
		
		$data['page'] = 'admin/bitcoin';
		$data['title'] = $this->nav['bitcoin']['heading'];
		$data['nav'] = $this->generate_nav();
		$this->load->library('Layout', $data);
	}
	
	public function edit_bitcoin() {
		$this->load->library('form_validation');
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
		$data['config'] = $this->bw_config->load_admin('bitcoin');
		$data['price_index'] = $this->bw_config->price_index;
		$data['accounts'] = $this->bw_bitcoin->listaccounts(0);
		
		if($this->input->post('update_price_index') == 'Update') {
			if(is_array($data['config']['price_index_config'][$this->input->post('price_index')]) || $this->input->post('price_index') == 'Disabled'){
				$update = array('price_index' => $this->input->post('price_index'));
				$this->config_model->update($update);
				$this->bw_bitcoin->ratenotify();
				redirect('admin/bitcoin');
			}
		}
		
		if($this->input->post('admin_transfer_bitcoins') == 'Send') {
			if($this->form_validation->run('admin_transfer_bitcoins') == TRUE) {
				$amount = $this->input->post('amount');
				if($data['accounts'][$this->input->post('from')] >= (float)$amount) {
					if($this->bw_bitcoin->move($this->input->post('from'), $this->input->post('to'), (float)$amount) == TRUE)
						redirect('admin/bitcoin');
				} else {
					$data['transfer_bitcoins_error'] = 'That account has insufficient funds.';
				}
			}
		}
		$data['page'] = 'admin/edit_bitcoin';
		$data['title'] = $this->nav['bitcoin']['heading'];
		$data['nav'] = $this->generate_nav();		
		$this->load->library('Layout', $data);
	}
	
	public function users() {
		$data['nav'] = $this->generate_nav();
		$data['user_count'] = $this->general_model->count_entries('users');
		$data['config'] = $this->bw_config->load_admin('users');
		
		$data['page'] = 'admin/users';
		$data['title'] = $this->nav['users']['heading'];
		$this->load->library('Layout', $data);
	}
	
	public function edit_users() {
		$this->load->library('form_validation');
		$data['nav'] = $this->generate_nav();
		$data['config'] = $this->bw_config->load_admin('users');
		
		if($this->form_validation->run('admin_edit_users') == TRUE) {
			$changes['login_timeout'] = ($this->input->post('login_timeout') !== $data['config']['login_timeout']) ? $this->input->post('login_timeout') : NULL;
			$changes['captcha_length'] = ($this->input->post('captcha_length') !== $data['config']['captcha_length']) ? $this->input->post('captcha_length') : NULL;
			$changes['registration_allowed'] = ($this->input->post('registration_allowed') !== $data['config']['registration_allowed']) ? $this->input->post('registration_allowed'): NULL;
			$changes['vendor_registration_allowed'] = ($this->input->post('vendor_registration_allowed') !== $data['config']['vendor_registration_allowed']) ? $this->input->post('vendor_registration_allowed'): NULL;
			$changes['encrypt_private_messages'] = ($this->input->post('encrypt_private_messages') !== $data['config']['encrypt_private_messages']) ? $this->input->post('encrypt_private_message'): NULL;
			$changes = array_filter($changes, 'strlen');
		
			// Update config
			$this->config_model->update($changes);
			redirect('admin/users');
		} 
		
		$data['page'] = 'admin/edit_users';
		$data['title'] = $this->nav['users']['heading'];
		$this->load->library('Layout', $data);
	}
	
	// Display admin stats/info about items.
	public function items() {
		$data['nav'] = $this->generate_nav();
		$data['item_count'] = $this->general_model->count_entries('items');
		$data['categories'] = $this->categories_model->list_all();
		$data['page'] = 'admin/items';
		$data['title'] = $this->nav['items']['heading'];
		$this->load->library('Layout', $data);
	}
	
	// Edit Item/Categorys settings, 
	public function edit_items() {
		$this->load->library('form_validation');
		$data['nav'] = $this->generate_nav();
		$data['categories'] = $this->categories_model->list_all();
		
		// Add a new category.
		if($this->input->post('add_category') == 'Add') {
			if($this->form_validation->run('admin_add_category') == TRUE) {
				$category = array(	'name' => $this->input->post('category_name'),
									'hash' => $this->general->unique_hash('categories','hash'),
									'parent_id' => $this->input->post('category_parent'));
				if($this->categories_model->add($category) == TRUE)
					redirect('admin/items');
			} 
		} 
		
		// Delete a category
		if($this->input->post('delete_category') == 'Delete') {
			if($this->form_validation->run('admin_delete_category') == TRUE) {
		
				$category = $this->categories_model->get(array('id' => $this->input->post('category_id')));
				$cat_children = $this->categories_model->get_children($category['id']);
		
				// Check if items or categories are orphaned by this action, redirect to move these.
				if($category['count_items'] > 0 || $cat_children !== FALSE) {
					redirect('admin/category/orphans/'.$category['hash']);
				} else {
					// Delete the category.
					if($this->categories_model->delete($this->input->post('category_id')) == TRUE)
						redirect('admin/items');
				}
			}
		}
		$data['page'] = 'admin/edit_items';
		$data['title'] = $this->nav['items']['heading'];
		$this->load->library('Layout', $data);
	}

	public function category_orphans($hash) {
		$data['category'] = $this->categories_model->get(array('hash' => $hash));
		if($data['category'] == FALSE)
			redirect('admin/items');
			
		$this->load->library('form_validation');
			
		// Load the list of categories.
		$data['categories'] = $this->categories_model->list_all();
		// Load the selected categories children.
		$data['children'] = $this->categories_model->get_children($data['category']['id']);		
		
		// Calculate what text to display.
		if($data['category']['count_items'] > 0 && $data['children']['count'] > 0){
			$data['list'] = "categories and items";
		} else {
			if($data['children']['count'] > 0)				$data['list'] = 'categories';
			if($data['category']['count_items'] > 0)		$data['list'] = 'items';
		}
		
		// If there is nothing to be done for this category, redirect.
		if(!isset($data['list']))
			redirect('admin/edit/items');

		if($this->form_validation->run('admin_category_orphans') == TRUE) {
			// Update records accordingly.
			if($data['list'] == 'items') {
				$this->categories_model->update_items_category($data['category']['id'], $this->input->post('category_id'));
			} else if($data['list'] == 'categories') {
				$this->categories_model->update_parent_category($data['category']['id'], $this->input->post('category_id'));
			} else if($data['list'] == 'categories and items') {
				$this->categories_model->update_items_category($data['category']['id'], $this->input->post('category_id'));
				$this->categories_model->update_parent_category($data['category']['id'], $this->input->post('category_id'));
			}
			// Finally, delete the category.
			if($this->categories_model->delete($data['category']['id']) == TRUE)
				redirect('edit/items');
		}
		
		$data['page'] = 'admin/category_orphans';
		$data['title'] = 'Fix Orphans';
		$this->load->library('Layout', $data);
	}

	public function user_tokens() {
		$this->load->model('users_model');
		$this->load->library('form_validation');
		
		if($this->input->post('create_token') == "Create"){
			if($this->form_validation->run('admin_create_token') == TRUE){
				
				$update = array('user_type' => $this->input->post('user_role'),
								'token_content' => $this->general->unique_hash('registration_tokens','token_content', 128),
								'comment' => $this->input->post('token_comment'));
								
				$data['returnMessage'] = 'Unable to create your token at this time.';
				if($this->users_model->add_registration_token($update) == TRUE){
					$data['success'] = TRUE;
					$data['returnMessage'] = 'Your token has been created';
				} 
			}
		}
		
		$data['tokens'] = $this->users_model->list_registration_tokens();
		$data['page'] = 'admin/user_tokens';
		$data['title'] = 'Registration Tokens';
		$this->load->library('Layout', $data);
	}
	
	public function delete_token($token) {
		$this->load->library('form_validation');
		$this->load->model('users_model');
		
		$token = $this->users_model->check_registration_token($token);
		if($token == FALSE)
			redirect('admin/tokens');
			
		$data['returnMessage'] = 'Unable to delete the specified token, please try again later.';
		if($this->users_model->delete_registration_token($token['id']) == TRUE){
			$data['success'] = TRUE;
			$data['returnMessage'] = 'The selected token has been deleted.';
		}
			
		$data['tokens'] = $this->users_model->list_registration_tokens();
		$data['page'] = 'admin/user_tokens';
		$data['title'] = 'Registration Tokens';
		$this->load->library('Layout', $data);
			
		return FALSE;
	}

	// Generate the navigation bar for the admin panel.
	public function generate_nav() { 
		$links = '';
		
		foreach($this->nav as $entry) { 
			$links .= '<li';
			if(uri_string() == 'admin'.$entry['panel'] || uri_string() == 'admin/edit'.$entry['panel']) {
				$links .= ' class="active" ';
				$self = $entry;
				$heading = $entry['heading'];
			}
			$links .= '>'.anchor('admin'.$entry['panel'], $entry['title']).'</li>';
		}

		$nav = '
		  <div class="tabbable">
			<label class="span3"><h2>'.$self['heading'].'</h2></label>
			<label class="span1"><a href="'.site_url().'admin/edit'.$self['panel'].'" class="btn ">Edit</a></label>
			<label class="span7">
			  <ul class="nav nav-tabs">
			  '.$links.'
			  </ul>
			</label>
		  </div>';

		return $nav;
	}
	
	// Callback to check the captcha length is not too long.
	public function check_captcha_length($param) {
		if($param < 13) 
			return TRUE;
			
		return FALSE;
	}

	// Callback functions for form validation.
	public function check_bool($param) {
		if($this->general->matches_any($param, array('0','1')))
			return TRUE;
		
		return FALSE;
	}

	// Callback; check the required category exists (for parent_id)
	public function check_category_exists($param) {
		if($param == '0')	// Allows the category to be a root category.
			return TRUE;
			
		$category = $this->categories_model->get(array('id' => $param));
		if($category !== FALSE)
			return TRUE;
		
		return FALSE;
	}
	
	// Callback, check if the category can be deleted.
	public function check_can_delete_category($param) {
		$category =$this->categories_model->get(array('id' => $param));
		if($category !== FALSE)
			return TRUE;
			
		return FALSE;
	}
	
	// Callback: check the specified bitcoin account already exists.
	public function check_bitcoin_account_exists($param) {
		if($param == '')
			return FALSE;
			
		$accounts = $this->bw_bitcoin->listaccounts(0);
		if(isset($accounts[$param]))
			return TRUE;

		return FALSE;
	}
	
	// Check the submitted parameter is either 1, 2, or 3.
	public function check_admin_roles($param){
		if($this->general->matches_any($param, array('1','2','3')))
			return TRUE;
		return FALSE;
	}
};

/* End of file: Admin.php */
