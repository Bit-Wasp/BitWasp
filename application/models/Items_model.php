<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Items Model
 *
 * Model to contain database queries for dealing with vendor listings.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Items
 * @author        BitWasp
 *
 */
class Items_model extends CI_Model
{

    /**
     * Constructor
     *
     * @access    public
     * @see        Models/Currencies_Model
     * @see        Models/Accounts_Model
     * @see        Models/Images_Model
     * @see        Models/Users_Model
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('currencies_model');
        $this->load->model('accounts_model');
        $this->load->model('images_model');
        $this->load->model('users_model');
    }

    /**
     * Delete
     *
     * Insert a new row of information about exchange rates.
     *
     * @access    public
     * @param    int $id
     * @return    bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return ($this->db->delete('items') == TRUE) ? TRUE : FALSE;
    }

    /**
     * Pagination Links
     *
     * Creates HTML links for pagination.
     *
     * @param    array $items_config
     * @param    string $base_url
     * @param    int $per_page
     * @param    int $url_segment
     * @return    string
     */
    public function pagination_links($items_config, $base_url, $per_page, $url_segment)
    {
        $this->load->library('pagination');
        $pagination = array();
        $pagination["base_url"] = $base_url;
        $pagination["total_rows"] = $this->get_count($items_config);
        $pagination["per_page"] = $per_page;
        $pagination["uri_segment"] = $url_segment;
        $pagination["num_links"] = round($pagination["total_rows"] / $pagination["per_page"]);
        $this->pagination->initialize($pagination);
        return $this->pagination->create_links();
    }

    /**
     * Get Count
     *
     * Accepts an array containing the options for items, and returns
     * the total number of requests.
     *
     * @param    $opt
     * @return    int
     */
    public function get_count($opt = array())
    {
        $this->db->select('id')
            ->where('hidden', '0');

        // Add on extra options.
        if (count($opt) > 0) {
            // If there is a list of item ID's to load..
            if (isset($opt['item_id_list'])) {
                if (is_array($opt['item_id_list']) && count($opt['item_id_list']) > 0) {
                    $this->db->where_in('id', $opt['item_id_list']);
                }
                // Remove this option to avoid issues with the next step.
                unset($opt['item_id_list']);
            }

            foreach ($opt as $key => $val) {
                $this->db->where("$key", "$val");
            }
        }
        $this->db->order_by('add_time DESC');
        $this->db->from("items");
        return $this->db->count_all_results();
    }

    /**
     * Get List
     *
     * Loads all possible items.
     *
     * @return    array
     */
    public function get_list($opt = array())
    {
        return $this->get_list_pages($opt, 0, 10000);
    }

    /**
     * Get list of items. (need to build in pagination).
     *
     * Display all items which are not hidden, or for a banned user.
     *
     * @access    public
     * @param    array $opt
     * @param    array $start
     * @param    int $per_page
     * @return    boolean
     */
    public function get_list_pages($opt = array(), $start, $per_page)
    {
        $limit = $per_page;

        $this->db->select('items.id, items.hash, price, vendor_hash, currency, description, hidden, category, items.name, add_time, update_time, description, main_image, users.user_hash, users.user_name, users.banned, images.hash as image_hash, images.encoded as image_encoded, images.height as image_height, images.width as image_width, currencies.code as currency_code')
            ->where('hidden', '0')
            ->order_by('add_time DESC')
            ->join('users', 'users.user_hash = items.vendor_hash AND bw_users.banned = \'0\'')
            ->join('images', 'images.hash = items.main_image')
            ->join('currencies', 'currencies.id = items.currency')
            ->limit($limit, $start);

        // Add on extra options.
        if (count($opt) > 0) {
            // If there is a list of item ID's to load..
            if (isset($opt['item_id_list'])) {
                if (is_array($opt['item_id_list']) && count($opt['item_id_list']) > 0) {
                    $this->db->where_in('items.id', $opt['item_id_list']);
                } else {
                    $this->db->reset_query();
                    return FALSE;
                }

                // Remove this option to avoid issues with the next step.
                unset($opt['item_id_list']);
            }

            foreach ($opt as $key => $val) {
                $this->db->where("$key", "$val");
            }
        }

        // Get the list of items.
        $query = $this->db->get('items');

        $results = array();

        if ($query->num_rows() > 0) {
            $local_currency = $this->bw_config->currencies[$this->current_user->currency['id']];
            $local_rate = $this->bw_config->exchange_rates[strtolower($local_currency['code'])];

            foreach ($query->result_array() as $row) {
                // get vendor information
                $row['vendor'] = array();
                $row['vendor']['user_name'] = $row['user_name'];
                $row['vendor']['user_hash'] = $row['user_hash'];
                $row['vendor']['banned'] = $row['banned'];

                // get main image information
                $row['main_image'] = array();
                $row['main_image']['hash'] = $row['image_hash'];
                $row['main_image']['encoded'] = $row['image_encoded'];
                $row['main_image']['height'] = $row['image_height'];
                $row['main_image']['width'] = $row['image_width'];

                $row['description_s'] = strip_tags($row['description']);
                $row['description_s'] = strlen($row['description_s']) > 70 ? substr($row['description_s'], 0, 70) . "..." : $row['description_s'];

                $rate = $this->bw_config->exchange_rates[strtolower($row['currency_code'])];

                // Load information about the items.
                $row['description_s'] = substr(strip_tags($row['description']), 0, 70);
                if (strlen($row['description']) > 70) $row['description_s'] .= '...';
                $row['price_b'] = number_format(($row['price'] / $rate), 8);
                $row['price_l'] = ($this->current_user->currency['id'] !== '0') ? number_format((float)($row['price_b'] * $local_rate), 2) : number_format((float)($row['price_b'] * $local_rate), 8);
                $row['price_f'] = $local_currency['symbol'] . ' ' . $row['price_l'];

                // being used anywhere?
                // $row['images'] = $this->images_model->by_item($row['id']);
                $results[] = $row;
            }
        }

        return $results;

    }

    /**
     * Get
     *
     * Get information about an item (by $hash).
     *
     * @param $hash
     * @param bool $hidden
     * @return bool
     */
    public function get($hash, $hidden = TRUE)
    {
        if ($hidden == TRUE)
            $this->db->where('hidden', '0');

        $this->db->select('items.id, items.hash, price, vendor_hash, prefer_upfront, currency, hidden, category, items.name, ship_from, add_time, update_time, description, main_image, users.user_hash, users.user_name, users.id as vendor_id, users.banned, images.hash as image_hash, images.encoded as image_encoded, images.height as image_height, images.width as image_width')
            ->where('items.hash', $hash)
            ->order_by('add_time DESC')
            ->join('users', 'users.user_hash = items.vendor_hash AND bw_users.banned = \'0\'')
            ->join('images', 'images.hash = items.main_image')
            ->limit(1, 'id desc');

        $query = $this->db->get('items');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();

            $row['vendor'] = array();
            $row['vendor']['user_name'] = $row['user_name'];
            $row['vendor']['user_hash'] = $row['user_hash'];
            $row['vendor']['id'] = $row['vendor_id'];

            // get main image information
            $row['main_image'] = array();
            $row['main_image']['hash'] = $row['image_hash'];
            $row['main_image']['encoded'] = $row['image_encoded'];
            $row['main_image']['height'] = $row['image_height'];
            $row['main_image']['width'] = $row['image_width'];

            $row['description'] = strip_tags(nl2br($row['description']), '<br>');
            $row['description_f'] = $row['description'];

            // Load information about the items.
            $row['description_s'] = substr(strip_tags($row['description']), 0, 70);
            if (strlen($row['description']) > 70) $row['description_s'] .= '...';

            $currency = $this->bw_config->currencies[$row['currency']];
            $rate = $this->bw_config->exchange_rates[strtolower($currency['code'])];

            $row['price_b'] = number_format(($row['price'] / $rate), 8);
            $row['price_l'] = ($this->current_user->currency['id'] != '0')
                ? number_format((float)($row['price_b'] * $this->current_user->currency['rate']), 2)
                : number_format((float)($row['price_b'] * $this->current_user->currency['rate']), 8);
            $row['price_f'] = $this->current_user->currency['symbol'] . ' ' . $row['price_l'];

            $row['images'] = $this->images_model->by_item($row['hash']);

            $row['add_time_f'] = $this->general->format_time($row['add_time']);
            $row['ship_from_f'] = $this->bw_config->locations[$row['ship_from']]['location'];
            $row['update_time_f'] = $this->general->format_time($row['update_time']);

            return $row;
        }

        return FALSE;
    }

    /**
     * By User
     *
     * Load listings as displayed by a user.
     *
     * @access    public
     * @param    string $user_hash
     * @return    array/FALSE
     */
    public function by_user($user_hash)
    {
        $query = $this->db->select('id, hash, price, currency, hidden, category, name, description, main_image')
            ->where('vendor_hash', $user_hash)
            ->where('hidden !=', '1')
            ->order_by('add_time', 'asc')
            ->get('bw_items');

        if ($query->num_rows() > 0) {
            $results = array();
            foreach ($query->result_array() as $row) {

                $row['description_s'] = strip_tags($row['description_s']);
                $row['description_s'] = strlen($row['description_s']) > 50 ? substr($row['description_s'], 0, 50) . "..." : $row['description_s'];

                $row['main_image'] = $this->images_model->get($row['main_image']);
                $row['currency'] = $this->currencies_model->get($row['currency']);

                $row['price_b'] = $row['price'] / $row['currency']['rate'];
                $local_currency = $this->currencies_model->get($this->current_user->currency['id']);
                $row['price_l'] = (float)($row['price_b'] * $local_currency['rate']);
                $row['price_f'] = $local_currency['symbol'] . '' . $row['price_l'];
                array_push($results, $row);
            }
            return $results;
        }

        return FALSE;
    }

}

;

/* End of File: Items_Model.php */
