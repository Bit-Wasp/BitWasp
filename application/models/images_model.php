<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images_model extends CI_Model {
	
	public function __construct() {
		parent::__construct();
	}
	
	// Load an images information.
	public function get($image_hash) {
		$this->db->select('hash, encoded, height, width');
		$this->db->where('hash', $image_hash);
		$query = $this->db->get('images');
		if($query->num_rows() > 0)
			return $query->row_array();
		
		if($image_hash !== 'default') 
			return $this->get('default');
			
		return FALSE;
	}
	
	// List an items images using the item hash.
	public function by_item($item_hash) {
		$this->db->select('image_hash');
		$this->db->where('item_hash', $item_hash);
		$query = $this->db->get('item_images');
		if($query->num_rows() > 0) {
			$results = array();
			foreach($query->result_array() as $row) {
				array_push($results, $this->get($row['image_hash']));
			}
		} else {
			$results = array();
		}
		return $results;
	}
	
	// Add an encoded image to the images table.
	public function add($image_hash, $file_name) {
		$insert = array('hash' => $image_hash,
						'encoded' => $this->image->encode($file_name));	
						
		if($this->db->insert('images', $insert) == TRUE)
			return TRUE;
		
		return FALSE;
	}
	
	// Add to an item, as well as to the images table.
	public function add_to_item($image_hash, $file_name, $item_hash, $main_image = FALSE) {
		$insert = $this->add($image_hash, $file_name);
			
		if($insert == TRUE) {
			$link = array('image_hash' => $image_hash,
						  'item_hash' => $item_hash);

			if($this->db->insert('item_images', $link) == TRUE) {
				// If we need to update the main image, do it now. 
				if($main_image == TRUE)
					$this->main_image($item_hash, $image_hash);
				
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// Return an item_hash for an image's hash.
	public function get_item($image_hash) {
		$this->db->select('item_hash')
				 ->where('image_hash', $image_hash);
		$query = $this->db->get('item_images');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['item_hash'];
		}
		return FALSE;
	}
	
	// Update the main_image of an item.
	public function main_image($item_hash, $image_hash) {
		$update = array('main_image' => $image_hash);
		$this->db->where('hash', $item_hash);
		if($this->db->update('items', $update) == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	// Delete an items image. Handles removing the main_image.
	public function delete_item_img($item_hash, $image_hash) {
		// Delete link
		$this->db->where('image_hash', $image_hash);
		$link = $this->db->delete('item_images');
		
		// Get main_image
		$this->db->select('main_image')
		         ->where('hash', $item_hash);
		$item = $this->db->get('items');
		$item = $item->row_array();
		
		// As the link has been deleted, it's safe to update main_image to a new one before deleting the image.
		if($item['main_image'] == $image_hash){
			$images = $this->by_item($item_hash);
			if(count($images) > 0) {
				$this->main_image($item_hash, $images[0]['hash']);
			} else {
				$this->main_image($item_hash, 'default');
			}
		}
		
		// Delete the regular and large image from the table.
		$this->db->where('hash', $image_hash);
		$image = $this->db->delete('images');
		$this->db->where('hash', $image_hash."_l");
		$image_l = $this->db->delete('images');
	
		return (($link == TRUE) && ($image == TRUE) && ($image_l == TRUE)) ? TRUE : FALSE;
	}
};
