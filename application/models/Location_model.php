<?php    if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Location Model
 *
 * This class handles handles database queries regarding locations. They
 * can be against different sets of data - the basic BitWasp location list
 * or a user defined list of locations.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Location
 * @author        BitWasp
 */
class Location_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Location Info
     *
     * Function to get the array of information describing a location,
     * specified by it's ID. Returns FALSE on failure, otherwise the
     * database record for the location.
     *
     * @param    string /int    $id
     * @return    string/FALSE
     */
    public function get_location_info($id)
    {
        if ($this->bw_config->location_list_source == 'Custom') {
            $query = $this->db->where('id', $id)->get('locations_custom_list');
        } else {
            $query = $this->db->where('id', $id)->get('locations_default_list');
        }

        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Get Child Locations
     *
     * Loads an array of child locations in an array (the main array of
     * locations)
     *
     * @param    array $array
     * @param    int $id
     * @return  array/FALSE
     */
    public function get_child_locations($array, $id)
    {
        $results = array();

        foreach ($array as $entry) {
            if ($entry['parent_id'] == $id)
                $results[] = $entry;

            if (isset($entry['children']) AND count($entry['children']) > 0 AND ($children = $this->get_child_locations($entry['children'], $id)) !== FALSE)
                $results = array_merge($results, $children);
        }
        return count($results) > 0 ? $results : FALSE;
    }

    /**
     * Validate User Child Location
     *
     * This function will create a list of the heirarchy of parent locations
     * from the users location to the root location, and returns true
     * or false depending on whether the required_parent_id is contained
     * in this list of points.
     *
     * @param    id $required_parent_id
     * @param    id $user_location_id
     * @return    boolean
     */
    public function validate_user_child_location($required_parent_id, $user_location_id)
    {
        $required_parent_id = (int)$required_parent_id;
        $user_location_id = (int)$user_location_id;

        if ($required_parent_id == $user_location_id)
            return TRUE;

        $location_heirarchy = $this->get_location_heirarchy($user_location_id);
        $required_location_found = FALSE;

        foreach ($location_heirarchy as $location) {

            if ($location['id'] == $required_parent_id)
                $required_location_found = TRUE;

            if ($location['parent_id'] == $required_parent_id)
                $required_location_found = TRUE;
        }
        return $required_location_found;
    }

    /**
     * Get Location Heirarchy
     *
     * Loads a sequence of locations, going from $child_location_id to it's
     * parent location, and so on, until it hits the root category.
     *
     * @param    int $child_location_id
     * @return boolean
     */
    public function get_location_heirarchy($child_location_id)
    {
        $location_heirarchy[] = $this->bw_config->locations[$child_location_id];

        do {
            $previous_position = count($location_heirarchy) - 1;

            // Break if you reach in invalid point in the chain.
            if (!isset($this->bw_config->locations[$location_heirarchy[$previous_position]['parent_id']]))
                break;

            $current = $this->bw_config->locations[$location_heirarchy[$previous_position]['parent_id']];
            $location_heirarchy[] = $current;

        } while ($location_heirarchy[($previous_position)]['parent_id'] !== '0' && $current['parent_id'] !== '0');

        return $location_heirarchy;
    }

    /**
     * Generate Select List
     *
     * This function creates a <select> menu to select categories, which
     * displays parent categories in bold. When chosing a category, if
     * block_access_to_parent_category is used in form validation, the bold
     * categories will be disallowed. The name of the post variable is
     * set by $param_name, and the class for the tag is $class. An ID can
     * be selected by default on the page by setting $selected to the id,
     * otherwise leave it set to FALSE. Optional parameters can be set
     * in $extras: array('root' => TRUE) or array('worldwide' => TRUE) can
     * be set to display an option for the Root Location, or Worldwide.
     *
     * It uses a recursive function, generate_select_list_recurse() to
     * recurse into the multidimensional array to show child/parent
     * categories.
     *
     * @param $list_type
     * @param $param_name
     * @param $class
     * @param bool $selected
     * @param array $extras
     * @return string
     */
    public function generate_select_list($list_type, $param_name, $class, $selected = FALSE, $extras = array())
    {
        $locations = ($list_type == $this->bw_config->location_list_source) ? $this->bw_config->locations : $this->get_list($list_type);

        if ($list_type == 'Custom')
            $locations = $this->to_multi_dimensional_array($locations);

        $select = "<select name=\"{$param_name}\" class='{$class}' autocomplete=\"off\">\n";
        $select .= "<option value=\"\"></option>\n";
        if (isset($extras['root']) AND $extras['root'] == TRUE)
            $select .= "<option style=\"font-weight:bold;\" value=\"0\">Root Location</option>";
        if (isset($extras['worldwide']) AND $extras['worldwide'] == TRUE)
            $select .= "<option style=\"font-weight:bold;\" value=\"worldwide\">Worldwide</option>";

        foreach ($locations as $cat) {
            $select .= $this->generate_select_list_recurse($cat, $selected);
        }

        $select .= '</select>';
        return $select;
    }

    /**
     * Get List
     *
     * Loads a multidimensional array of locations, depending on the
     * supplied list specifier: Default, or Custom.
     *
     * @param    string $list
     * @param    boolean $menu
     * @return    array/FALSE
     */
    public function get_list($list, $menu = TRUE)
    {
        if ($list == 'Custom') {
            //Load all categories and sort by parent category
            $this->db->order_by("parent_id asc, location asc");
            $query = $this->db->get('locations_custom_list');

            if ($query->num_rows() == 0)
                return array();
        } else {
            //Load all categories and sort by parent category
            $this->db->order_by("parent_id asc, location asc");
            $query = $this->db->get('locations_default_list');

            if ($query->num_rows() == 0)
                return array();
        }

        $results = array();
        foreach ($query->result_array() as $res) {
            $results[$res['id']] = $res;
        }

        return ($menu == FALSE) ? $results : $this->to_multi_dimensional_array($results);
    }

    /**
     * To Multi-Dimensional Array
     *
     * This function is called after using get_list(), or on the data
     * stored in bw_config->locations.
     *
     * @param    array $list
     * @return    array
     */
    public function to_multi_dimensional_array($list)
    {

        $menu = array();
        // Add all categories to $menu[] array.
        foreach ($list as $result) {
            $menu[$result['id']] = array(
                'id' => $result['id'],
                'location' => $result['location'],
                'hash' => $result['hash'],
                'parent_id' => $result['parent_id']
            );
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
     * Generate Select List Recurse
     *
     * Called by generate_select_list, this function takes a multidimensional
     * array as input, and recurses deeper into the array
     * if $array['children'] > 0. If that is the case, the select option
     * will be in bold, indicating a parent category. Otherwise the option
     * is not altered.
     *
     * @param array $array
     * @param bool $selected
     * @return string
     */
    public function generate_select_list_recurse($array, $selected = FALSE)
    {

        if (isset($array['children']) AND is_array($array['children'])) {
            $select_txt = '';
            if ($selected !== FALSE AND $array['id'] == $selected) $select_txt = ' selected="selected" ';
            $output = "<option style=\"font-weight:bold;\" value=\"{$array['id']}\"{$select_txt}>" . htmlspecialchars($array['location'], ENT_XHTML, 'UTF-8') . "</option>\n";
            foreach ($array['children'] as $child) {
                $output .= $this->generate_select_list_recurse($child, $selected);
            }
        } else {
            $select_txt = ' ';
            if ($selected !== FALSE AND $array['id'] == $selected) $select_txt = ' selected="selected" ';
            if ($array['parent_id'] == '0') $select_txt .= "style=\"font-weight:bold;\" ";
            $output = "<option value=\"{$array['id']}\"{$select_txt}>" . htmlspecialchars($array['location'], ENT_XHTML, 'UTF-8') . "</option>\n";
        }
        return $output;
    }

    /**
     * Add
     *
     * Add a category to the table, as outlined below.
     * $category = array(    'location' => '...',
     *                        'hash' => '...'),
     *                        'parent_id' => '...');
     * The category must contain these parameters, otherwise the insert will fail.
     * Returns a boolean TRUE on successful insert, else returns FALSE.
     *
     * @access    public
     * @param    array $location
     * @return    bool
     */
    public function add_custom_location($location)
    {
        return $this->db->insert('locations_custom_list', $location) == TRUE;
    }

    /**
     * Delete Custom Location
     *
     * Deletes a selected location $id. Returns boolean indicating success.
     *
     * @param    int $id
     * @return    boolean
     */
    public function delete_custom_location($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('locations_custom_list') == TRUE;
    }

    /**
     * Location by ID
     *
     * Load the name of the location specified by $id. Returns a string
     * if successful or FALSE on failure.
     *
     * need to review this disallow worldwide option...
     *
     * @param    int $id
     * @param    boolean $disallow_worldwide
     * @return    string/FALSE
     */
    public function location_by_id($id, $disallow_worldwide = FALSE)
    {
        if ($id == 'worldwide')
            return ($disallow_worldwide == TRUE) ? FALSE : 'Worldwide';

        return (!isset($this->bw_config->locations[$id])) ? FALSE : $this->bw_config->locations[$id]['location'];
    }

    /**
     * Custom Location By Id
     *
     * This function takes an $id, and checks if this custom location exists.
     * Returns an array of information if it exists, or FALSE if it does
     * not.
     *
     * @param    int $id
     * @return    array/FALSE
     */
    public function custom_location_by_id($id)
    {
        $query = $this->db->where('id', $id)->get('locations_custom_list');

        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Menu Human Readable
     *
     * This is a recursive function which displays a heirarchy of locations
     * in the custom location list. Function returns a HTML string containig
     * a nested structure.
     *
     * @param    array $locations
     * @param    int $level
     * @param    array $params
     * @return    string
     */
    public function menu_human_readable($locations, $level, $params)
    {
        $content = '';
        $level++;

        if ($level !== 1)
            $content .= "<ul>\n";

        // Pregenerate the URL. Checks for trailing slashes, fixes up
        // issues when mod_rewrite is disabled.
        // Loop through each parent category
        foreach ($locations as $location) {
            //Check if were are currently viewing this category, if so, set it as active
            $content .= "<li ";
            if (isset($params['id'])) {
                if ($params['id'] == $location['id'])
                    $content .= "class='active'";
            }
            $content .= ">\n";

            // Display link if category contains items.
            $content .= '<span>' . $location['location'] . '</span>';

            // Check if we need to recurse into children.
            if (isset($location['children']))
                $content .= $this->menu_human_readable($location['children'], $level, $params);

            $content .= "</li>\n";
        }

        if ($level !== 1)
            $content .= "</ul>\n";

        return $content;
    }

}

;

/* End of file: Location_model.php */
/* Location: application/models/Location_model.php */