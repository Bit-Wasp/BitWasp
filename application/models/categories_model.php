<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currencies Model
 *
 * This class handles handles database queries regarding currencies.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Currencies
 * @author		BitWasp
 * 
 */
class Categories_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */		
	public function __construct() { 
		parent::__construct();
		
	}
	
	/**
	 * Add
	 * 
	 * Add a category to the table, as outlined below.
	 * $category = array(	'name' => '...',
	 *						'hash' => '...'),
	 *						'parent_id' => '...');
	 * The category must contain these parameters, otherwise the insert will fail. 
	 * Returns a boolean TRUE on successful insert, else returns FALSE.
	 *
	 * @access	public
	 * @param	array	$category
	 * @return	bool
	 */			
	public function add($category) {
		return ($this->db->insert('categories', $category) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Rename
	 * 
	 * Change the name for the category record, $category_id, to $new_name.
	 * Returns TRUE if update was successful, or FALSE if failure.
	 *
	 * @access	public
	 * @param	int	$category_id
	 * @param	string	$new_name
	 * @return	bool
	 */				
	public function rename($category_id, $new_name) {
		$this->db->where('id', $category_id);
		return ($this->db->update('categories', array('name' => $new_name)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Delete
	 * 
	 * Delete a category as specified by the category's ID. Returns TRUE if the record 
	 * was successfully deleted, FALSE on failure. 
	 *
	 * @access	public
	 * @param	int	$category_id
	 * @return	bool
	 */				
	public function delete($category_id) {
		$this->db->where('id', $category_id);
		return ($this->db->delete('categories') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Get
	 * 
	 * Loads a category based on $cat['id'] or $cat['hash']. Returns FALSE
	 * if neither of these is set. If there are records specified by this
	 * identifier, work out the number of items in the cateogry. If there
	 * are no records, default to FALSE.
	 *
	 * @access	public
	 * @param	array	$cat
	 * @return	array/FALSE
	 */				
	public function get(array $cat) {
		$this->db->select('id, name, hash, parent_id');

		if (isset($cat['hash'])) {
			$this->db->select('id, name, hash, parent_id');			// Select statement repeated to avoid annoying errors.
			$query = $this->db->get_where('categories', array('hash' => $cat['hash']));
		} elseif (isset($cat['id'])) {
			$this->db->select('id, name, hash, parent_id');
			$query = $this->db->get_where('categories', array('id' => $cat['id']));
		} else {
			return FALSE;
		}
		
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$this->db->where('category', $row['id']);
			$query = $this->db->get('items');
			$row['count_items'] = $query->num_rows();
			return $row;
		}
		return FALSE;
	}
	
	/**
	 * List All
	 * 
	 * List all categories in a general list. Returns FALSE if there are 
	 * no categories in the table. Returns an array if there are records.
	 *
	 * @access	public
	 * @return	array/FALSE
	 */					
	public function list_all() {
		$this->db->select('id, hash, name, parent_id');
		$this->db->order_by('name', 'asc');
		$query = $this->db->get('categories');
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
	}
	
	/**
	 * Get Children
	 * 
	 * Load the direct children of the specified parent ID. Also load the
	 * number of child categories for that parent.
	 *
	 * @access	public
	 * @param	int	$category_id
	 * @return	array
	 */				
	public function get_children($category_id) {
		$this->db->where('parent_id', $category_id);
		$query = $this->db->get('categories');
		$result = $query->result_array();
		$result['count'] = $query->num_rows();
		return $result;
	}
	
	/**
	 * 
	 * Update Items Category
	 * 
	 * Move items from one category ID to another. Returns TRUE if it
	 * was successful, otherwise it returns FALSE.
	 *
	 * @access	public
	 * @param	int	$current_id
	 * @param	int	$new_id
	 * @return	bool
	 */				
	public function update_items_category($current_id, $new_id) {
		$this->db->where('category', $current_id);
		return ($this->db->update('items', array('category' => $new_id)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Update Parent Category
	 * 
	 * Move categorys with the parent category of $current_id to category
	 * $new_id. Returns TRUE if the parent category of $current_id was 
	 * successfully updated to $new_id. Returns FALSE if unsuccessful.
	 *
	 * @access	public
	 * @param	int	$current_id
	 * @param	int	$new_id
	 * @return	bool
	 */				
	public function update_parent_category($current_id, $new_id) {
		$this->db->where('parent_id', $current_id);
		return ($this->db->update('categories', array('parent_id' => $new_id)) == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Menu
	 * 
	 * Prepare categories in a multidimensional array. Load results, and 
	 * and loop through them to build up information. Build the array, adding
	 * on menu children, and unset any loose children from the very first level. 
	 * 
	 * @access	public
	 * @return	array
	 */				
	public function menu() {
		$this->load->model('items_model');
		
		$this->db->select('id, description, name, hash, parent_id');
		//Load all categories and sort by parent category
		$this->db->order_by("parent_id asc, name asc");
		$query = $this->db->get('categories');
		$menu = array();
	
		if($query->num_rows() == 0) 
			return array();
			
		// Add all categories to $menu[] array.
		foreach($query->result() as $result) {
			$items = $this->items_model->get_list(array('category' => $result->id));
			
			$menu[$result->id] = array(	'id' => $result->id,
										'name' => $result->name,
										'description' => $result->description,
										'hash' => $result->hash,
										'count' => count($items),
										'count_children' => count($this->get_children($result->id)),
										'parent_id' => $result->parent_id
									);
		}
		
		// Store all child categories as an array $menu[parentID]['children']
		foreach($menu as $ID => &$menuItem) {
			if($menuItem['parent_id'] !== '0')								
				$menu[$menuItem['parent_id']]['children'][$ID] = &$menuItem;
		}

		// Remove child categories from the first level of the $menu[] array.
		foreach(array_keys($menu) as $ID) {
			if($menu[$ID]['parent_id'] != "0")
				unset($menu[$ID]);
		}
		// Return constructed menu.
		return $menu;
	}
	

	/**
	 * Generate Select List
	 * 
	 * This function creates a <select> menu to select categories, which
	 * displays parent categories in bold. When chosing a category, if
	 * block_access_to_parent_category is used in form validation, the bold 
	 * categories will be disallowed. The name of the post variable is 'category'.
	 * 
	 * It uses a recursive function, generate_select_list_recurse() to
	 * recurse into the multidimensional array to show child/parent
	 * categories.
	 * 
	 * @return	string
	 */
	public function generate_select_list($selected = FALSE) {
		$cats = $this->menu();
		$select = "<select name=\"category\" class='span5' autocomplete=\"off\">\n<option value=\"\"></option>";
		foreach($cats as $cat){
			$select.= $this->generate_select_list_recurse($cat, $selected);
		}
		$select.= '</select>';
		return $select;
	}
	
	/**
	 * Generate Select List Recurse
	 * 
	 * Called by generate_select_list, this function takes a multidimensional 
	 * array as input, and recurses deeper into the array 
	 * if $array['children'] > 0. If that is the case, the select option
	 * will be in bold, indicating a parent category. Otherwise the option 
	 * is not altered.
	 * 
	 * @param	array	$array
	 * @return	string
	 */
	public function generate_select_list_recurse($array, $selected){
		
		if(isset($array['children']) && is_array($array['children'])){
			$select_txt = '';
			if($selected !== FALSE && $array['id'] == $selected) $select_txt = ' selected="selected" ';
			$output = "<option style=\"font-weight:bold;\" value=\"{$array['id']}\"{$select_txt}>{$array['name']}</option>\n";
			foreach($array['children'] as $child){
				$output.= $this->generate_select_list_recurse($child, $selected);
			}
		} else {
			$select_txt = '';
			if($selected !== FALSE && $array['id'] == $selected) $select_txt = ' selected="selected" ';
			$output = "<option value=\"{$array['id']}\"{$select_txt}>{$array['name']}</option>\n";
		}
		return $output;
	}

};

/* End of file Categories_model.php */
