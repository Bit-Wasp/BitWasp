<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use BitWasp\BitcoinLib\BitcoinLib;
use BitWasp\BitcoinLib\Electrum;
use BitWasp\BitcoinLib\BIP32;

/**
 * Bitcoin Model
 *
 * This class handles the database queries relating to orders.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Bitcoin
 * @author        BitWasp
 *
 */
class Bitcoin_model extends CI_Model
{

    /**
     * Construct
     *
     * @access    public
     * @return    void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Fees Address
     *
     * This function generates the next fees address from the electrum
     * MPK, logs the usage, and adds the address to the watch address
     * list.
     *
     * @param    string $user_hash
     * @param    string $magic_byte
     * @return    string
     */
    public function get_fees_address($user_hash, $magic_byte)
    {
        // Take the next electrum key.
        $key_data = $this->get_next_key();
        $address = BitcoinLib::public_key_to_address($key_data['public_key'], $magic_byte);

        // Log electrum usage
        $this->bitcoin_model->log_key_usage('fees', $this->bw_config->electrum_mpk, $key_data['iteration'], $key_data['public_key'], FALSE, $user_hash);
        // Add the address to the watch list.
        $this->add_watch_address($address, 'fees');

        return $address;
    }

    /**
     * Get Next Key
     *
     * Loads the next electrum public key from the MPK, and updates the
     * config details.
     *
     * @return    $array
     */
    public function get_next_key()
    {
        $i = $this->bw_config->electrum_iteration;
        $public_key = Electrum::public_key_from_mpk($this->bw_config->electrum_mpk, $this->bw_config->electrum_iteration, 0, FALSE);
        if ($public_key == FALSE)
            return FALSE;

        $this->config_model->update(array('electrum_iteration' => ($this->bw_config->electrum_iteration + 1)));
        return array('public_key' => $public_key,
            'iteration' => $this->bw_config->electrum_iteration);
    }


    /**
     * Add Watch Address
     *
     * @param    string $address
     * @param    string $purpose
     * @return    boolean
     */
    public function add_watch_address($address, $purpose)
    {
        return ($this->db->insert('watched_addresses', array('address' => "$address", 'purpose' => "$purpose")) == TRUE) ? TRUE : FALSE;
    }

    /**
     * Log Key Usage
     *
     * @param    string $usage
     * @param    string $mpk
     * @param    int $iteration
     * @param    string $public_key
     * @param    int $order_id
     * @param    string $user_hash
     * @return    boolean
     */
    public function log_key_usage($usage, $mpk, $iteration, $public_key, $order_id = FALSE, $user_hash = FALSE)
    {
        if (!in_array($usage, array('fees', 'order')))
            return FALSE;
        if ($usage == 'order' && $order_id == FALSE)
            return FALSE;
        if ($usage == 'fees' && $user_hash == FALSE)
            return FALSE;

        $coin = $this->bw_config->currencies[0];
        $address = BitcoinLib::public_key_to_address($public_key, $coin['crypto_magic_byte']);

        $order_id = ($usage == 'order') ? $order_id : '';
        $user_hash = ($usage == 'fees') ? $user_hash : '';

        $log = array('usage' => $usage,
            'mpk' => $mpk,
            'iteration' => $iteration,
            'public_key' => $public_key,
            'address' => $address,
            'order_id' => $order_id,
            'fees_user_hash' => $user_hash);

        return ($this->db->insert('key_usage', $log) == TRUE) ? TRUE : FALSE;

    }

    /**
     * Count Key Usage
     *
     * This function loads the total count of electrum usage logs, for
     * use with the pagination functions
     *
     * @return    int
     */
    public function count_key_usage()
    {
        return $this->db->from('key_usage')->count_all_results();
    }

    /**
     * Get Key Usage Page
     *
     * This function accepts the parameters $per_page, the number of records
     * to display per page, and the index to start at, $start.  Returns an
     * array of records.
     *
     * @param    int $per_page
     * @param    int $start
     * @return    array
     */
    public function get_key_usage_page($per_page, $start)
    {
        // Pagination
        return $this->db->order_by('id DESC')->limit($per_page, $start)->get('key_usage')->result_array();;
    }

    /**
     * Watch Address List
     *
     * @return array
     */
    public function watch_address_list()
    {
        $query = $this->db->get('watched_addresses');
        $query_array = $query->result_array();

        $results = array('data' => array(),
            'addresses' => array());

        foreach ($query_array as $res) {
            $results['data'][$res['address']] = $res;
            $results['addresses'][] = $res['address'];
        }

        return $results;
    }

    /**
     * Get Watch Address
     *
     * This function accepts an address, and searches for it in the
     * bw_watched_addresses table, to return its purpose, and id.
     *
     * @param    string $address
     * @return    array/FALSE
     */
    public function get_watch_address($address)
    {
        $this->db->where('address', $address);
        $query = $this->db->get('watched_addresses');
        return ($query->num_rows() == 0) ? FALSE : $query->row_array();
    }

    /**
     * Delete Watch Address
     *
     * This function removes the entry for $address from the watched_addresses
     * table. This means that no more payments to this address will be
     * recorded.
     *
     * @param        string $address
     * @return        boolean
     */
    public function delete_watch_address($address)
    {
        $this->db->where('address', $address);
        return ($this->db->delete('watched_addresses') == TRUE) ? TRUE : FALSE;
    }

};


/* End of file: bitcoin_model.php */
/* Location application/models/Bitcoin_model.php */