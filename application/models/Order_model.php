<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use BitWasp\BitcoinLib\BitcoinLib;
use BitWasp\BitcoinLib\RawTransaction;

/**
 * Order Model
 *
 * This class handles the database queries relating to orders.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Order
 * @author        BitWasp
 *
 */
class Order_model extends CI_Model
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
    }

    /**
     * Add
     *
     * Adds an order to the database. Columns are specified by array keys.
     * Returns a boolean.
     *
     * @param    array $order
     * @return    bool
     */
    public function add($order)
    {
        $order['time'] = time();
        $order['created_time'] = time();
        return $this->db->insert('orders', $order) == TRUE;
    }

    /**
     * My Orders
     *
     * Loads the current vendors orders.
     * Returns an array on success and FALSE on failure.
     *
     * @return    array/FALSE
     */
    public function vendor_orders()
    {
        $query = $this->db->where('vendor_hash', $this->current_user->user_hash)
            ->where('progress >', '0')
            ->order_by('time desc')
            ->get('orders');

        if ($query->num_rows() > 0) {
            $row = $query->result_array();
            return $this->build_array($row);
        } else {
            return array();
        }
    }

    /**
     * Build Array
     *
     * Used to build an array of orders into a more readable array.
     * Contains information about the vendor, the items (removes vendor
     * entry from each item).
     *
     * @param    array $orders
     * @return    array/FALSE
     */
    public function build_array($orders)
    {
        $this->load->model('accounts_model');

        if (count($orders) > 0) {
            $i = 0;
            $item_array = array();

            // Loop through each order.
            foreach ($orders as $order) {
                // Extract product hash/quantities.
                $items = explode(":", $order['items']);
                $j = 0;

                $price_b = 0.00000000;
                $price_l = 0.00000000;
                foreach ($items as $item) {
                    // Load each item & quantity.
                    $array = explode("-", $item);
                    $quantity = $array[1];

                    if($order['progress'] == '0') {
                        $item_info = $this->items_model->get($array[0], FALSE, FALSE);
                    } else {
                        if(isset($array[2]) AND isset($array[3]))
                            $item_info = $this->items_model->get($array[0], FALSE, $array[2], $array[3]);
                        else
                            $item_info = $this->items_model->get($array[0], FALSE, FALSE);
                    }

                    // If the item no longer exists, display a message.
                    if ($item_info == FALSE) {
                        $message = "Item ";
                        $message .= (strtolower($this->current_user->user_role) == 'vendor') ? 'has been removed' : 'was removed, contact your vendor';
                        $item_array[$j] = array('hash' => 'removed',
                            'name' => $message);
                    } else {
                        // Remove the vendor array, reduces the size of responses.
                        unset($item_info['vendor']);
                        $item_array[$j] = $item_info;
                    }

                    $item_array[$j++]['quantity'] = $quantity;
                }

                // Determine the progress message. Contains a status update
                // for the order, and lets the user progress to the next step.
                switch ($order['progress']) {
                    case '0': // Buyer choses items. (1)
                        $buyer_progress_message = 'Confirm your order to proceed.';
                        $vendor_progress_message = '';
                        // no vendor progress message
                        break;
                    case '1': // Vendor must chose escrow, or up-front. (2)
                        $buyer_progress_message = 'Awaiting vendor response. <input type="submit" class="btn btn-mini" name="cancel[' . $order['id'] . ']" value="Cancel" /> ';
                        $vendor_progress_message = "Accept order to continue.";
                        break;
                    case '2': // Buyer must pay to address. Escrow: 4. Upfront: 3.
                        $buyer_progress_message = (($order['vendor_selected_escrow'] == '0') ? 'Early finalization requested. ' : 'Escrow Transaction: ') . 'Pay to address. ';
                        $vendor_progress_message = 'Waiting for buyer to pay to the order address. <input type="submit" class="btn btn-mini" name="cancel[' . $order['id'] . ']" value="Cancel" /> ';
                        break;
                    case '3': // An up-front payment. Buyer signs first.
                        $buyer_progress_message = (($order['vendor_selected_upfront'] == '1') ? 'Vendor requested up-front payment.' : '') . " Please sign transaction. ";
                        $vendor_progress_message = "Waiting on buyer to sign. ";
                        break;
                    case '4': // Awaiting dispatch. Vendor must sign to indicate dispatch. (5)
                        $buyer_progress_message = "Awaiting Dispatch. ";
                        $vendor_progress_message = "Sign " . (($order['vendor_selected_upfront'] == '1') ? ' & broadcast' : '') . " the transaction to confirm the items dispatch. ";
                        break;
                    case '5': // Awaiting delivery. Escrow: buyer finalizes or disputes.
                        // Upfront: buyer can dispute or mark received.
                        $buyer_progress_message = 'Order dispatched. ' . (($order['vendor_selected_upfront'] == '1') ? 'Click to confirm receipt of the goods, or raise a dispute' : 'Sign & broadcast once received, or raise a dispute');
                        $vendor_progress_message = 'Buyer awaiting delivery. ';
                        break;
                    case '6': // Disputed transaction.
                        $buyer_progress_message = "Disputed transaction. ";
                        $vendor_progress_message = "Disputed transaction. ";
                        break;
                    case '7':
                        $buyer_progress_message = ($order['refund_time'] !== '') ? "Payment refunded." : "Purchase complete.";
                        $vendor_progress_message = ($order['refund_time'] !== '') ? "Order refunded." : "Order complete.";
                        break;
                    case '8':
                        $buyer_progress_message = "Awaiting refund.";
                        $vendor_progress_message = "Awaiting refund.";
                }

                $currency = $this->bw_config->currencies[$order['currency']];

                // Work out what price to display for the current user.
                $order_price = ($this->current_user->user_role == 'Vendor') ? ($order['price'] + $order['shipping_costs'] - $order['extra_fees']) : ($order['price'] + $order['shipping_costs'] + $order['fees']);

                // Convert price to bitcoin.
                $order_price = ($currency['id'] !== '0') ? $order_price / $this->bw_config->currencies[$order['currency']]['rate'] : number_format($order_price, 8);

                // Load the users local currency.
                // Convert the order's price into the users own currency.
                $price_l = ($order_price * $this->bw_config->exchange_rates[strtolower($this->current_user->currency['code'])]);
                $price_l = ($this->current_user->currency['id'] !== '0') ? number_format($price_l, 2) : number_format($price_l, 8);

                // Add extra details to the order.
                $tmp = $order;
                $tmp['vendor'] = $this->accounts_model->get(array('user_hash' => $order['vendor_hash']));
                $tmp['buyer'] = $this->accounts_model->get(array('id' => $order['buyer_id']));
                $tmp['public_keys'] = $this->load_public_keys($order['id']);
                $tmp['items'] = $item_array;
                $tmp['order_price'] = $order_price;
                $tmp['vendor_fees'] = $order['fees'] + $order['extra_fees'];
                $tmp['total_paid'] = number_format($order['price'] + $order['shipping_costs'] + $order['fees'], 8);
                $tmp['price_l'] = $price_l;
                $tmp['currency'] = $currency;
                $tmp['time_f'] = $this->general->format_time($order['time']);
                $tmp['partially_signed_time_f'] = $this->general->format_time($order['partially_signed_time']);
                $tmp['created_time_f'] = $this->general->format_time($order['created_time']); // 0
                $tmp['confirmed_time_f'] = $this->general->format_time($order['confirmed_time']); // 1
                $tmp['selected_payment_type_time'] = $this->general->format_time($order['selected_payment_type_time']); // 2
                $tmp['paid_time_f'] = $this->general->format_time($order['paid_time']); // 3
                $tmp['dispatched_time_f'] = $this->general->format_time($order['dispatched_time']); // 5
                $tmp['received_time_f'] = $this->general->format_time($order['received_time']); // 6
                $tmp['disputed_time_f'] = $this->general->format_time($order['disputed_time']); // 6
                $tmp['finalized_time_f'] = $this->general->format_time($order['dispatched_time']); // 7
                $tmp['progress_message'] = ($this->current_user->user_role == 'Vendor') ? $vendor_progress_message : $buyer_progress_message;

                $orders[$i++] = $tmp;
                unset($item_array);
                unset($tmp);
            }
            return $orders;

        } else {
            return FALSE;
        }
    }

    /**
     * My Purchases
     *
     * Returns the current buyers purchases on success, and FALSE if there
     * are none.
     *
     * @return    array/FALSE
     */
    public function buyer_orders()
    {
        $query = $this->db->where('buyer_id', $this->current_user->user_id)
            ->order_by('time desc')
            //->order_by('progress asc, time desc')
            ->get('orders');
        #echo $this->db->last_query();
        return ($query->num_rows() > 0) ? $this->build_array($query->result_array()) : array();

    }

    /**
     * Load
     *
     * Buyer can load an order about them, as specified by $vendor_hash,
     * and a progress $progress.
     * This is needed when the buyer is making a purchase with a vendor,
     * to see if any order exists already.
     *
     * @param    string $vendor_hash
     * @param    int $progress
     * @return    array/FALSE
     */
    public function load($vendor_hash, $progress)
    {
        $result = $this->build_array($this->db->where('vendor_hash', $vendor_hash)
            ->where('buyer_id', $this->current_user->user_id)
            ->where('progress', $progress)
            ->get('orders')
            ->result_array());
        return $result[0];
    }

    public function load_public_keys($order_id)
    {

        $results = array();
        $q = $this->db->get_where('bip32_user_keys', array('order_id' => $order_id))->result_array();
        foreach ($q as $res) {
            $results[(strtolower($res['user_role']))] = $res;
        }

        return $results;

    }

    /**
     * Load Order
     *
     * Load an order, specified by it's $id, and the current $progress.
     * Calculates whether it's a buyer or a vendor who is making the
     * request.
     *
     * @param    int $id
     * @param    array $allowed_progress
     * @return    array/FALSE
     */
    public function load_order($id, $allowed_progress = array())
    {

        switch ($this->current_user->user_role) {
            case 'Vendor':
                $this->db->where('vendor_hash', $this->current_user->user_hash);
                break;
            case 'Buyer':
                $this->db->where('buyer_id', $this->current_user->user_id);
                break;
            default:
                return FALSE;
                break;
        }

        $query = $this->db->select("orders.*, ")
            ->where('id', "$id")
            ->where_in('progress', $allowed_progress)
            ->limit(1)
            ->get('orders');

        if ($query->num_rows() > 0) {
            $row = $this->build_array($query->result_array());
            if (in_array($row[0]['progress'], $allowed_progress))
                return $row[0];

        }
        return FALSE;
    }

    /**
     * Vendor Accept Order
     *
     * Pass info generated at either buyer_confirm or vendor_accept page
     * via $info, and then create the order/address details.
     *
     * $info = array('vendor_public_keys' => array,
     *                'order_type' => array,
     *                'order' => array
     *                'initiating_user' => array
     *                'update_fields' => array
     *
     * );
     * @param        array $info
     * @return    string/TRUE
     */
    public function vendor_accept_order($info)
    {
        $this->load->model('bitcoin_model');
        $this->load->model('bip32_model');
        $this->load->model('accounts_model');

        if ($info['initiating_user'] == 'buyer') {
            // Buyer public key is in $info.buyerpubkey array, also ID in update fields.

            $buyer_public_key = $info['buyer_public_key'];
            $this->update_order($info['order']['id'], $info['update_fields']);
            foreach ($info['update_fields'] as $key => $field) {
                $info['order'][$key] = $field;
            }
            $info['update_fields'] = array();
        } else {
            $buyer_public_key = $info['order']['public_keys']['buyer'];
        }

        // Add vendors public key no matter what we're doing!
        $vendor_public_key = $this->bip32_model->add_child_key(array(
            'user_id' => $info['order']['vendor']['id'],
            'user_role' => 'Vendor',
            'order_id' => $info['order']['id'],
            'order_hash' => '',
            'parent_extended_public_key' => $info['vendor_public_key']['parent_extended_public_key'],
            'provider' => $info['vendor_public_key']['provider'],
            'extended_public_key' => $info['vendor_public_key']['extended_public_key'],
            'public_key' => $info['vendor_public_key']['public_key'],
            'key_index' => $info['vendor_public_key']['key_index']
        ));

        // Get vendors public key, stored by that function.
        $admin_public_key = $this->bip32_model->get_next_admin_child();

        if ($admin_public_key == FALSE) {
            return 'An error occured, which prevented your order being created. Please notify an administrator.';
        } else {
            $admin_public_key = $this->bip32_model->add_child_key(array(
                'user_id' => '0',
                'user_role' => 'Admin',
                'order_id' => $info['order']['id'],
                'order_hash' => '',
                'parent_extended_public_key' => $admin_public_key['parent_extended_public_key'],
                'provider' => 'Manual',
                'extended_public_key' => $admin_public_key['extended_public_key'],
                'public_key' => $admin_public_key['public_key'],
                'key_index' => $admin_public_key['key_index']
            ));
            $public_keys = array($buyer_public_key['public_key'], $vendor_public_key['public_key'], $admin_public_key['public_key']);
            $sorted_keys = RawTransaction::sort_multisig_keys($public_keys);
            $multisig_details = RawTransaction::create_multisig('2', $sorted_keys);

            // If no errors, we're good to create the order!
            if ($multisig_details !== FALSE) {
                $this->bitcoin_model->log_key_usage('order', $this->bw_config->bip32_mpk, $admin_public_key['key_index'], $admin_public_key['public_key'], $info['order']['id']);

                $info['update_fields']['vendor_public_key'] = $vendor_public_key['id'];
                $info['update_fields']['admin_public_key'] = $admin_public_key['id'];
                $info['update_fields']['buyer_public_key'] = $buyer_public_key['id'];
                $info['update_fields']['address'] = $multisig_details['address'];
                $info['update_fields']['redeemScript'] = $multisig_details['redeemScript'];
                $info['update_fields']['selected_payment_type_time'] = time();
                $info['update_fields']['progress'] = 2;
                $info['update_fields']['time'] = time();

                if ($info['order_type'] == 'escrow') {
                    $info['update_fields']['vendor_selected_escrow'] = '1';
                    $info['update_fields']['extra_fees'] = ((($info['order']['price'] + $info['order']['shipping_costs']) / 100) * $this->bw_config->escrow_rate);
                } else {
                    $info['update_fields']['vendor_selected_escrow'] = '0';
                    $info['update_fields']['vendor_selected_upfront'] = '1';
                    $info['update_fields']['extra_fees'] = ((($info['order']['price'] + $info['order']['shipping_costs']) / 100) * $this->bw_config->upfront_rate);
                }

                if ($this->update_order($info['order']['id'], $info['update_fields']) == TRUE) {
                    $this->bitcoin_model->add_watch_address($multisig_details['address'], 'order');

                    $subject = 'Confirmed Order #' . $info['order']['id'];
                    $message = "Your order with {$info['order']['vendor']['user_name']} has been confirmed.\n" . (($info['order_type'] == 'escrow') ? "Escrow payment was chosen. Once you pay to the address, the vendor will ship the goods. You can raise a dispute if you have any issues." : "You must make payment up-front to complete this order. Once the full amount is sent to the address, you must sign a transaction paying the vendor.");
                    $this->order_model->send_order_message($info['order']['id'], $info['order']['buyer']['user_name'], $subject, $message);

                    $subject = 'New Order #' . $info['order']['id'];
                    $message = "A new order from {$info['order']['buyer']['user_name']} has been confirmed.\n" . (($info['order_type'] == 'escrow') ? "Escrow was chosen for this order. Once paid, you will be asked to sign the transaction to indicate the goods have been dispatched." : "Up-front payment was chosen for this order based on your settings for one of the items. The buyer will be asked to sign the transaction paying you immediately after payment, which you can sign and broadcast to mark the order as dispatched.");
                    $this->order_model->send_order_message($info['order']['id'], $info['order']['vendor']['user_name'], $subject, $message);

                    $msg = ($info['initiating_user'] == 'buyer')
                        ? 'This order has been automatically accepted, visit the orders page to see the payment address!'
                        : 'You have accepted this order! Visit the orders page to see the bitcoin address!';
                    $this->current_user->set_return_message($msg, 'success');
                    return TRUE;
                } else {
                    return 'There was an error creating your order.';
                }
            } else {
                return 'Unable to create address.';
            }
        }
    }

    /**
     * Create Spend Transaction
     *
     * This function takes a $from_address, an order address, and a $tx_outs array,
     * specifying who the transaction should pay.
     *
     * Returns TRUE if the transaction is successfully created, and previous details
     * removed, or else a string containing an error if it fails.
     *
     * @param string $from_address
     * @param array $tx_outs
     * @param string $script
     * @return bool|string
     */
    public function create_spend_transaction($from_address, array $tx_outs = array(), $script)
    {
        if (count($tx_outs) < 1)
            return 'No outputs specified in transaction.';

        $this->load->model('transaction_cache_model');

        // Add the inputs at the multisig address.
        $payments = $this->transaction_cache_model->payments_to_address($from_address);
        if (count($payments) == 0)
            return 'No spendable outputs found for this address';

        $order_id = $payments[0]['order_id'];

        // Create the transaction inputs
        $tx_ins = array();
        $tx_pkScripts = array();
        foreach ($payments as $pmt) {
            $tx_ins[] = array('txid' => $pmt['tx_id'],
                'vout' => $pmt['vout']);
            $tx_pkScripts[] = array('txid' => $pmt['tx_id'], 'vout' => (int)$pmt['vout'], 'scriptPubKey' => $pmt['pkScript'], 'redeemScript' => $script);
        }

        $json = json_encode($tx_pkScripts);
        $tx_outs = array_map('strval', $tx_outs);

        $raw_transaction = RawTransaction::create($tx_ins, $tx_outs);
        if ($raw_transaction == FALSE) {
            return 'An error occurred creating the transaction!';
        } else {
            // Embed redeem script into all tx's
            $new_tx = RawTransaction::decode($raw_transaction);

            foreach ($new_tx['vin'] as &$input_ref) {
                //$empty_input = $script;
                $input_ref['scriptSig']['hex'] = $script;
            }
            $raw_transaction = RawTransaction::encode($new_tx);
            $decoded_transaction = RawTransaction::decode($raw_transaction);

            if ($this->update_order($order_id, array('unsigned_transaction' => $raw_transaction . " ",
                'json_inputs' => "'$json'",
                'partially_signed_transaction' => '',
                'partially_signed_time' => '',
                'partially_signing_user_id' => ''))
            ) {

                $this->transaction_cache_model->clear_expected_for_address($from_address);
                $this->transaction_cache_model->log_transaction($decoded_transaction['vout'], $from_address, $order_id);

                return TRUE;
            }
            return 'An error occured updating the order!';
        }
    }

    /**
     * Update Order
     *
     * This function is used to change properties of an order, and also
     * to move it in a non-linear fashion through the order process -
     * allows for refunds, requesting early finalization..
     *
     * @param    int $order_id
     * @param    array $update
     * @return    boolean
     */
    public function update_order($order_id, array $update = array())
    {
        if (count($update) == 0)
            return FALSE;

        $this->db->where('id', $order_id);

        return $this->db->update('orders', $update) == TRUE;
    }

    /**
     * Requested Order Type
     *
     * Takes an $order_arr, and determines which order type should be
     * requested: either 'escrow' or 'upfront'
     *
     * @param    array $order_arr
     * @return    string
     */
    public function requested_order_type($order_arr)
    {
        $upfront = FALSE;

        foreach ($order_arr['items'] as $item) {
            $upfront = $upfront || (bool)$item['prefer_upfront'];
        }
        return ($upfront == TRUE) ? 'upfront' : 'escrow';
    }


    /**
     * Update Items
     *
     * Updates the items in $order_it, as specified by $update.
     * If $act == 'update' then we update the order with the new $update['quantity'],
     * otherwise it's creating the item in the order.
     *
     * @param    int $order_id
     * @param    array $update
     * @param    string $act
     * @return    bool
     */
    public function update_items($order_id, $update, $act = 'update')
    {

        $order_info = $this->get($order_id);
        if ($order_info == FALSE)
            return FALSE;

        $found_item = FALSE;
        $item_string = '';
        $place = 0;

        // Process items already on the order.
        foreach ($order_info['items'] as $item) {
            if ($item['hash'] == $update['item_hash']) {
                $found_item = TRUE;
                $quantity = ($act == 'update') ? ($item['quantity'] + $update['quantity']) : ($update['quantity']);
            } else {
                $quantity = $item['quantity'];
            }

            if ($quantity > 0) {
                if ($place++ !== 0) $item_string .= ":";

                $item_string .= $item['hash'] . "-" . $quantity . "-" . $update['fx']."-".$update['price'];
            }
        }
        // If we haven't encountered the item on the list, add it now.
        if ($found_item == FALSE) {
            if ($update['quantity'] > 0)
                $item_string .= ((strlen($item_string)>0)?':':'') . $update['item_hash'] . "-" . $update['quantity'] . "-". $update['fx']."-".$update['price'];
        }

        // Delete order if the item_string is empty.
        if (empty($item_string)) {
            $this->delete($order_id);
            return TRUE;
        }

        $order = array('items' => $item_string,
            'price' => $this->calculate_price($item_string),
            'time' => time());

        $this->db->where('id', $order_id)
            ->where('progress', '0');
        return $this->db->update('orders', $order) == TRUE;

    }

    /**
     * Get
     *
     * Loads an order by it's $order_id. Does not require the user to
     * be a specific role.
     *
     * @param    int $order_id
     * @return    array/FALSE
     */
    public function get($order_id)
    {
        $query = $this->db->where('id', $order_id)
            ->get('orders');

        if ($query->num_rows() > 0) {
            $row = $this->build_array($query->result_array());
            return $row[0];
        }
        return FALSE;
    }

    /**
     * Delete
     *
     * Deletes an order as specified by it's $order_id. Does not
     * require that the user has a specific role.
     * Returns a boolean.
     *
     * @param    int $order_id
     * @return    bool
     */
    public function delete($order_id)
    {
        $this->db->where('id', $order_id);
        return $this->db->delete('orders') == TRUE;
    }

    /**
     * Calculate Price
     *
     * Recalculates the price based on an order's item string.
     *
     * @param    string $item_string
     * @return    int
     */
    public function calculate_price($item_string)
    {
        $array = explode(":", $item_string);
        $price = 0;
        foreach ($array as $item_code) {
            $info = explode("-", $item_code);
            $quantity = $info[1];
            $item_info = $this->items_model->get($info[0]);
            $price += $quantity * $item_info['price_b'];
        }

        return $price;
    }

    /**
     * Set User Public Key
     *
     * This function will set the {$user_type}_public_key for $order_id,
     * to the supplied $public_key.
     *
     * @param    int $order_id
     * @param    string $user_type
     * @param    string $public_key
     * @return    boolean
     */
    public function set_user_public_key($order_id, $user_type, $public_key)
    {
        $user_type = strtolower($user_type);
        if (!in_array($user_type, array('buyer', 'vendor', 'admin')))
            return FALSE;
        $index = $user_type . '_public_key';
        $update = array($index => $public_key);
        return $this->update_order($order_id, $update);
    }

    /**
     * Send Order Message
     *
     * Sends a message to $recipient - the vendors name. The $order_id is
     * specified, as well as the $message and $subject.
     *
     * @param    int $order_id
     * @param    string $recipient
     * @param    string $subject
     * @param    string $message
     * @return    void
     */
    public function send_order_message($order_id, $recipient, $subject, $message)
    {
        $this->load->library('bw_messages');
        $this->load->model('messages_model');
        $this->load->model('accounts_model');

        $admin = $this->accounts_model->get(array('user_name' => 'admin'));
        $details = array('username' => $recipient,
            'subject' => $subject,
            'message' => $message);
        $message = $this->bw_messages->prepare_input(array('from' => $admin['id']), $details);
        $message['order_id'] = $order_id;
        if ($this->messages_model->send($message)) {
            $recipient_ac = $this->accounts_model->get(array('user_name' => $recipient));
            if (strlen($recipient_ac['email_address']) > 0 AND $recipient_ac['email_updates'] == 1) {
                $this->load->library('email');
                $service_name = preg_replace("/^[\w]{2,6}:\/\/([\w\d\.\-]+).*$/", "$1", $this->config->slash_item('base_url'));
                $this->email->from('do-not-reply@' . $service_name, '');
                $this->email->to($recipient_ac['email_address']);
                $this->email->subject('Order Update on ' . $this->bw_config->site_title);
                $this->email->message($this->bw_messages->for_email);
                $this->email->send();
            }
        };

    }

    /**
     * Order Paid Callback
     *
     * Loads orders marked as paid, and generates the transaction
     * for each. This is done at the end of the callback function, since
     * it will have prepared all the information in the payments table.
     *
     */
    public function order_paid_callback()
    {

        $query = $this->db->get('paid_orders_cache');
        if ($query->num_rows() == 0)
            return FALSE;

        $paid = $query->result_array();
        $coin = $this->bw_config->currencies[0];

        $this->load->model('transaction_cache_model');
        $this->load->model('accounts_model');

        foreach ($paid as $record) {
            $order = $this->get($record['order_id']);
            $vendor_address = $order['vendor_payout'];
            $admin_address = BitcoinLib::public_key_to_address($order['public_keys']['admin']['public_key'], $coin['crypto_magic_byte']);

            // Create the transaction outputs
            $tx_outs = array($admin_address => (string)number_format(($order['fees'] + $order['extra_fees'] - 0.0001), 8),
                $vendor_address => (string)number_format(($order['price'] + $order['shipping_costs'] - $order['extra_fees']), 8));

            $create_spend_transaction = $this->create_spend_transaction($order['address'], $tx_outs, $order['redeemScript']);
            if ($create_spend_transaction == TRUE) {
                $next_progress = ($order['vendor_selected_escrow'] == '1') ? '4' : '3';
                $this->progress_order($order['id'], '2', $next_progress, array('paid_time' => time()));
            } else {
                //$this->log_model->
            }
            $this->transaction_cache_model->delete_finalized_record($order['id']);
        }
    }

    /**
     * Progress Order
     *
     * Used to progress an order by $order_id. Can either be a vendor or a buyer.
     * Controls the flow of the order.
     *
     * @param $order_id
     * @param $current_progress
     * @param int $set_progress
     * @param array $changes
     * @return bool
     */
    public function progress_order($order_id, $current_progress, $set_progress = 0, array $changes = array())
    {
        $current_order = $this->get($order_id);

        if ($current_order == FALSE OR (isset($current_order['progress']) AND $current_order['progress'] !== $current_progress))
            return FALSE;

        if ($current_progress == '2' && in_array($set_progress, array('3', '4'))) {
            $update['progress'] = ($set_progress == '3') ? '3' : '4';
        } else if ($current_progress == '3' AND $set_progress == '6') {
            $update['progress'] = '6';
        } else if ($current_progress == '4' AND in_array($set_progress, array('5', '6')) == TRUE) {
            $update['progress'] = ($set_progress == '5') ? '5' : '6';
        } else if ($current_progress == '5' AND in_array($set_progress, array('6', '7')) == TRUE) {
            $update['progress'] = ($set_progress == '6') ? '6' : '7';
        } else {
            $update['progress'] = ($current_progress + 1);
        }
        $update['time'] = time();

        $this->db->where('id', $order_id);
        if ($this->db->update('orders', $update) == TRUE) {
            if ($update['progress'] == '7') {
                $this->increase_users_order_count(array($current_order['buyer']['id'], $current_order['vendor']['id']));
                $this->load->model('review_auth_model');
                $this->review_auth_model->issue_tokens_for_order($order_id);
            }

            $this->update_order($order_id, $changes);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Increase Users Order Count
     *
     * Takes an $order_id, and increases the buyer/vendors order count.
     *
     * @param    int $user_ids
     */
    public function increase_users_order_count($user_ids)
    {
        $this->load->model('users_model');
        foreach ($user_ids as $uid) {
            $this->users_model->increase_order_count($uid);
        }
    }

    /**
     * Order Finalized Callback
     * -
     * This function is called with an array of information when an input
     * in an order has been spent. If this happens, it either corresponds
     * to an escrow or up-front payment going through. We need to check
     * that the spend was expected - matches a hash of the expected
     * outcome for that input we store on transaction creation.
     * It can also happen when the order is disputed - in which case it
     * simply progresses to complete.
     *
     * Updates order information where necessary.
     *
     * @param    array $array
     */
    public function order_finalized_callback($array)
    {

        $this->load->model('disputes_model');
        $this->load->model('bitcoin_model');

        foreach ($array as $record) {
            $order = $this->get_order_by_address($record['address']);

            $complete = false;
            // If progress is 6, then a disputed order is completed.

            if ($order['progress'] == '8') {
                $update = array('progress' => '7',
                    'time' => time(),
                    'refund_completed_time' => time());
                if ($this->update_order($order['id'], $update) == TRUE)
                    $complete = TRUE;

            } elseif ($order['progress'] == '6') {
                $dispute = $this->disputes_model->get_by_order_id($order['id']);

                if ($this->progress_order($order['id'], '6', '7') == TRUE) {
                    $dispute_update = array('posting_user_id' => '',
                        'order_id' => $order['id'],
                        'dispute_id' => $dispute['id'],
                        'message' => 'Dispute closed, payment was broadcast.');
                    $this->disputes_model->post_dispute_update($dispute_update);
                    // Set final response. Prevents further posts in the
                    // dispute. This is the only way an escrow dispute
                    // can be finalized.
                    $this->disputes_model->set_final_response($order['id']);

                    $complete = true;
                }
            } else {
                // Otherwise, progress depending on whether the transaction is escrow, or upfront.
                // Escrow
                if ($order['vendor_selected_upfront'] == '0')
                    if ($this->progress_order($order['id'], '5', '7', array('received_time' => time(), 'time' => time())) == TRUE)
                        $complete = true;

                // Upfront payment. Vendor takes money to confirm dispatch.
                if ($order['vendor_selected_upfront'] == '1') {
                    $update = array('dispatched_time' => time(),
                        'dispatched' => '1',
                        'time' => time());
                    if ($this->progress_order($order['id'], '4', '5', $update) == TRUE)
                        $complete = true;
                }
            }

            // If complete, then record the details.
            if ($complete) {
                $update = array('finalized' => '1',
                    'finalized_time' => time(),
                    'final_transaction_id' => $record['final_id'],
                    'finalized_correctly' => (int)$record['valid']);

                $this->update_order($order['id'], $update);
                $this->bitcoin_model->delete_watch_address($order['address']);
            }
        }
    }

    /**
     * Get Order By Address
     *
     * Loads order details when given a multisig $address. Returns FALSE
     * if no such order exists, otherwise returns the order array.
     *
     * @param    string $address
     * @return    array/FALSE
     */
    public function get_order_by_address($address)
    {
        $query = $this->db->where('address', $address)
            ->get('orders');
        if ($query->num_rows() == 0) {
            return FALSE;
        } else {
            $build = $this->build_array($query->result_array());
            return $build[0];
        }
    }

    /**
     * Buyer Cancel
     *
     * Cancels a buyers order by resetting everything.
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function buyer_cancel($order_id)
    {
        $changes = array('progress' => '0',
            'shipping_costs' => 0.00000000,
            'fees' => 0.00000000,
            'confirmed_time' => '',
            'buyer_public_key' => '');
        $this->db->where('id', $order_id);
        return $this->db->update('orders', $changes) == TRUE;
    }

    /**
     * Vendor Cancel
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function vendor_cancel($order_id)
    {
        $changes = array('progress' => '0',
            'shipping_costs' => 0.00000000,
            'fees' => 0.00000000,
            'extra_fees' => 0.00000000,
            'selected_payment_type_time' => '',
            'buyer_public_key' => '',
            'vendor_public_key' => '',
            'admin_public_key' => '',
            'address' => '',
            'redeemScript' => '',
            'confirmed_time' => '');
        $this->db->where('id', $order_id);
        return $this->db->update('orders', $changes) == TRUE;
    }

    /**
     * Admin Orders By Progress
     *
     * This function is used by autorun jobs. Loads all orders which have
     * progress=$progress, and finalized=$finalized.  Returns a n
     * multidimensional array if any records exist, or FALSE on failure.
     *
     * @param    int $progress
     * @param    int $finalized
     * @return    array/FALSE
     */
    public function admin_orders_by_progress($progress, $finalized)
    {
        $query = $this->db->where('progress', "$progress")
            ->where('finalized', "$finalized")
            ->get('orders');
        return ($query->num_rows() > 0) ? $query->result_array() : FALSE;

    }

    /**
     * Admin Set Progress
     *
     * This function is used by autorun jobs to set the progress of
     * the order $order_id to $progress. Unlike the normal progress_order()
     * function, which requires the current progress and calculates the next
     * progress number accordingly, this function can arbitrarily set
     * an order to any stage in the order process.
     *
     * @param    int $order_id
     * @param    int $progress
     * @return    boolean
     */
    public function admin_set_progress($order_id, $progress)
    {
        $this->db->where('id', "$order_id");
        return $this->db->update('orders', array('progress' => $progress)) == TRUE;
    }

    /**
     * Admin Count Orders
     *
     * @return    int
     */
    public function admin_count_orders()
    {
        return $this->db->select('id')
            ->from('orders')
            ->where('progress >', '0')
            ->count_all_results();
    }

    /**
     * Admin Order Page
     *
     * @param    int $per_page
     * @param    int $start
     * @return    int
     */
    public function admin_order_page($per_page, $start)
    {
        return $this->build_array($this->db->where('progress >', '0')
            ->limit($per_page, $start)
            ->order_by('id', 'desc')
            ->get('orders')
            ->result_array());
    }

    /**
     * Admin Order Details
     *
     * @param    int $order_id
     * @return    false/array
     */
    public function admin_order_details($order_id)
    {
        $query = $this->db->where('progress >', '0')
            ->where('id', "$order_id")
            ->get('orders');

        return ($query->num_rows() == 0)
            ? FALSE
            : $this->build_array($query->result_array());
    }
}

;

/* End Of File: Order_model.php */
/* Location: application/models/Order_model.php */
