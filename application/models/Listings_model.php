<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Listings Model
 *
 * This class handles the database queries relating to listings.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Listings
 * @author        BitWasp
 *
 */
class Listings_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        $this->load->model('accounts_model');
    }

    /**
     * Add
     *
     * Add a new listing to the database.
     *
     * @access    public
     * @param    string $properties
     * @return    bool
     */
    public function add($properties)
    {
        $add = $this->db->insert('items', $properties);
        if ($add == TRUE)
            return $this->db->insert_id();

        return FALSE;
    }

    /**
     * Delete
     *
     * Delete a listing, where the listing belongs to the current user.
     *
     * @access    public
     * @param    string $hash
     * @return    bool
     */
    public function delete($hash)
    {
        $this->db->where('hash', $hash);
        $this->db->where('vendor_hash', $this->current_user->user_hash);
        return $this->db->delete('items') == TRUE;
    }

    /**
     * My Listings
     *
     * Loads a vendors listings into an array, or FALSE if they have none.
     *
     * @return    array/FALSE
     */
    public function my_listings()
    {
        $query = $this->db->select('id, hash, price, currency, hidden, category, name, description, main_image')
            ->where('vendor_hash', $this->current_user->user_hash)
            ->order_by('add_time', 'asc')
            ->get('bw_items');

        if ($query->num_rows() > 0) {
            $results = array();
            foreach ($query->result_array() as $row) {

                $row['description_s'] = substr(strip_tags($row['description']), 0, 50);
                if (strlen($row['description']) > 50) $row['description_s'] .= '...';

                $row['main_image'] = $this->images_model->get($row['main_image']);
                $row['currency'] = $this->bw_config->currencies[$row['currency']];
                $row['currency']['rate'] = $this->bw_config->exchange_rates[strtolower($row['currency']['code'])];

                $row['price_b'] = number_format(($row['price'] / $row['currency']['rate']), 8);
                $local_currency = $this->currencies_model->get($this->current_user->currency['id']);
                $price_l = ($row['price_b'] * $local_currency['rate']);
                $row['price_l'] = ($this->current_user->currency['id'] !== '0') ? number_format($price_l, 2) : number_format($price_l, 8);
                $row['price_f'] = $local_currency['symbol'] . '' . $row['price_l'];
                array_push($results, $row);
            }
            return $results;
        }

        return FALSE;
    }

    /**
     * Get
     *
     * Load an item if it belongs to the current user.
     *
     * @access    public
     * @param    string $hash
     * @return    array/FALSE
     */
    public function get($hash)
    {
        $this->load->model('currencies_model');

        $query = $this->db->where('vendor_hash', $this->current_user->user_hash)
            ->where('hash', $hash)
            ->get('items');

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $row['description'] = str_replace('<br />', '', $row['description']);
            $row['description_f'] = nl2br($row['description']);
            $row['vendor'] = $this->accounts_model->get(array('user_hash' => $row['vendor_hash']));
            $row['currency'] = $this->bw_config->currencies[$row['currency']];
            $row['currency']['rate'] = $this->bw_config->exchange_rates[strtolower($row['currency']['code'])];
            $row['price_b'] = $row['price'] / $row['currency']['rate'];

            $local_currency = $this->bw_config->currencies[$this->current_user->currency['id']];
            $local_rate = $this->bw_config->exchange_rates[strtolower($local_currency['code'])];
            $row['price_l'] = (float)round(($row['price_b'] * $local_rate), 8, PHP_ROUND_HALF_UP);
            $row['price_f'] = $local_currency['symbol'] . ' ' . $row['price_l'];

            $row['main_image_f'] = $this->images_model->get($row['main_image']);
            $row['images'] = $this->images_model->by_item($hash);

            return $row;
        }

        return FALSE;
    }

    /**
     * Update
     *
     * Update an item with an array of changes (index as column, val as val)
     *
     * @access    public
     * @param    string $item_hash
     * @param    array $changes
     * @return    bool
     */
    public function update($item_hash, $changes)
    {
        $this->db->where('hash', $item_hash);
        $this->db->where('vendor_hash', $this->current_user->user_hash);
        return $this->db->update('items', $changes) == TRUE;
    }

}

;

/* End of file Listings_model.php */
/* Location: application/models/Listings_model.php */