<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Listings extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('items_model');
		$this->load->model('listings_model');
		$this->load->model('currencies_model');
		$this->load->library('image');
	}
	
	// Show a users listings
	public function manage() {
		$data['title'] = 'Manage Listings';
		$data['page'] = 'listings/manage';
		$data['items'] = $this->items_model->by_user($this->current_user->user_hash);
		$this->load->library('Layout', $data);
	}
	
	// Edit an item.
	public function edit($item_hash) {
		$this->load->library('form_validation');
		$this->load->model('categories_model');
		
		$data['item'] = $this->listings_model->get($item_hash);
		if($data['item'] == FALSE)
			redirect('listings');
			
		if($this->form_validation->run('edit_listing') === TRUE) {
			// Compare post values to the original, remove any NULL entries.
			if($data['item']['price'] !== $this->input->post('price') ||
			   $data['item']['currency']['id'] !== $this->input->post('currency')){
				$changes['currency'] = $this->input->post('currency');
				$changes['price'] = $this->input->post('price');
			}

			$changes['name'] = ($data['item']['name'] == $this->input->post('name')) ? NULL : $this->input->post('name');
			$changes['description'] = ($data['item']['description'] == $this->input->post('description')) ? NULL : $this->input->post('description');
			$changes['category'] = ($data['item']['category'] == $this->input->post('category')) ? NULL : $this->input->post('category');
			$changes = array_filter($changes, 'strlen');
			
			if(count($changes) > 0){
				$data['returnMessage'] = 'Unable to save your changes at this time.';
				if($this->listings_model->update($item_hash, $changes) == TRUE) 
					$data['returnMessage'] = 'Your changes have been saved.';
			}
				
			// Refresh any changes.
			$data['item'] = $this->listings_model->get($item_hash);
				
		}
		
		$data['page'] = 'listings/edit';
		$data['title'] = 'Edit '.$data['item']['name'];
		$data['categories'] = $this->categories_model->list_all();
		$data['currencies'] = $this->currencies_model->get();
		
		$this->load->library('Layout', $data);
	}
	
	// Add an item.
	public function add() {
		$this->load->model('categories_model');
		$this->load->library('form_validation');
		
		$data['page'] = 'listings/add';
		$data['title'] = 'Add a Listing';
		$data['local_currency'] = (array)$this->current_user->currency;
		if($this->form_validation->run('add_listing') == TRUE){
			
			$properties = array('add_time' => time(),
								'category' => $this->input->post('category'),
								'currency' => $data['local_currency']['id'],
								'description' => $this->input->post('description'),
								'hash' => $this->general->unique_hash('items','hash'),
								'hidden' => ($this->input->post('hidden') == 'on') ? '1' : '0',
								'main_image' => 'default',
								'name' => $this->input->post('name'),
								'price' => $this->input->post('price'),
								'vendor_hash' => $this->current_user->user_hash		
						);
			if($this->listings_model->add($properties) == TRUE) {
				$data['page'] = 'listings/images';
				$data['title'] = 'Item Created';
				$data['item'] = $this->listings_model->get($properties['hash']);
				$data['images'] = $this->images_model->by_item($properties['hash']);
				// Allow the user to add some images.
			} else {
				$data['returnMessage'] = 'Error adding your item, please try again later.';
			}
		}
		
		if($data['page'] == 'listings/add'){
			$data['categories'] = $this->categories_model->list_all();
			$data['currencies'] = $this->currencies_model->get();
		}
		$this->load->library('Layout', $data);
	}
	
	// Delete an item, along with it's images.
	public function delete($hash) {
		$item = $this->listings_model->get($hash);
		
		if($item == FALSE) 
			redirect('listings');
			
		// Delete an items images as well.
		if($this->listings_model->delete($hash) !== FALSE) {
			if(count($item['images']) > 0){
				foreach($item['images'] as $image){
					$this->images_model->delete_item_img($hash, $image['hash']);
				}
			}
		}
		redirect('listings');
	}
	
	// Add images to an item.
	public function images($item_hash) {	
   	 	$data['item']= $this->listings_model->get($item_hash);
   	 	if($data['item'] == FALSE)
			redirect('listings');	 
			
		$this->load->model('images_model');
   	 	$this->load->library('form_validation');

		// Load image_upload rules from ./config/image_upload.php and then load the upload library.
		$this->config->load('image_upload', TRUE);
		$config = $this->config->item('image_upload');
		$this->load->library('upload', $config);	// Build upload class.

		$data['title'] = 'Item Images';
		$data['page'] = 'listings/images';
		
		//if($this->form_validation->run('add_image') !== FALSE) {
		if($this->input->post('add_image') == 'Create'){
			
			if(!$this->upload->do_upload()){
				
				$data['returnMessage'] = $this->upload->display_errors();
			} else {
				
				$upload_data = $this->upload->data();
				$upload_data['upload_path'] = $config['upload_path'];
				$this->image->import($upload_data);
										
				$small = $this->image->resize('200','150',$upload_data['raw_name']."_s");
				$thumb = $this->image->resize('100','75', $upload_data['raw_name']."_thumb");
				
				$main_image = FALSE;
				if($data['item']['main_image'] == 'default' || $this->input->post('main_image') == 'true')
					$main_image = TRUE;
				
				$hash = $this->general->unique_hash('images','hash'); 
				$add_small = $this->images_model->add_to_item($hash, $small['file_name'], $item_hash, $main_image);
				$add_normal = $this->images_model->add($hash."_l", $upload_data['file_name']);
				//$add_thumb = $this->images_model->add_to_item($thumb['file_name'], $item_hash);
					
				// Remove files.
				unlink($upload_data['full_path']);
				unlink($small['full_path']);
				unlink($thumb['full_path']);
			}
		}
		// Reload images after adding new ones.
		$data['images'] = $this->images_model->by_item($item_hash);
		
		$this->load->library('Layout', $data);
	}
	
	// Delete an image and redirect.
	public function delete_image($image_hash) {
		$item_hash = $this->images_model->get_item($image_hash);
		$item_info = $this->listings_model->get($item_hash);
		
		if($item_info == FALSE)
			redirect('listings');
	
		$this->images_model->delete_item_img($item_hash, $image_hash);
		
		redirect('listings/images/'.$item_hash);
	}
	
	// Update the main image and redirect.
	public function main_image($image_hash) {
		$item_hash = $this->images_model->get_item($image_hash);
		$item = $this->listings_model->get($item_hash);
		if($item == FALSE)
			redirect('listings');
			
		$this->images_model->main_image($item_hash, $image_hash);
		redirect('listings/images/'.$item_hash);
	}

	// Callback functions for form validation.
	public function check_category_exists($param) {
		$this->load->model('categories_model');
		$categories = $this->categories_model->list_all();
		$cat_id[] = array();
		foreach($categories as $category){
			$cat_id[] = $category['id'];
		}
		return $this->general->matches_any($param, $cat_id);
	}
	
	public function check_currency_exists($param) {
		$currencies = $this->currencies_model->get();
		$c_id = array();
		foreach($currencies as $currency) { 
			$c_id[] = $currency['id'];
		}
		return $this->general->matches_any($param, $c_id);
	}
	
	public function check_price_positive($param) {
		return ($param > 0) ? TRUE : FALSE;
	}
};

/* End of file Listings.php */
