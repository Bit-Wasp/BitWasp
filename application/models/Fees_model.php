<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Fees Model
 *
 * Model to contain database queries for dealing with fee's
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Fees
 * @author        BitWasp
 *
 */
class Fees_model extends CI_Model
{

    /**
     * Construct
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Delete
     *
     * This function deletes an entry in the fee's table, based on the $id.
     *
     * @param    int $id
     * @return    boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return ($this->db->delete('fees') == TRUE) ? TRUE : FALSE;
    }

    /**
     * Add
     *
     * This function adds an entry to the fee's table. $fee is an array
     * which contains keys for 'low', the lower limit of the range,
     * 'high', the upper limit of the range, and 'rate', the range.
     *
     * @param    array $fee
     * @return    boolean
     */
    public function add($fee)
    {
        return $this->db->insert('fees', $fee) == TRUE;
    }

    /**
     * Calculate
     *
     * Calculates the fee to be applied to an order of $order_price.
     *
     * @param    string $order_price
     * @return    int
     */
    public function calculate($order_price)
    {
        $fees_list = $this->fees_list();
        $default_rate = $this->bw_config->default_rate;
        $minimum_fee = $this->bw_config->minimum_fee;
        $order_price = round($order_price, 8, PHP_ROUND_HALF_UP);

        // Loop through fee list, checking if: fee['low'] < order_price <= fee[high]
        if ($fees_list !== FALSE) {
            foreach ($fees_list as $fee) {

                if ($fee['low'] < $order_price && $order_price <= $fee['high']) {
                    $rate = $fee['rate'];
                    $cost = $fee['rate'] / 100 * $order_price;
                    $cost = ($cost < $minimum_fee) ? $minimum_fee : $cost;
                    break;
                }
            }
        }

        if (!isset($cost)) {
            // Load the default rate as we haven't found an applicable fee range yet.
            $rate = $default_rate;
            $cost = $order_price * $default_rate / 100;
            $cost = ($cost < $minimum_fee) ? $minimum_fee : $cost; // comment this line so free items have free fee's
        }
        $cost = round($cost, 8, PHP_ROUND_HALF_UP);

        return $cost;
    }

    /**
     * Fees List
     *
     * This function loads an array containing information about the
     * fee's. fee[low] < order_price <= fee[high] will have rate fee[rate]
     * Returns an array on success, and FALSE on failure.
     *
     * @return    array/FALSE
     */
    public function fees_list()
    {
        $query = $this->db->order_by('low', 'ASC')->get('fees');
        return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
    }
}

;

/* End of File: Fees_model.php */
/* Location: application/models/Fees_model.php */