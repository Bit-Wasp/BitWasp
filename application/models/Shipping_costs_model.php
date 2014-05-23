<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Shipping Costs Model
 *
 * Model to contain database queries for dealing with shipping costs.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Items
 * @author        BitWasp
 *
 */
class Shipping_costs_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Item List Cost
     *
     * This function calculates the shipping cost for an array of items.
     * It requires the users location.
     *
     * @param    array $item_list
     * @param    int $location
     * @return    float
     */
    public function costs_to_location($item_list, $location)
    {
        // Work out the cost in bitcoin.
        $cost = 0.00000000;
        foreach ($item_list as $item) {
            $find_cost = $this->find_location_cost($item['id'], $location);
            $cost += $find_cost['cost'] * $item['quantity'];
        }
        return $cost;
    }

    /**
     * Find Location Cost
     *
     * This function will load the list of shipping costs, and check if
     * the supplied user location is allowed, by being a child of a
     * location in the shipping costs list. Returns the cost info if
     * the user has an acceptable location, otherwise returns false.
     *
     * @param int $item_id
     * @param int $user_location_id
     * @return bool
     */
    public function find_location_cost($item_id, $user_location_id)
    {
        $this->load->model('location_model');
        $costs_list = $this->for_item($item_id);
        foreach ($costs_list as $destination_id => $cost_info) {
            if ($destination_id == 'worldwide')
                $worldwide = $cost_info;

            if ($this->location_model->validate_user_child_location($destination_id, $user_location_id) !== FALSE)
                $btc_cost = $cost_info;
        }
        if (!isset($btc_cost) AND isset($worldwide))
            return $worldwide;
        return (isset($btc_cost)) ? $btc_cost : FALSE;
    }

    /**
     * For Item
     *
     * Loads the raw array of shipping costs for a specified item. Then
     * it processes this, to convert the values to the BTC for universal use.
     * Returns an array if successful, or FALSE if no entries exist.
     *
     * @param    int $item_id
     * @param    boolean(opt) $all
     * @return    array/FALSE
     */
    public function for_item($item_id, $all = FALSE)
    {

        $query = $this->for_item_raw($item_id, $all);
        if ($query !== FALSE) {

            $item_hash = $this->get_item_hash($item_id);
            $item = $this->items_model->get($item_hash, FALSE);

            $output = array();
            foreach ($query as $res) {
                if ($item['currency'] !== '0') {
                    $currency = $this->bw_config->currencies[$item['currency']];
                    $btc_cost = $res['cost'] / $this->bw_config->exchange_rates[strtolower($currency['code'])];
                } else {
                    $currency = $this->bw_config->currencies[0];
                    $btc_cost = $res['cost'];
                }

                $output[$res['destination_id']] = array('cost' => round($btc_cost, 8, PHP_ROUND_HALF_UP),
                    'currency' => $currency,
                    'enabled' => $res['enabled'],
                    'destination_id' => $res['destination_id'],
                    'destination_f' => $res['destination_f']);
            }
            return $output;
        } else {
            return FALSE;
        }

    }

    /**
     * For Item Raw
     *
     * Load the raw array of shipping information for the $item_id. Returning
     * results for locations not currently on offer is possible by setting
     * $all to TRUE.
     *
     * This is used to display the shipping configuration format, where
     * converting to bitcoins is not desired if the item has a different
     * currency.
     * This information returned by this functioncanbe parsed by the for_item()
     * function.
     *
     * @param    int $item_id
     * @param    boolean(optional) $all
     * @return    array/FALSE
     */
    public function for_item_raw($item_id, $all = FALSE)
    {
        $query = $this->db->where('item_id', $item_id)
            ->get('shipping_costs');

        $results = array();
        foreach ($query->result_array() as $res) {
            $res['destination_f'] = ($res['destination_id'] == 'worldwide') ? 'Worldwide' : $this->bw_config->locations[$res['destination_id']]['location'];
            $results[] = $res;
        }
        return (count($results) > 0) ? $results : FALSE;
    }

    /**
     * Get Item Hash
     *
     * This helper function obtains the item hash for the item $id.
     * Returns the hash if the item exists, or FALSE on failure.
     *
     * @param    int $id
     * @return    string/FALSE
     */
    public function get_item_hash($id)
    {
        $query = $this->db->select('hash')->where('id', $id)->get('items');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['hash'];
        }
        return FALSE;
    }

    /**
     * List by Location
     *
     * Used by the items controller to find items available in a particular
     * location.
     *
     * @param    int $location_id
     * @return    array/FALSE
     */
    public function list_IDs_by_location($location_id)
    {
        $query = $this->db->where('destination_id', $location_id)
            ->get('shipping_costs');

        if ($query->num_rows() > 0) {
            $tmp = array();
            foreach ($query->result_array() as $item) {
                array_push($tmp, $item['item_id']);
            }
            return $tmp;
        }
        return FALSE;
    }

    /**
     * Update Shipping Costs
     *
     * Supply an array containing information about the costs for the
     * item. This function will delete any costs currenty held, and
     * replace them with the current information.
     *
     * @param int $cost_id
     * @param array $cost_array
     * @return bool
     */
    public function update($cost_id, $cost_array)
    {
        $this->db->where('id', $cost_id);
        return $this->db->update('shipping_costs', $cost_array) == TRUE;
    }

    /**
     * Insert
     *
     * Inserts a new shipping cost into the table.
     *
     * @param    array $array
     * @return    boolean
     */
    public function insert($array)
    {
        return $this->db->insert('shipping_costs', $array) == TRUE;
    }

    /**
     * Delete
     *
     * Deletes shipping cost $id.
     *
     * @param        int $id
     * @return        boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('shipping_costs') == TRUE;
    }
}

;

/* End of File: Shipping_costs_model.php */
/* Location: application/model/Shipping_costs_model.php */