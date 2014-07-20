<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use BitWasp\BitcoinLib\BitcoinLib;
use BitWasp\BitcoinLib\RawTransaction;

/**
 * Transaction Cache Model
 *
 * This class handles the queries for handling cached transaction id's.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Transaction Cache
 * @author        BitWasp
 *
 */
class Transaction_cache_model extends CI_Model
{

    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
    }

    //////////////////////////////////////////////////////////////////
    // Cached lists of transactions from blocks.
    //////////////////////////////////////////////////////////////////

    /**
     * Add Cache List
     *
     * Supply an array, containing child-arrays containing information
     * about a transaction.
     *
     * @param    array $array
     * @return    boolean
     */
    public function add_cache_list($array)
    {
        return $this->db->insert_batch('transactions_block_cache', $array) == TRUE;
    }

    /**
     * Count Cache List
     *
     * Returns the total count of records in the block cache - displayed
     * on admin panel
     *
     * @return    int
     */
    public function count_cache_list()
    {
        return $this->db->select('id')
            ->from('transactions_block_cache')
            ->count_all_results();
    }

    /**
     * Delete List
     *
     * Deletes a list of cached transaction id's as specified by the
     * tx_id key in each child-array.
     *
     * @param    array $array
     * @return    boolean
     */
    public function delete_cache_list($array)
    {
        $this->db->where('tx_id', $array[0]['tx_id']);
        foreach (array_splice($array, 1) as $tx) {
            $this->db->or_where('tx_id', $tx['tx_id']);
        }
        return $this->db->delete('transactions_block_cache');
    }

    /**
     * Cache List
     *
     * This function loads the entire list of cached block transactions.
     *
     * @return    array
     */
    public function cache_list()
    {
        $query = $this->db->limit(1000)->get('transactions_block_cache');
        return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
    }

    //////////////////////////////////////////////////////////////////
    // Stored payments obtained through scraping the blockchain
    //////////////////////////////////////////////////////////////////

    /**
     * Add Payments List
     *
     * Record an array containing payments on addresses we are watching.
     *
     * @param    array $tx_array
     * @return    boolean
     */
    public function add_payments_list($tx_array)
    {
        $this->load->model('order_model');
        $this->load->model('users_model');
        $final = array();

        foreach ($tx_array as $payment) {

            // Check that we have not encountered this before.
            if ($this->check_already_have_payment($payment['tx_id'], $payment['vout']) == FALSE) {
                // Treat order and fee payments differently!
                if ($payment['purpose'] == 'order') {
                    $order = $this->order_model->get_order_by_address($payment['address']);
                    $order_total = ($order['price'] + $order['shipping_costs'] + $order['fees']) * 1e8;

                    // Sum up the total number of payments.
                    $total_payment = $payment['value'] * 1e8;
                    $order_payments = $this->payments_to_address($payment['address']);
                    foreach ($order_payments as $record) {
                        $total_payment += $record['value'] * 1e8;
                    }

                    // Set the order id for payment records.
                    $payment['order_id'] = $order['id'];

                    // If it's not already finalized, and payment is sufficient, record a paid order.
                    if ($order['finalized'] == '0' AND abs($order_total - $total_payment) <= 0.00000001)
                        $this->record_paid_order($order['id']);
                }

                if ($payment['purpose'] == 'fees') {
                    // Load the user hash.
                    $user_hash = $this->users_model->get_payment_address_owner($payment['address']);

                    // Check the user_hash and entry_payment exist..
                    if ($user_hash !== FALSE) {
                        $entry_payment = $this->users_model->get_entry_payment($user_hash);

                        if ($entry_payment !== FALSE) {

                            // Get current value, and add the previous ones.
                            $paid_amount = $payment['value'] * 1e8;
                            $fee_payments = $this->payments_to_address($payment['address']);
                            foreach ($fee_payments as $record) {
                                $paid_amount += $record['value'] * 1e8;
                            }

                            // If the paid amount is correct, let the user log in.
                            if ($paid_amount >= $entry_payment['amount'] * 1e8) {
                                $this->users_model->delete_entry_payment($user_hash);
                                $this->users_model->set_entry_paid($user_hash);
                            }
                        }
                    }

                    // Set user hash for payment records.
                    $payment['fees_user_hash'] = $user_hash;
                }

                $final[] = $payment;
            }
        }
        return count($final) == 0 OR $this->db->insert_batch('transactions_payments_cache', $final) == TRUE;
    }

    /**
     * Check Already Have Payment
     *
     * This function accepts a $tx_id, and $vout, and checks if we have
     * already recorded this input in the payments cache.
     *
     * @param    string $tx_id
     * @param    int $vout
     * @return    boolean
     */
    public function check_already_have_payment($tx_id, $vout)
    {
        return $this->db->where('tx_id', $tx_id)
            ->where('vout', $vout)
            ->from('transactions_payments_cache')
            ->count_all_results() > 0;
    }

    public function get_payment($tx_id)
    {
        return $this->db->get_where('transactions_payments_cache', array('tx_id' => $tx_id))->row_array();
    }

    /**
     * Payments To Address
     *
     * This function returns an array containing all inputs which we know
     * about that pay to a particular address. Returns an empty array if
     * no records exist.
     *
     * @param    string $address
     * @return    array
     */
    public function payments_to_address($address)
    {
        return $this->db->where('address', $address)
            ->get('transactions_payments_cache')
            ->result_array();
    }

    /**
     * Record Paid Order
     *
     * This is done in the add_payments_list() function. It adds a paid
     * order to a cache in the database, where it can be processed later
     * to have the unsigned transaction generated.
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function record_paid_order($order_id)
    {
        return $this->db->insert('paid_orders_cache', array('order_id' => $order_id)) == TRUE;
    }

    /**
     * Parse Outputs Into Array
     *
     * This function accepts $outputs - the [vout] array from a decoded
     * transaction. All vouts are converted into an individual row to
     * be stored if it pays to an address we care about, once the $txid
     * and $block_height are supplied.
     * This is done when we are creating a list of outputs which are
     * sent to our addresses.
     *
     * @param    string $txid
     * @param    int $block_height
     * @param    array $outputs
     * @return    array
     */
    public function parse_outputs_into_array($txid, $block_height, $outputs)
    {
        if (!is_array($outputs))
            return array();

        $addrs = array();

        foreach ($outputs as $v_out => $output) {
            // Only try to deal with what you can decode!

            if (isset($output['scriptPubKey']['addresses'][0]))
                $addrs[] = array('address' => $output['scriptPubKey']['addresses'][0],
                    'pkScript' => $output['scriptPubKey']['hex'],
                    'value' => $output['value'],
                    'tx_id' => $txid,
                    'vout' => $v_out,
                    'block_height' => $block_height);
        }

        return $addrs;
    }

    /**
     * Payments List
     *
     * This function loads a list of all the transactions which send to
     * addresses we care about. Returns an array containing two indexes:
     *  - tx_ids contains an array where each entry is a txid,
     *  - txs contains an array where each transaction is indexed by its txid.
     *
     * @param   string $param
     * @return    array
     */
    public function payments_list($param = FALSE)
    {
        if ($param !== FALSE)
            if (in_array($param, array('fees', 'order')))
                $this->db->where('purpose', $param);

        $query = $this->db->get('transactions_payments_cache');
        if ($query->num_rows() > 0) {
            $txid_list = array();
            $tx_list = array();
            foreach ($query->result_array() as $id => $tx) {
                $tx_list[$tx['tx_id']] = $tx;
                $txid_list[] = $tx['tx_id'];
            }
            return array('tx_ids' => $txid_list,
                'txs' => $tx_list);
        } else {
            return FALSE;
        }
    }

    ////////////////////////////////////////////////////////////////
    // Transaction broadcasting
    ////////////////////////////////////////////////////////////////
    /**
     * To Broadcast
     *
     * Accepts a raw $transaction hex, and adds it to a cache of transactions
     * to be broadcast whenever the process callback is run.
     *
     * @param $transaction
     * @return bool
     */
    public function to_broadcast($transaction)
    {
        return $this->db->insert('transactions_broadcast_cache', array('transaction' => $transaction)) == TRUE;
    }

    /**
     * Broadcast List
     *
     * Obtains the list of transactions to broadcast from the database
     * @return mixed
     */
    public function broadcast_list()
    {
        return $this->db->get('transactions_broadcast_cache')->result_array();
    }

    /**
     * Clear Broadcast List
     *
     * Accepts an array of id's of records in the transactions_broadcast_cache table
     * which are to be deleted.
     *
     * @param $id_array
     * @return bool
     */
    public function clear_broadcast_list($id_array)
    {
        return $this->db->where_in('id', $id_array)->delete('transactions_broadcast_cache') == TRUE;
    }

    /**
     * Update Broadcast List Remaining
     *
     * Update an array of id's for records in this table to a new 'attempts_remaining'
     * value.
     *
     * @param $id_array
     * @param $new_remaining
     * @return bool
     */
    public function update_broadcast_list_remaining($id_array, $new_remaining)
    {
        return $this->db->where_in('id', $id_array)
            ->set('attempts_remaining', $new_remaining)
            ->update('transactions_broadcast_cache') == TRUE;
    }

    ////////////////////////////////////////////////////////////////
    // 'Complete Order' Trigger
    ////////////////////////////////////////////////////////////////

    /**
     * Check Inputs Against Payments
     *
     * Accepts $inputs, which is directly taken from a decoded transaction.
     * $list is a the entire $this->payments_list(). Returns an array
     * of transaction row's which spend from multisignature addresses.
     *
     * @param    array $inputs
     * @param    array $list
     * @return    array
     */
    public function check_inputs_against_payments($inputs, $list)
    {
        $interesting = array();
        foreach ($inputs as $input) {
            if (isset($input['txid'])) {
                if (in_array($input['txid'], $list['tx_ids'])) {
                    if ($list['txs'][$input['txid']]['vout'] == $input['vout']) {
                        $input['assoc_address'] = $list['txs'][$input['txid']]['address'];
                        $interesting[] = $input;
                    }
                }
            }
        }
        return $interesting;
    }

    /**
     * Delete Finalized Record
     *
     * This function attempts to delete an order from the paid_orders_cache
     * once it has been processed. Returns a boolean indicating the outcome.
     *
     * @param    int $order_id
     * @return boolean
     */
    public function delete_finalized_record($order_id)
    {
        $this->db->where('order_id', "$order_id");
        return $this->db->delete('paid_orders_cache') == TRUE;
    }

    ////////////////////////////////////////////////////////////////
    //	Log Functions - Used to see if transactions were expected or not.
    //  Prevent people cheating the site
    ////////////////////////////////////////////////////////////////

    /**
     * Log Transaction
     *
     * This function converts a generated, unsigned transactions outputs
     * to a recognizable, and hopefuly reproducable hash whenever outgoing
     * payments are encountered in the future.
     *
     * @param        array $outputs
     * @param        string $multisig_address
     * @param        int $order_id
     * @return        boolean
     */
    public function log_transaction($outputs, $multisig_address, $order_id)
    {
        $outputs_arr = $this->outputs_to_log_array($outputs);
        $json = json_encode($outputs_arr);
        $hash = hash('sha256', $json);
        $arr = array('outputs_hash' => $hash,
            'address' => $multisig_address,
            'order_id' => $order_id);
        return $this->db->insert('transactions_expected_cache', $arr) == TRUE;
    }

    /**
     * Outputs to Log Array
     *
     * This function converts the outputs of a transaction to our log format.
     * This is an array containing entries of the format: array('address' => '', 'value' => '').
     *
     * This is hashed and stored in the database, to later confirm if
     * an input was spent correctly.
     *
     * @param    array $outputs
     * @return    array
     */
    public function outputs_to_log_array($outputs)
    {
        $array = array();
        foreach ($outputs as $vout => $output) {
            $array[] = array('address' => $output['scriptPubKey']['addresses'][0],
                'value' => number_format($output['value'], 8));
        }

        return $array;
    }

    /**
     * Search Log Hashes
     *
     * This function searches for a raw outputs_hash, to see if it
     * matches any on record.
     *
     * @param    string $outputs_hash
     * @return    ARRAY/FALSE
     */
    public function search_log_hashes($outputs_hash, $order_id)
    {
        $this->db->reset_query();
        $this->db->where('outputs_hash', "{$outputs_hash}");
        $this->db->where('order_id', "{$order_id}");
        $query = $this->db->get('transactions_expected_cache');
        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Clear Expected For Address
     *
     * Used to clear an expected outcome for inputs on a particular $address.
     * This is done when the admin creates a new transaction when resolving
     * a dispute. Returns a boolean indicating outcome.
     *
     * @param    string $address
     * @return    boolean
     */
    public function clear_expected_for_address($address)
    {
        $this->db->where('address', $address);
        return $this->db->delete('transactions_expected_cache') == TRUE;
    }

    /**
     * Check If Expected Spend
     *
     * This function takes a transactions ['vout'] array and checks if
     * this matches what we expected. Only done for order payments:
     * as the payments leaving these addresses would have been created
     * by the site, and have explicitly agreed outputs, any deviation
     * in the hash means the transaction has been tampered with.
     *
     * Also done to verify partially signed transactions.
     *
     * @param    array $output
     * @return    boolean
     */
    public function check_if_expected_spend($output, $order_id)
    {
        $hash = hash('sha256', json_encode($this->outputs_to_log_array($output)));
        echo $hash . "<br />";
        $search = $this->search_log_hashes($hash, $order_id);
        return ($search === FALSE) ? FALSE : $search['address'];
    }

    //
    // Chain reorg functions - not complete!!
    //

    /**
     * Check Block Seen
     *
     * This function checks if the supplied block hash has been seen
     * or not by returning a boolean.
     *
     * @param    string $block_hash
     * @return    boolean
     */
    public function check_block_seen($block_hash)
    {
        return $this->db->where('hash', $block_hash)
            ->from('blocks')
            ->count_all_results() > 0;
    }

    /**
     * Check Block Height Set
     *
     * Check if a particular height in the blockchain has already been
     * recorded.
     *
     * @param    int $height
     * @return    boolean
     */
    public function check_block_height_set($height)
    {
        return $this->db->where('height', $height)
            ->from('blocks')
            ->count_all_results() == 0;
    }

    /**
     * Add Seen Block
     *
     * Records a block into the blocks table..
     *
     * @param        string $block_hash
     * @param        int $height
     * @param        string $prev_hash
     * @return        boolean
     */
    public function add_seen_block($block_hash, $height, $prev_hash)
    {
        return $this->db->insert('blocks', array('hash' => $block_hash,
            'height' => $height,
            'prev_hash' => $prev_hash)) == TRUE;
    }

    /**
     * Last Added Block
     *
     * Return the last block added to the processed list.
     *
     * @return array/FALSE
     */
    public function last_added_block()
    {
        $query = $this->db->order_by('id', 'DESC')
            ->limit(1)
            ->get('blocks');
        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Block Info
     *
     * Loads stored information about a particular block.
     *
     * @param        array $info
     * @return        array/FALSE
     */
    public function block_info($info)
    {
        if (!isset($info['block']) && !isset($info['height']))
            return FALSE;

        (isset($info['block'])) ? $this->db->where('block', $info['block']) : $this->db->where('height', $info['height']);

        $query = $this->db->get('blocks');
        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }
}

;

/* End of file: Transaction_cache_model.php */
/* Location: application/models/Transaction_cache_model.php */
