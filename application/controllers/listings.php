<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Listings Controller
 *
 * This class handles management of a vendors listings.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Items
 * @author		BitWasp
 * 
 */
class Listings extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Listings_Model
	 * @see		Models/Currencies_Model
	 * @see		Libraries/Image
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('items_model');
		$this->load->model('listings_model');
		$this->load->model('currencies_model');
		$this->load->library('image');
	}
	
	/**
	 * Show all a users listings.
	 * URI: /listings
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * 
	 * @return	void
	 */
	public function manage() {
		$data['title'] = 'Manage Listings';
		$data['page'] = 'listings/manage';
		$data['items'] = $this->listings_model->my_listings();
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Edit a Listing
	 * URI: /listings/edit/$hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Categories_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function edit($item_hash) {
		$this->load->library('form_validation');
		$this->load->model('categories_model');
		
		// If the listing doesn't exist, or belong to this user, abort.
		$data['item'] = $this->listings_model->get($item_hash);
		if($data['item'] == FALSE)
			redirect('listings');
			
		if($this->form_validation->run('edit_listing') === TRUE) {
			// Compare post values to the original, remove any NULL entries.
			if($data['item']['price'] !== $this->input->post('price') ||
			  $data['item']['currency']['id'] !== $this->input->post('currency')) {
				$changes['currency'] = $this->input->post('currency');
				$changes['price'] = $this->input->post('price');
			}

			$changes['name'] = ($data['item']['name'] == $this->input->post('name')) ? NULL : $this->input->post('name');
			$changes['description'] = ($data['item']['description'] == $this->input->post('description')) ? NULL : $this->input->post('description');
			$changes['category'] = ($data['item']['category'] == $this->input->post('category')) ? NULL : $this->input->post('category');
			$changes['ship_from'] = ($data['item']['ship_from'] == $this->input->post('ship_from')) ? NULL : $this->input->post('ship_from');
			$changes = array_filter($changes, 'strlen');
			
			if(count($changes) > 0) {
				$data['returnMessage'] = 'Unable to save your changes at this time.';
				if($this->listings_model->update($item_hash, $changes) == TRUE) 
					$data['returnMessage'] = 'Your changes have been saved.';
			}
				
			// Refresh any changes.
			$data['item'] = $this->listings_model->get($item_hash);
		}
		
		$data['page'] = 'listings/edit';
		$data['title'] = 'Edit '.$data['item']['name'];
		$data['categories'] = $this->categories_model->generate_select_list('category', 'span5', $data['item']['category']);
		$data['currencies'] = $this->currencies_model->get();
		
		$this->load->model('location_model');
		$data['item_location_select'] = $this->location_model->generate_select_list($this->bw_config->location_list_source, 'ship_from', 'span5', $data['item']['ship_from']);
		
	
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Add a Listing
	 * URI: /listings/add
	 * 
	 * @access	public
	 * @see		Models/Listings_Model
	 * @see		Models/Images_Model
	 * @see		Models/Categories_Model
	 * @see		Models/Currencies_Model
	 * @see		Libraries/Form_Validation
	 * 
	 * @return	void
	 */
	public function add() {
		$this->load->model('categories_model');
		$this->load->library('form_validation');
		$this->load->model('location_model');
		
		$data['page'] = 'listings/add';
		$data['title'] = 'Add a Listing';
		$data['local_currency'] = (array)$this->current_user->currency;
		$data['locations'] = $this->location_model->generate_select_list($this->bw_config->location_list_source, 'ship_from', 'span5');
		
		if($this->form_validation->run('add_listing') == TRUE) {
			
			$hash = $this->general->unique_hash('items','hash');
			$properties = array('add_time' => time(),
								'category' => $this->input->post('category'),
								'currency' => $data['local_currency']['id'],
								'description' => $this->input->post('description'),
								'hash' => $hash,
								'hidden' => ($this->input->post('hidden') == 'on') ? '1' : '0',
								'main_image' => 'default',
								'name' => $this->input->post('name'),
								'price' => $this->input->post('price'),
								'vendor_hash' => $this->current_user->user_hash,
								'ship_from' => $this->input->post('ship_from')
						);
			// Add the listing
			if($this->listings_model->add($properties) == TRUE) {
				$this->session->set_userdata('new_item','true');
				$this->session->set_flashdata('shipping_returnMessage',json_encode(array('returnMessage' => 'Your item has been created. You must now configure shipping costs for your item.', 'success' => TRUE)));
				redirect('listings/shipping/'.$hash);
			} else {
				// Display an error message.
				$data['returnMessage'] = 'Error adding your item, please try again later.';
			}
		}
		
		if($data['page'] == 'listings/add') {
			$data['categories'] = $this->categories_model->generate_select_list('category','span5');
			$data['currencies'] = $this->currencies_model->get();
		}
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Delete an item along with it's images.
	 * URI: /listings/delete/$hash
	 * 
	 * @access	public
	 * @see		Models/Listings_Model
	 * @see		Models/Images_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function delete($hash) {
		$item = $this->listings_model->get($hash);

		// Abort if the listing does not exist.
		if($item == FALSE) 
			redirect('listings');
			
		// Delete an items images as well.
		if($this->listings_model->delete($hash) !== FALSE) {
			if(count($item['images']) > 0) {
				// Delete each image.
				foreach($item['images'] as $image) {
					$this->images_model->delete_item_img($hash, $image['hash']);
				}
			}
		}
		redirect('listings');
	}
	
	/**
	 * Add Image to a Listing
	 * URI: /listings/edit/$hash
	 * 
	 * @access	public
	 * @see		Models/Listings_Model
	 * @see		Models/Images_Model
	 * @see		Libraries/Form_Validation
	 * 
	 * @param	string
	 * @return	void
	 */
	public function images($item_hash) {	
   	 	$data['item']= $this->listings_model->get($item_hash);
   	 	if($data['item'] == FALSE)
			redirect('listings');	 
			
		$this->load->model('images_model');
   	 	$this->load->library('form_validation');

		$info = (array)json_decode($this->session->flashdata('images_returnMessage'));
		if(count($info) !== 0){
			if(isset($info['success']) && $info['success'] == TRUE)
				$data['success'] = TRUE;
			$data['returnMessage'] = $info['returnMessage'];
		}

		// Load image_upload rules from ./config/image_upload.php and then load the upload library.
		$this->config->load('image_upload', TRUE);
		$config = $this->config->item('image_upload');
		$this->load->library('upload', $config);	// Build upload class.

		$data['title'] = 'Item Images';
		$data['page'] = 'listings/images';
		
		// If the Add Image form has been submitted:
		if($this->input->post('add_image') == 'Create') {
			
			if(!$this->upload->do_upload()) {
				// If there is an error with the file, display the errors.
				$data['returnMessage'] = $this->upload->display_errors();
			} else {
				// Process the upload.
				
				$upload_data = $this->upload->data();
				$upload_data['upload_path'] = $config['upload_path'];

				$this->image->import($upload_data);			// Should be error checking here

				// Prepare the normal image's encoded string
				$normal_encoded_string = $this->image->encode($upload_data['file_name']);
				// Resize the image to smaller sizes. Image is base64 encoded in output array.
				$small = $this->image->resize('200','150',$upload_data['raw_name']."_s");
				//$thumb = $this->image->resize('100','75', $upload_data['raw_name']."_thumb");
				
				$main_image = FALSE;
				if($data['item']['main_image'] == 'default' || $this->input->post('main_image') == 'true')
					$main_image = TRUE;
				
				$hash = $this->general->unique_hash('images','hash'); 
				
				// If resizing fails, use the normal image.
				$add_small = ($small !== FALSE) ? $this->images_model->add_to_item($hash, $small['encoded_string'], $item_hash, $main_image) : $this->images_model->add_to_item($hash, $normal_encoded_string, $item_hash, $main_image);
				$add_normal = $this->images_model->add($hash."_l", $normal_encoded_string);
				//$add_thumb = $this->images_model->add_to_item($thumb['file_name'], $item_hash);
					
				// Remove the uploaded file.
				unlink($upload_data['full_path']);
			}
		}
		// Reload images after adding new ones.
		$data['images'] = $this->images_model->by_item($item_hash);
		
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Shipping
	 * 
	 * This function is used to configure the shipping charges for a
	 * listing. Redirects to listings page if the requested item is invalid.
	 * URI: /listings/shipping/$hash
	 */
	public function shipping($item_hash) {
		$data['item'] = $this->listings_model->get($item_hash);
		if($data['item'] == FALSE)
			redirect('listings');

		$info = (array)json_decode($this->session->flashdata('shipping_returnMessage'));
		if(count($info) !== 0){
			if(isset($info['success']) && $info['success'] == TRUE)
				$data['success'] = TRUE;
			$data['returnMessage'] = $info['returnMessage'];
		}
		
		$this->load->library('form_validation');
		$this->load->model('accounts_model');
		$this->load->model('shipping_costs_model');
		
		$new_item = $this->session->userdata('new_item');
		$redirect_to = ($new_item == 'true') ? 'listings/images/'.$data['item']['hash'] : 'listings/shipping/'.$data['item']['hash'];
		$data['shipping_costs'] = $this->shipping_costs_model->for_item($data['item']['id'], TRUE);

		if($this->input->post('shipping_costs_update') == 'Update') {
			//if($this->form_validation->run('shipping_costs') == TRUE) {
				$prices = $this->input->post('price');
				$country = $this->input->post('country');
				$enabled = $this->input->post('enabled');
	
				$array = array();
				$i = 0; 
				foreach($prices as $key => $price) {
					$new_key = ($key !== 'worldwide') ? $country[$i] : $key;
					
					if($country[$i] !== '')
						$array[] = array('cost' => $price,
										'destination_id' => $new_key,
										'enabled' => (isset($enabled[$new_key]) && $enabled[$new_key] == '1') ? '1' : '0');
					
					if($key !== 'worldwide')
						$i++;
				}
				if($new_item == 'true')
					$this->session->unset_userdata('new_item');
				
				if($this->shipping_costs_model->update($data['item']['id'], $array)) {
					if($new_item == 'true'){
						$this->session->set_flashdata('images_returnMessage',json_encode(array('returnMessage' => 'Shipping costs have been updated. Now add images for your item.')));
					}
					echo 'redirect here';
					redirect($redirect_to);
				}
				
			//}
		}
		
		$this->load->model('location_model');
		$data['locations'] = $this->location_model->generate_select_list($this->bw_config->location_list_source, 'country[]', 'span5');
		$data['current_user'] = $this->current_user->status();
		$data['account'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
		$data['title'] = 'Shipping Costs';
		$data['page'] = 'listings/shipping_costs';
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Delete a Image from a Listing
	 * URI: /listings/edit/$hash
	 * 
	 * @access	public
	 * @see		Models/Listings_Model
	 * @see		Models/Images_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function delete_image($image_hash) {
		$item_hash = $this->images_model->get_item($image_hash);
		$item_info = $this->listings_model->get($item_hash);
		
		if($item_info == FALSE)
			redirect('listings');
	
		$this->images_model->delete_item_img($item_hash, $image_hash);
		
		redirect('listings/images/'.$item_hash);
	}
	
	/**
	 * Add a Listings Main Image, and redirect.
	 * URI: /listings/main_image/$hash
	 * 
	 * @access	public
	 * @see		Models/Listings_Model
	 * @see		Models/Images_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function main_image($image_hash) {
		$item_hash = $this->images_model->get_item($image_hash);
		$item = $this->listings_model->get($item_hash);
		if($item == FALSE)
			redirect('listings');
			
		$this->images_model->main_image($item_hash, $image_hash);
		redirect('listings/images/'.$item_hash);
	}

	// Callback functions for form validation.
	
	/**
	 * Check the supplied category ID exists.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_category_exists($param) {
		$this->load->model('categories_model');
		$categories = $this->categories_model->list_all();
		$cat_id[] = array();
		foreach($categories as $category) {
			$cat_id[] = $category['id'];
		}
		return $this->general->matches_any($param, $cat_id);
	}
	
	/**
	 * Check Currency Exists
	 * 
	 * Check the supplied currency ID ($param) exists.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_currency_exists($param) {
		$currencies = $this->currencies_model->get();
		$c_id = array();
		foreach($currencies as $currency) { 
			$c_id[] = $currency['id'];
		}
		return $this->general->matches_any($param, $c_id);
	}
	
	/**
	 * Check Is Positive
	 * 
	 * Check the supplied parameter is a positive number.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_is_positive($param) {
		return (is_numeric($param) && $param >= 0) ? TRUE : FALSE;		
	}
	
	/**
	 * Check Location
	 * 
	 * Check the supplied location ID ($param) exists.
	 *
	 * @param	id	$param
	 * @return	boolean
	 */
	public function check_location($param) {
		$this->load->model('location_model');
		echo $param;
		return ($this->location_model->location_by_id($param) !== FALSE) ? TRUE : FALSE;
	}

	/**
	 * Check Shipping Location
	 * 
	 * Check the supplied location ID for the shipping destination exists.
	 * 
	 * @param	string	$param
	 * @return	boolean
	 */
	public function check_shipping_location($param) {
		$this->load->model('location_model');
		return ($param == 'worldwide' || ($this->location_model->location_by_id($param) !== FALSE)) ? TRUE : FALSE;
	}

	/**
	 * Check Bool
	 * 
	 * Check the supplied parameter is for a boolean..
	 *
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_bool($param) {
		return ($this->general->matches_any($param, array('0','1')) == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Block Access To Parent Category
	 * 
	 * This function blocks form submission when a user selects a category
	 * which has child categories in it. If it has child categories,
	 * it returns FALSE, to block form submission. Otherwise it returns
	 * TRUE, allowing the submission.
	 * 
	 * @param	int	$category_id
	 * @return	boolean
	 */
	public function block_access_to_parent_category($category_id){
		$info = $this->categories_model->get_children($category_id);
		// If it has children, return FALSE, as they are not allowed
		// to post there.
		return ($info['count'] > 0) ? FALSE : TRUE;
	}


};

/* End of file Listings.php */
