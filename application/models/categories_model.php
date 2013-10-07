<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends CI_Model {

	public function __construct() { 
		parent::__construct();
		
	}
	
	// Add a category to the table.
	public function add($category) {
		if($this->db->insert('categories', $category) == TRUE)
			return TRUE;
		
		return FALSE;
	}
	
	// Delete a category.
	public function delete($category_id) {
		$this->db->where('id', $category_id);
		if($this->db->delete('categories') == TRUE)
			return TRUE;
		
		return FALSE;
	}
	
	// Load a category, along with the number of items it contains.
	public function get(array $cat) {
		$this->db->select('id, name, hash, parent_id');

		if (isset($cat['hash'])) {
			$query = $this->db->get_where('categories', array('hash' => $cat['hash']));
		} elseif (isset($cat['id'])) {
			$query = $this->db->get_where('categories', array('id' => $cat['id']));
		} else {
			return FALSE;
		}
		
		if($query->num_rows() > 0){
			$row = $query->row_array();
			$this->db->where('category', $row['id']);
			$query = $this->db->get('items');
			$row['count_items'] = $query->num_rows();
			return $row;
		}
		return FALSE;
	}
	
	// Load all category information.
	public function list_all() {
		$this->db->select('id, hash, name, parent_id');
		$this->db->order_by('name', 'asc');
		$query = $this->db->get('categories');
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			return $result;
		}
			
		return FALSE;
	}
	/*
	// Count the number of items this category.		// UNUSED?
	public function count_items($category_id) {
		$this->db->where('category', $category_id);
		$query = $this->db->get('items');
		return $query->num_rows();
	}*/
	
	// Load the direct children of the selected category.
	public function get_children($category_id) {
		$this->db->where('parent_id', $category_id);
		$query = $this->db->get('categories');
		$result = $query->result_array();
		$result['count'] = $query->num_rows();
		return $result;
	}
	
	// Update items' parent ID.
	public function update_items_category($current_id, $new_id) {
		$this->db->where('category', $current_id);
		if($this->db->update('items', array('category' => $new_id)) == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	// Change categories' parent ID.
	public function update_parent_category($current_id, $new_id) {
		$this->db->where('parent_id', $current_id);
		if($this->db->update('categories', array('parent_id' => $new_id)) == TRUE)
			return TRUE;
			
		return FALSE;
	}

	// Produce categories in a dynamic multi-dimensional array
	public function menu(){
		
		$this->db->select('id, description, name, hash, parent_id');
		//Load all categories and sort by parent category
		$this->db->order_by("parent_id asc, name asc");
		$query = $this->db->get('categories');
		$menu = array();
	
		if($query->num_rows() == 0) 
			return array();
			
		// Add all categories to $menu[] array.
		foreach($query->result() as $result){
      		$this->db->where('category',"{$result->id}");
      		$this->db->where('hidden !=', '1');
			$products = $this->db->get('items');
			
			$menu[$result->id] = array(	'id' => $result->id,
										'name' => $result->name,
										'description' => $result->description,
										'hash' => $result->hash,
										'count' => $products->num_rows(),
										'parent_id' => $result->parent_id
									);
		}
		
		// Store all child categories as an array $menu[parentID]['children']
		foreach($menu as $ID => &$menuItem){
			if($menuItem['parent_id'] !== '0')								
				$menu[$menuItem['parent_id']]['children'][$ID] = &$menuItem;
		}

		// Remove child categories from the first level of the $menu[] array.
		foreach(array_keys($menu) as $ID){
			if($menu[$ID]['parent_id'] != "0")
				unset($menu[$ID]);
		}
		// Return constructed menu.
		return $menu;
	}
};

/* End of file Categories_model.php */
