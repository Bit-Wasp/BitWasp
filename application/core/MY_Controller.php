<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * CodeIgniter MY_Controller Class
 *
 * initializes common controller settings, this is to be derived by all controllers of this application
 *
 * @name    MY_Controller
 * @category    Core Libraries
 * @author  Md. Ali Ahsan Rana
 * @link    http://codesamplez.com/
 */

/**
 * @property Mysmarty $mysmarty
 */
class MY_Controller extends CI_Controller
{

    public $_partials;
    public $category = null;

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->_partials = array();

    }

    public function _partial($view_access_name, $page)
    {
        $this->_partials[$view_access_name] = $page;
    }

    /**
     * final view codes for showing template
     */
    protected function _render($template, $data = NULL)
    {
        $this->_template_data_array = $data;

        $info = json_decode($this->session->flashdata('returnMessage'));
        if (!isset($this->_template_data_array['returnMessage']) && count($info) !== 0 && isset($info->message)) {
            $this->smarty->assign('returnMessage', $info->message);
            $this->smarty->assign('returnMessage_class', (isset($info->class) ? $info->class : 'warning'));
        } else {
            $this->smarty->assign('returnMessage', '');
            $this->smarty->assign('returnMessage_class', '');
        }

        $this->_prepare_template();

        $page_title = '';
        $header_meta = '';
        if ($data != NULL) {
            //assigns all data as smarty variables. Reduces smarty assignment in controllers
            foreach ($this->_template_data_array as $key => $value) {
                if ($key == 'title') {
                    $page_title = $value;
                    continue;
                } else if ($key == 'header_meta') {
                    $header_meta = $value;
                    continue;
                }

                $this->smarty->assign($key, $value);
            }
        }

        $this->smarty->assign('header', array('title' => $page_title,
            'site_title' => $this->bw_config->site_title,
            'site_description' => $this->bw_config->site_description,
            'maintenance_mode' => $this->bw_config->maintenance_mode,
            'header_meta' => $header_meta));

        $this->smarty->assign('footer', array('price_index' => $this->bw_config->price_index,
            'exchange_rates' => $this->bw_config->exchange_rates,
            'currencies' => $this->bw_config->currencies));

        $this->_handle_partials();

        $this->smarty->display('header.tpl');
        $this->smarty->display($template . ".tpl");
        $this->smarty->display('footer.tpl');
    }

    protected function _prepare_template()
    {
        // Prepare variables needed on each page
        $this->smarty->assign('current_user', $this->current_user->status(), 'global');
        $this->smarty->assign('allow_guests', $this->bw_config->allow_guests);
        $this->smarty->assign('coin', $this->bw_config->currencies[0]);

        if (!isset($this->_template_data_array['currentCat']))
            $this->_template_data_array['currentCat'] = array();

        // Prepare menu & bar variables
        if ($this->current_user->logged_in()) {
            $this->smarty->assign('count_unread_messages', $this->general_model->count_unread_messages(), 'global');
            if ($this->current_user->user_role == 'Vendor')
                $this->smarty->assign('count_new_orders', $this->general_model->count_new_orders(), 'global');

            $categories = $this->categories_model->menu();
            $category_data = [
                'cats' => ((count($categories) > 0)
                        ? $this->_prepare_menu($categories, 0, $this->_template_data_array['currentCat'])
                        : 'No Categories'),
                'block' => FALSE,
                'js' => $this->_prepare_js_menu($this->bw_config->categories, $this->_template_data_array['currentCat']),
                'locations_select' => $this->location_model->generate_select_list($this->bw_config->location_list_source, 'location', 'span12'),
                'locations_w_select' => $this->location_model->generate_select_list($this->bw_config->location_list_source, 'location', 'span12', FALSE, array('worldwide' => TRUE))
            ];

            if (isset($this->_template_data_array['ship_from_error'])) {
                $category_data['ship_from_error'] = $this->_template_data_array['ship_from_error'];
                unset($this->_template_data_array['ship_from_error']);
            }

            if (isset($this->_template_data_array['ship_to_error'])) {
                $category_data['ship_to_error'] = $this->_template_data_array['ship_to_error'];
                unset($this->_template_data_array['ship_to_error']);
            }
        } else {

            $js_menu = $this->_prepare_js_menu($this->bw_config->categories, $this->_template_data_array['currentCat']);

            if ($this->bw_config->allow_guests == TRUE) {
                $categories = $this->categories_model->menu();
                $category_data = [
                    'cats' => ((count($categories) > 0)
                            ? $this->_prepare_menu($categories, 0, $this->_template_data_array['currentCat'])
                            : 'No Categories'),
                    'js' => $js_menu,
                    'block' => FALSE
                ];
            } else {
                $category_data['block'] = TRUE;
            }
        }
        $this->smarty->assign('category_data', $category_data);
    }

    public function _prepare_js_menu($categories)
    {
        $content = array();
        foreach ($categories as $category) {

            if (isset($categories[$category['parent_id']]))
            {
                $parent = & $categories[$category['parent_id']];
                $parent['count_child_items'] += $category['count_child_items'];
            }
        }

        foreach ($categories as $category) {
            $extra = array();
            if ($category['id'] == $this->category)
                $extra['selected'] = true;


         //   if ($category['count_child_items'] > 0){

                $content[$category['id']] = (object)array(
                    'id' => $category['id'],
                    'text' => $category['name'],
                    'hash' => $category['hash'],
                    'href' => base_url('category/'.$category['hash']),
                    'tags' => "".$category['count_child_items']."",
                    'parent_id' => $category['parent_id']
                );
           // }


        }

        // Store all child categories as an array $menu[parentID]['children']
        foreach ($content as &$menuItem) {
            if ($menuItem->parent_id !== '0')
                $content[$menuItem->parent_id]->nodes[] = & $menuItem;
        }

        // Remove child categories from the first level of the $menu[] array.
        foreach ($content as $c) {
            if ($c->parent_id != "0" OR $c->tags == 0)
                unset($content[$c->id]);
        }
        //$content = $this->_prepare_menu2($content, 0, array());
        return json_encode(json_decode(json_encode(array_values($content))));

    }

    public function _prepare_menu2($categories, $level, $parent)
    {
        $content = array();
        $level++;

        // Pregenerate the URL. Checks for trailing slashes, fixes up
        // issues when mod_rewrite is disabled.
        // Loop through each parent category
        foreach ($categories as $category) {
            if(isset($category['nodes']))
                $nodes = $this->_prepare_menu2($category['nodes'], $level, $params);

            //Check if were are currently viewing this category, if so, set it as active
            $build = new StdClass;
            $build->text = $category['text'];
            $build->href = base_url('category/'.$category['hash']);
            $build->tags = $category['tags'];


        }

        return $content;
    }

    /**
     * Menu
     *
     * A recursive function to generate a menu from an array of categories.
     * Uses each categories parent ID to determine where it should be placed.
     *
     * @param        array $categories
     * @param        int $level
     * @param        array $params
     * @return        string
     */
    public function _prepare_menu($categories, $level, $params)
    {
        $content = '';
        $level++;

        if ($level !== 1)
            $content .= "<ul>\n";

        // Pregenerate the URL. Checks for trailing slashes, fixes up
        // issues when mod_rewrite is disabled.
        // Loop through each parent category
        foreach ($categories as $category) {
            //Check if were are currently viewing this category, if so, set it as active
            $content .= "<li class='padd";
            if (isset($params['id'])) {
                if ($params['id'] == $category['id'])
                    $content .= " active";
            }
            $content .= "'>";

            // Display link if category contains items.
            $content .= ($category['count'] == 0) ? "<a href='#'>{$category['name']}   </a>" : anchor('category/' . $category['hash'], $category['name'] . ' (' . $category['count'] . ")");

            // Check if we need to recurse into children.
            if (isset($category['children']))
                $content .= $this->_prepare_menu($category['children'], $level, $params);

            $content .= "</li>\n";
        }

        if ($level !== 1)
            $content .= "</ul>\n";

        return $content;
    }

    public function _handle_partials()
    {
        // Load partial templates now that preloading is done.
        if (count($this->_partials) > 0) {
            foreach ($this->_partials as $variable_name => $page_to_render) {
                $a = $this->smarty->fetch($page_to_render . ".tpl");
                $this->smarty->assign($variable_name, $a);
            }
        }
    }
}
