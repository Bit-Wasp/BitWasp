<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Items Controller
 *
 * This class handles with requests for items.
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Items
 * @author        BitWasp
 *
 */
class Items extends MY_Controller
{

    /**
     * Constructor
     *
     * @access    public
     * @see        Models/Items_Model
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('items_model');
        $this->items_per_page = 6;
    }

    /**
     * Load all items.
     * NOTE: pagination to come soon.
     * URI: /items
     *
     * @access    public
     * @param        int $page
     */
    public function index($page = 1)
    {
        if (!(is_numeric($page) && $page > 0))
            $page = 1;

        $data['title'] = 'Items';
        $data['page'] = 'items/index';
        $data['links'] = $this->items_model->pagination_links(array(), site_url('items'), $this->items_per_page, 2);
        $data['items'] = $this->items_model->get_list_pages(array(), $page, $this->items_per_page);
        $this->_render($data['page'], $data);
    }

    /**
     * Load all items in a category.
     * URI: /category/$hash
     *
     * @access    public
     * @see        Models/Items_Model
     * @see        Models/Categories_Model
     *
     * @param    string $hash
     * @param    int $page
     * @return    void
     */
    public function category($hash, $page = 1)
    {
        if (!(is_numeric($page) && $page > 0))
            $page = 1;

        $this->load->model('categories_model');
        $data['category'] = $this->categories_model->get(array('hash' => "$hash"));
        if ($data['category'] == FALSE)
            redirect('items');

        $data['title'] = 'Items by Category: ' . $data['category']['name'];
        $data['custom_title'] = 'Category: ' . $data['category']['name'];
        $data['page'] = 'items/index';

        $data['links'] = $this->items_model->pagination_links(array('category' => $data['category']['id']), site_url("category/$hash"), $this->items_per_page, 3);
        $data['items'] = $this->items_model->get_list_pages(array('category' => $data['category']['id']), $page, $this->items_per_page);

        $this->_render($data['page'], $data);
    }

    /**
     * Location
     *
     * This function requires that $source is either 'ship-to' or 'ship-from'.
     * If not, it will redirect the user to the items page. It will then
     * check if a form was posted, and will then load the appropriate items,
     * or if the requested post $location doesn't exist, will redirect to items.
     * Otherwise, it can be called by supplying the parameters via GET.
     * URI: /location/$source/$location
     *
     * @access    public
     * @see        Models/Items_Model
     * @see        Models/Categories_Model
     *
     * @param    string $source
     * @param    string $location
     * @param    int $page
     */
    public function location($source = '', $location = NULL, $page = 1)
    {
        if (!(is_numeric($page) && $page > 0))
            $page = 1;

        // Set location to NULL if invalid. Will cause later function to fail.
        if (!(is_numeric($location) && $location >= 0))
            $location = NULL;

        // If the $source is invalid, redirect to the items page.
        if (!in_array($source, array('ship-to', 'ship-from')))
            redirect('items');

        $this->load->library('form_validation');
        $this->load->model('location_model');
        $this->load->model('shipping_costs_model');

        // Load any POSTed location information.
        if ($this->input->post('ship_to_submit') == 'Go') {
            if ($this->form_validation->run('ship_to_submit') == TRUE) {
                $data['location_name'] = $this->location_model->location_by_id($this->input->post('location'));
                if ($data['location_name'] == FALSE)
                    redirect('items');
                $items_config = array('item_id_list' => $this->shipping_costs_model->list_IDs_by_location($this->input->post('location')));
            } else {
                $data['ship_to_error'] = 'Please make a valid selection.';
            }
        } else if ($this->input->post('ship_from_submit') == 'Go') {
            if ($this->form_validation->run('ship_from_submit') == TRUE) {
                $data['location_name'] = $this->location_model->location_by_id($this->input->post('location'), TRUE);
                if ($data['location_name'] == FALSE)
                    redirect('items');
                $items_config = array('ship_from' => $this->input->post('location'));
            } else {
                $data['ship_from_error'] = 'Please make a valid selection.';
            }
        } else {
            $data['location_name'] = $this->location_model->location_by_id($location);
            if ($data['location_name'] == FALSE)
                redirect('items');

            if ($source == 'ship-to') {
                // Load the id's of items which are available in the $location
                $items_config = array('item_id_list' => $this->shipping_costs_model->list_IDs_by_location($location));
            } else if ($source == 'ship-from') {
                // Simply specify the item has ship_from=$location.
                $items_config = array('ship_from' => $location);
            }
        }

        // This will only be set once the location was valid.
        if (isset($items_config)) {
            $data['links'] = $this->items_model->pagination_links($items_config, site_url("location/{$source}/{$location}"), $this->items_per_page, 4);
            $data['items'] = $this->items_model->get_list_pages($items_config, $page, $this->items_per_page);

            // Set the appropriate titles.
            if ($source == 'ship-from') {
                $data['title'] = 'Items shipped from ' . $data['location_name'];
                $data['custom_title'] = 'Shipping From: ' . $data['location_name'];
            } else if ($source == 'ship-to') {
                $data['title'] = 'Items shipped to ' . $data['location_name'];
                $data['custom_title'] = 'Shipping To: ' . $data['location_name'];
            }

            $data['page'] = 'items/index';
        } else {
            $data['page'] = 'welcome_message';
        }
        $this->_render($data['page'], $data);
    }

    /**
     * Load a specific item
     * URI: /item/$hash
     *
     * @access    public
     * @see        Models/Items_Model
     *
     * @param    string $hash
     * @return    void
     */
    public function get($hash)
    {
        $this->load->helper('form');
        $this->load->model('shipping_costs_model');
        $this->load->model('review_model');

        $data['item'] = $this->items_model->get($hash, FALSE);
        if ($data['item'] == FALSE)
            redirect('');

        $data['page'] = 'items/get';
        $data['title'] = $data['item']['name'];
        $data['shipping_costs'] = $this->shipping_costs_model->for_item($data['item']['id']);

        $data['reviews'] = $this->review_model->random_latest_reviews(8, 'item', $hash);
        $data['review_count']['all'] = $this->review_model->count_reviews('item', $hash);
        $data['review_count']['positive'] = $this->review_model->count_reviews('item', $hash, 0);
        $data['review_count']['disputed'] = $this->review_model->count_reviews('item', $hash, 1);
        $data['average_rating'] = $this->review_model->current_rating('item', $hash);
        $data['vendor_rating'] = $this->review_model->current_rating('user', $data['item']['vendor']['user_hash']);

        $this->_render($data['page'], $data);
    }

}

;

/* End of File: Items.php */
/* Location: application/controllers/Items.php */
