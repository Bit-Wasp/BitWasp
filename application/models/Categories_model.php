<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currencies Model
 *
 * This class handles handles database queries regarding currencies.
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Currencies
 * @author        BitWasp
 *
 */
class Categories_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add
     *
     * Add a category to the table, as outlined below.
     * $category = array(    'name' => '...',
     *                        'hash' => '...'),
     *                        'parent_id' => '...');
     * The category must contain these parameters, otherwise the insert will fail.
     * Returns a boolean TRUE on successful insert, else returns FALSE.
     *
     * @access    public
     * @param    array $category
     * @return    bool
     */
    public function add($category)
    {
        return $this->db->insert('categories', $category) == TRUE;
    }

    /**
     * Rename
     *
     * Change the name for the category record, $category_id, to $new_name.
     * Returns TRUE if update was successful, or FALSE if failure.
     *
     * @access    public
     * @param    int $category_id
     * @param    string $new_name
     * @return    bool
     */
    public function rename($category_id, $new_name)
    {
        $this->db->where('id', $category_id);
        return $this->db->update('categories', array('name' => $new_name)) == TRUE;
    }

    /**
     * Delete
     *
     * Delete a category as specified by the category's ID. Returns TRUE if the record
     * was successfully deleted, FALSE on failure.
     *
     * @access    public
     * @param    int $category_id
     * @return    bool
     */
    public function delete($category_id)
    {
        $this->db->where('id', $category_id);
        return $this->db->delete('categories') == TRUE;
    }

    /**
     * Get
     *
     * Loads a category based on $cat['id'] or $cat['hash']. Returns FALSE
     * if neither of these is set. If there are records specified by this
     * identifier, work out the number of items in the cateogry. If there
     * are no records, default to FALSE.
     *
     * @access    public
     * @param    array $cat
     * @return    array/FALSE
     */
    public function get(array $cat)
    {

        if (isset($cat['hash'])) {
            $query = $this->db->select("c1.*, (SELECT COUNT(*) FROM bw_items WHERE category = c1.id) AS count_child_items, (SELECT COUNT(*) FROM bw_categories WHERE parent_id = c1.id) AS count_child_cats")
                ->from('categories c1')
                ->where('c1.hash', $cat['hash'])
                ->get();

        } elseif (isset($cat['id'])) {
            $query = $this->db->select("c1.*, (SELECT COUNT(*) FROM bw_items WHERE category = c1.id) AS count_child_items, (SELECT COUNT(*) FROM bw_categories WHERE parent_id = c1.id) AS count_child_cats")
                ->from('categories c1')
                ->where('c1.hash', $cat['id'])
                ->get();
        } else {
            return FALSE;
        }

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $row['count_items'] = $this->get_item_count($row['id']);
            return $row;
        } else {
            return FALSE;
        }
    }

    /**
     * Get Item Count
     *
     * Loads the number of items in each category.
     *
     * @param    int $category_id
     * @return    int
     */
    public function get_item_count($category_id)
    {
        return $this->db->where('hidden', '0')->where('category', $category_id)->count_all_results('items');
    }

    /**
     * List All
     *
     * List all categories in a general list. Returns FALSE if there are
     * no categories in the table. Returns an array if there are records.
     *
     * @access    public
     * @return    array/FALSE
     */
    public function list_all()
    {

        $query = $this->db->select("c1.*, (SELECT COUNT(*) FROM bw_items WHERE category = c1.id) AS count_child_items, (SELECT COUNT(*) FROM bw_categories WHERE parent_id = c1.id) AS count_child_cats")
            ->from('categories c1')
            ->get();

        $results = array();
        foreach ($query->result() as $res) {
            $results[$res->id] = (array)$res;
        }
        return ($query->num_rows() > 0) ? $results : array();
    }

    /**
     * Get Children Count
     *
     * Loads the number of child categories for the specified $category_id.
     *
     * @param    int $category_id
     * @return    int
     */
    public function get_children_count($category_id)
    {
        return $this->db->where('parent_id', $category_id)->count_all_results('categories');
    }

    /**
     *
     * Update Items Category
     *
     * Move items from one category ID to another. Returns TRUE if it
     * was successful, otherwise it returns FALSE.
     *
     * @access    public
     * @param    int $current_id
     * @param    int $new_id
     * @return    bool
     */
    public function update_items_category($current_id, $new_id)
    {
        $this->db->where('category', $current_id);
        return $this->db->update('items', array('category' => $new_id)) == TRUE;
    }

    /**
     * Update Parent Category
     *
     * Move categorys with the parent category of $current_id to category
     * $new_id. Returns TRUE if the parent category of $current_id was
     * successfully updated to $new_id. Returns FALSE if unsuccessful.
     *
     * @access    public
     * @param    int $current_id
     * @param    int $new_id
     * @return    bool
     */
    public function update_parent_category($current_id, $new_id)
    {
        $this->db->where('parent_id', $current_id);
        return $this->db->update('categories', array('parent_id' => $new_id)) == TRUE;
    }

    /**
     * Generate Select List
     *
     * This function creates a <select> menu to select categories, which
     * displays parent categories in bold. When chosing a category, if
     * block_access_to_parent_category is used in form validation, the bold
     * categories will be disallowed. The name of the post variable is $param_name,
     * and the class for the tag is $class. You can set an ID to be $selected
     * by default in the select box, otherwise leave it at FALSE, and $extras
     * is an array containing optional features. array('root' => TRUE) will
     * display the root category as an option.
     *
     * It uses a recursive function, generate_select_list_recurse() to
     * recurse into the multidimensional array to show child/parent
     * categories.
     *
     * @param    string $param_name
     * @param    string $class
     * @param    FALSE /int    $selected
     * @param    array $extras
     * @return    string
     */
    public function generate_select_list($param_name, $class, $selected = FALSE, $extras = array())
    {
        $cats = $this->menu();
        $select = "<select name=\"{$param_name}\" class='{$class}' autocomplete=\"off\">\n<option value=\"\"></option>";
        if (isset($extras['root']) && $extras['root'] == TRUE)
            $select .= "<option style=\"font-weight:bold;\" value=\"0\">Root Category</option>";

        foreach ($cats as $cat) {
            $select .= $this->generate_select_list_recurse($cat, $selected);
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * Menu
     *
     * Returns a multidimensional array, showing the heirarchy of the
     * categories.
     *
     * @return    array
     */
    public function menu()
    {
        $categories = $this->bw_config->categories;
        if ($categories == FALSE OR count($categories) == 0)
            return array();

        $this->load->model('items_model');

        // Add all categories to $menu[] array.
        foreach ($categories as $result) {
            // Only need the count for this.
            $joins = array(
                array('table' => 'users',
                    'on' => "users.user_hash = items.vendor_hash AND users.banned='0'")
            );
            $count_item_children = $this->items_model->get_count(array('category' => $result['id']), $joins);
            $count_menu_children = count($this->get_children($result['id']));

            $menu[$result['id']] = array(
                'id' => $result['id'],
                'name' => $result['name'],
                'description' => $result['description'],
                'hash' => $result['hash'],
                'count' => $count_item_children,
                'count_children' => $count_menu_children,
                'parent_id' => $result['parent_id']
            );

            if (isset($menu[$result['parent_id']]))
                $menu[$result['parent_id']]['count_children'] += $count_menu_children;
        }

        // Store all child categories as an array $menu[parentID]['children']
        foreach ($menu as $ID => &$menuItem) {
            if ($menuItem['parent_id'] !== '0')
                $menu[$menuItem['parent_id']]['children'][$ID] = & $menuItem;
        }

        // Remove child categories from the first level of the $menu[] array.
        foreach (array_keys($menu) as $ID) {
            if ($menu[$ID]['parent_id'] != "0")
                unset($menu[$ID]);
        }

        // Return constructed menu.
        return $menu;
    }

    /**
     * Get Children
     *
     * Load the direct children of the specified parent ID. Also load the
     * number of child categories for that parent.
     *
     * @access    public
     * @param    int $category_id
     * @return    array
     */
    public function get_children($category_id)
    {
        $query = $this->db->where('parent_id', $category_id)->get('categories');
        $result = $query->result_array();
        $result['count'] = $query->num_rows();
        return $result;
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
     * @param    array $array
     * @param    FALSE /int    $selected
     * @return    string
     */
    public function generate_select_list_recurse($array, $selected = FALSE)
    {

        if (isset($array['children']) AND is_array($array['children'])) {
            $select_txt = '';
            if ($selected !== FALSE AND $array['id'] == $selected) $select_txt = ' selected="selected" ';
            $output = "<option style=\"font-weight:bold;\" value=\"{$array['id']}\"{$select_txt}>{$array['name']}</option>\n";
            foreach ($array['children'] as $child) {
                $output .= $this->generate_select_list_recurse($child, $selected);
            }
        } else {
            $select_txt = '';
            if ($selected !== FALSE AND $array['id'] == $selected) $select_txt = ' selected="selected" ';
            $output = "<option value=\"{$array['id']}\"{$select_txt}>{$array['name']}</option>\n";
        }
        return $output;
    }

}

;

/* End of file Categories_model.php */
/* Location: application/models/Categories_model.php */