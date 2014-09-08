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

    public $l_short_description = 70;
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
    public function get_count($opt = array(), $joins = null)
    {
        $this->db->select('id, vendor_hash')
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
        if (is_array($joins)) {
            foreach ($joins as $join) {
                (isset($join['type']))
                    ? $this->db->join($join['table'], $join['on'], $join['type'])
                    : $this->db->join($join['table'], $join['on']);
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
    public function get_list_pages($opt = array(), $page, $per_page, $hidden_allowed = FALSE)
    {
        $limit = $per_page;

        if (!$hidden_allowed)
            $this->db->where('hidden', '0');

        $this->db->select('items.id, items.hash, price, vendor_hash, currency, description, hidden, category, items.name, add_time, update_time, description, main_image, users.user_hash, users.user_name, users.banned, images.hash as image_hash, images.encoded as image_encoded, images.height as image_height, images.width as image_width, currencies.code as currency_code, (SELECT count(bw_reviews.id)) as review_count')
            ->order_by('add_time DESC')
            ->select_avg('reviews.average_rating')
            ->from('items')
            ->join('users', 'users.user_hash = items.vendor_hash AND bw_users.banned = \'0\'')
            ->join('reviews', 'reviews.subject_hash = items.hash AND bw_reviews.review_type = \'item\' AND bw_reviews.timestamp < \'' . time() . '\'', 'left')
            ->group_by('items.id')
            ->join('images', 'images.hash = items.main_image', 'left')
            ->join('currencies', 'currencies.id = items.currency');

        ($page > 1) ? $this->db->limit($limit, (($page - 1) * $per_page)) : $this->db->limit($limit);

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
        $query = $this->db->get();

        $results = array();

        if ($query->num_rows() > 0) {

            $local_currency = $this->bw_config->currencies[$this->current_user->currency['id']];
            $local_rate = $this->bw_config->exchange_rates[strtolower($local_currency['code'])];

            foreach ($query->result_array() as $row) {
                // Get vendor information
                $row['vendor'] = array();
                $row['vendor']['user_name'] = $row['user_name'];
                $row['vendor']['user_hash'] = $row['user_hash'];
                $row['vendor']['banned'] = $row['banned'];

                // Get main image information
                $row['main_image'] = array();
                $row['main_image']['hash'] = $row['image_hash'];
                $row['main_image']['encoded'] = $row['image_encoded'];
                $row['main_image']['height'] = $row['image_height'];
                $row['main_image']['width'] = $row['image_width'];

                $row['description_s'] = format_short_description($row['description'], $this->l_short_description);

                // Calculate Bitcoin price
                $rate = $this->bw_config->exchange_rates[strtolower($row['currency_code'])];
                $row['price_b'] = number_format(($row['price'] / $rate), 8);

                // Set a formatted price, in the users native currency.
                $row['price_l'] = ($this->current_user->currency['id'] !== '0') ? number_format((float)($row['price_b'] * $local_rate), 2) : number_format((float)($row['price_b'] * $local_rate), 8);
                $row['price_f'] = $local_currency['symbol'] . ' ' . $row['price_l'];

                $results[] = $row;
            }
        }

        return $results;

    }

    /**
     * Get
     *
     * Get information about an item (by $hash). If $hidden is set to TRUE,
     * hidden items are allowed in results (eg, if the user visits that items URL
     * If set to false it will be disabled.
     *
     * $order_fx/$order_price is set when looking up items in an order which is confirmed
     * by the buyer.
     * In this case, $order_fx will be set to the exchange rate at the time of the order.
     * In all other cases this should be set to FALSE to use the currency exchange rates.
     *
     * @param $hash
     * @param bool $hidden
     * @param bool|float $order_fx
     * @param bool|float $order_price
     * @return bool
     */
    public function get($hash, $hidden = TRUE, $order_fx = FALSE, $order_price = FALSE)
    {
        if ($hidden == TRUE)
            $this->db->where('hidden', '0');

        $this->db->select('items.id, items.hash, price, vendor_hash, prefer_upfront, currency, hidden, category, items.name, ship_from, add_time, update_time, description, main_image, users.user_hash, users.user_name, users.id as vendor_id, users.banned, images.hash as image_hash, images.encoded as image_encoded, images.height as image_height, images.width as image_width, (SELECT count(bw_reviews.id)) as review_count')
            ->where('items.hash', $hash)
            ->from('items')
            ->order_by('add_time DESC')
            ->select_avg('reviews.average_rating')
            ->join('users', 'users.user_hash = items.vendor_hash AND bw_users.banned = \'0\'')
            ->join('images', 'images.hash = items.main_image')
            ->join('reviews', 'reviews.subject_hash = items.hash AND bw_reviews.review_type = \'item\' AND bw_reviews.timestamp < \'' . time() . '\'', 'left')
            ->group_by('items.id')
            ->limit(1, 'id desc');

        $query = $this->db->get();
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

            $row['description_s'] = format_short_description($row['description'], $this->l_short_description);

            // Determine price of item: Current price if $order_fx is FALSE
            // Otherwise work out price using $order_fx as the exchange rate
            $currency = $this->bw_config->currencies[$row['currency']];
            if($order_fx == FALSE) {
                $rate = $this->bw_config->exchange_rates[strtolower($currency['code'])];
                $price = $row['price']/$rate;
            } else {
                // Use whatever parameters are passed.
                $rate = $order_fx;
                $price = $order_price/$rate;
            }

            $row['price_b'] = $price;
            $row['price_l'] = ($this->current_user->currency['id'] != '0')
                ? number_format((float)($row['price_b'] * $this->current_user->currency['rate']), 2)
                : number_format((float)($row['price_b'] * $this->current_user->currency['rate']), 8);
            $row['price_f'] = $this->current_user->currency['symbol'] . ' ' . htmlentities($row['price_l']);
            $row['images'] = $this->images_model->by_item($row['hash']);
            $row['add_time_f'] = $this->general->format_time($row['add_time']);
            $row['ship_from_f'] = $this->bw_config->locations[$row['ship_from']]['location'];
            $row['update_time_f'] = $this->general->format_time($row['update_time']);

            return $row;
        }

        return FALSE;

    }

};

/* End of File: Items_Model.php */
