<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Order Model
 *
 * This class handles the database queries relating to orders.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Order
 * @author		BitWasp
 * 
 */
class Order_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Items_Model
	 */		
	public function __construct() {
		parent::__construct();
		$this->load->model('items_model');
	}
	
	/**
	 * Add
	 * 
	 * Adds an order to the database. Columns are specified by array keys.
	 * Returns a boolean.
	 * 
	 * @param	array	$order
	 * @return	bool
	 */
	public function add($order) {
		$order['time'] = time();
		$order['created_time'] = time();
		return ($this->db->insert('orders', $order) == TRUE) ? TRUE : FALSE;
	}
	
	/** 
	 * Cancel
	 * 
	 * This function cancels an order by resetting it's progress to 0.
	 * Buyers may cancel an order if FE is requested.
	 * Returns a boolean.
	 * 
	 * @param		int	$order_id
	 * @return		bool
	 */
	public function cancel($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('progress' => '0')) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Order By Progress
	 * 
	 * Load an order for the currently logged in vendor as specified by it's progress.
	 * 
	 * @param	int	$progress
	 * @return	array/FALSE
	 */
	public function order_by_progress($progress) {
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		$this->db->where('progress', $progress);
		$query = $this->db->get('orders');
		if($query->num_rows() > 0) {
			$row = $query->result_array();
			return $this->build_array($row);
		}
		return FALSE;
	}
	
	/**
	 * My Orders
	 * 
	 * Loads the current vendors orders.
	 * Returns an array on success and FALSE on failure.
	 * 
	 * @return	array/FALSE
	 */
	public function my_orders() {
		$this->db->where('vendor_hash', $this->current_user->user_hash);
		$this->db->where('progress >','0');
		$this->db->order_by('progress ASC, time desc');
		$query = $this->db->get('orders');
		if($query->num_rows() > 0) {
			$row = $query->result_array();
			return $this->build_array($row);
		}
		return FALSE;
	}
	
	/**
	 * My Purchases
	 * 
	 * Returns the current buyers purchases on success, and FALSE if there
	 * are none.
	 * 
	 * @return	array/FALSE
	 */
	public function my_purchases() {
		$this->db->where('buyer_id', $this->current_user->user_id)
				 ->order_by('progress asc, time desc');
		$query = $this->db->get('orders');
		return ($query->num_rows() > 0) ? $this->build_array($query->result_array()) : FALSE;
		
	}
	
	/**
	 * Load
	 * 
	 * Buyer can load an order about them, as specified by $vendor_hash,
	 * and can optionally set a progress $progress.
	 * 
	 * @param	string	$vendor_hash
	 * @param	int	progress
	 * @return	array/FALSE
	 */
	public function load($vendor_hash, $progress = NULL) {
		$this->db->where('vendor_hash', $vendor_hash);
		$this->db->where('buyer_id', $this->current_user->user_id);
		if($progress == NULL) {
			$this->db->where('progress !=', '3');
		} else {
			$this->db->where('progress', $progress);
		}
		$query = $this->db->get('orders');
		$result = $this->build_array($query->result_array());
		return $result[0];
	}

	/**
	 * Load Order
	 * 
	 * Load an order, specified by it's $id, and the current $progress.
	 * Calculates whether it's a buyer or a vendor who is making the
	 * request.
	 * 
	 * @param	int	$id
	 * @param	array	$allowed_progress
	 * @return	array/FALSE
	 */
	public function load_order($id, $allowed_progress = array()) {
		switch($this->current_user->user_role) {
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
		$this->db->where('id', "$id");
		$query = $this->db->get('orders');
		if($query->num_rows() > 0) {
			$result = $query->result_array();$i = 1;
			foreach($result as $res) {
				if($this->general->matches_any($res['progress'], $allowed_progress) == TRUE) {
					$row = $this->build_array($query->result_array());
					return $row[0];
				}
			}
		}
		return FALSE;
	}

	/**
	 * Get
	 * 
	 * Loads an order by it's $order_id. Does not require the user to
	 * be a specific role.
	 * 
	 * @param	int	$order_id
	 * @return	array/FALSE
	 */
	public function get($order_id) {
		$this->db->where('id', $order_id);
		$query =$this->db->get('orders');
		if($query->num_rows() > 0) {
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
	 * @param	int	$order_id
	 * @return	bool
	 */
	public function delete($order_id) {
		$this->db->where('id', $order_id);
		return  ($this->db->delete('orders') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Update Items
	 * 
	 * Updates the items in $order_it, as specified by $update.
	 * If $act == 'update' then we update the order with the new $update['quantity'],
	 * otherwise it's creating the item in the order.
	 * 
	 * @param	int	$order_id
	 * @param	array	$update
	 * @param	string	$act
	 * @return	bool
	 */
	public function update_items($order_id, $update, $act = 'update') {
		
		$order_info = $this->get($order_id);
		if($order_info == FALSE)
			return FALSE;
			
		$found_item = FALSE;	
		$item_string = '';
		$place = 0;
		
		// Process items already on the order.
		foreach($order_info['items'] as $item) {
			if($item['hash'] == $update['item_hash']) {
				$found_item = TRUE;
				$quantity = ($act == 'update') ? ($item['quantity']+$update['quantity']) : ($update['quantity']);
			} else {
				$quantity = $item['quantity'];
			}
			
			if($quantity > 0) {
				if($place++ !== 0)		$item_string .= ":";
					
				$item_string .= $item['hash']."-".$quantity;
			}
		}
		// If we haven't encountered the item on the list, add it now.
		if($found_item == FALSE) {
			if($update['quantity'] > 0)
				$item_string .= ":".$update['item_hash']."-".$update['quantity'];
		}
		
		if(empty($item_string)) {
			$this->delete($order_id);
			return TRUE;
		}
			
		$order = array(	'items' => $item_string,
						'price' => $this->calculate_price($item_string),
						'time' => time());
						
		$this->db->where('id', $order_id)
				 ->where('progress', '0');
		return ($this->db->update('orders', $order) == TRUE)  ? TRUE : FALSE;
		
	}
	
	/**
	 * Calculate Price
	 * 
	 * Recalculates the price based on an order's item string.
	 * 
	 * @param	string	$item_string
	 * @return	int	
	 */
	public function calculate_price($item_string) {
		$array = explode(":", $item_string);
		$price = 0;
		foreach($array as $item_code) {
			$info = explode("-", $item_code);
			$quantity = $info[1];
			$item_info = $this->items_model->get($info[0]);
			$price +=  $quantity*$item_info['price_b'];
		}
		
		return $price;
	}
	
	/**
	 * Fix Price
	 * 
	 * This function is used to fix the order's price at a set value. 
	 * It is used once the user places the order, to update the price
	 * to contain the order_price plus the additional fee's. This will
	 * then remain unchanged, on record as the price. 
	 * 
	 * @param	int	$order_id
	 * @param	float $order_price
	 * @return	boolean
	 */
	public function set_price($order_id, $order_price) {
		$this->db->where('id', "$order_id");
		return ($this->db->update('orders', array('price' => $order_price)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Fees
	 * 
	 * Record the fees for a particular order.
	 * 
	 * @param	int	$order_id
	 * @param	int $fee_price
	 * @return	boolean
	 */
	public function set_fees($order_id, $fee_price) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('fees' => $fee_price)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Confirmed Time
	 * 
	 * Sets the order (specified by $order_id) confirmed_time to the
	 * current timestamp, when a buyer confirms their order.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_confirmed_time($order_id){
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('confirmed_time' => time())) == TRUE)  ? TRUE : FALSE;
	}
	/**
	 * Set Finalized
	 * 
	 * Mark an order as finalized, when it comes to receiving item or finalizing (escrow)
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_finalized($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('finalized' => '1')) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Selected Escrow
	 * 
	 * Mark that a vendor chose to do an escrow transaction instead of a 
	 * finalize early transaction
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_selected_escrow($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('selected_escrow' => '1')) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Selected Payment Type Time
	 * 
	 * Record the timestamp for when a vendor chose to do an escrow transaction 
	 * or a finalize early transaction, by specifying $order_id.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_selected_payment_type_time($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('selected_payment_type_time' => time())) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Finalized Time
	 * 
	 * Record the timestamp for when the order was paid, by specifying
	 * $order_id.
	 * 
	 * @param	$order_id
	 * @return	boolean
	 */
	public function set_finalized_time($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('finalized_time' => time())) == TRUE) ? TRUE : FALSE;
	}
	/**
	 * Set Dispatched Time
	 * 
	 * Set the time an order was dispatched. Supply the order_id, and the
	 * timestamp will be recorded.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_dispatched_time($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('dispatched_time' => time())) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Set Disputed Time
	 * 
	 * Set the timestamp for when an order was disputed, specified by the
	 * $order_id
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_disputed_time($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('disputed_time' => time())) == TRUE) ? TRUE : FALSE;
	} 
	
	/**
	 * Set Disputed Order
	 * 
	 * Record that the order has been disputed, specified by $order_id.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_disputed_order($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('disputed' => '1')) == TRUE) ? TRUE : FALSE;
	}
	
	public function send_order_message($order_id, $recipient, $subject, $message){
		$this->load->library('bw_messages');
		$this->load->model('messages_model');
		$this->load->model('accounts_model');
		
		$admin = $this->accounts_model->get(array('user_name' => 'admin'));
		$details = array(	'username' => $recipient,
							'subject' => $subject,
							'message' => $message);
		$message = $this->bw_messages->prepare_input($admin['id'], $details);
		$message['order_id'] = $order_id;
		$this->messages_model->send($message);
					
	}
	
	/**
	 * Set Received Time
	 * 
	 * Set the time an order was received. Supply the order_id, and the
	 * timestamp will be recorded.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function set_received_time($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('received_time' => time())) == TRUE) ? TRUE : FALSE;
	}
	// 0: Order unconfirmed, and editable. 
	//		Buyer: Confirm (put funds into escrow) (set=1)
	// 1: Order is confirmed, and awaiting vendor response.
	//		Vendor: Finalize Early, (set=2)
	// 		Vendor: Confirm Dispatch, proceed to escrow. (set=4)
	// 2: Require early finalization
	//		Buyer: Finalizes, (set=3) (credit to vendor account)
	// 		Buyer: Cancel (set=0)
	// 3: User has finalized. Vendor must send product.
	//		Vendor: Confirm Dispatch. (set=4)
	//		Buyer: Dispute				*************
	// 4: Item is dispatched.
	//		Buyer: May Dispute because product hasnt arrived. (set=5) **********
	//		Buyer: Received. (set=6)	
	// 5: Dispute Transaction.
	// 		??? : Resolved (set=7)
	// 6: Buyer received item (if no finalize early, auto pay vendor)
	// 		auto pay vendor and set to 7.
	// 7: Transaction Complete
	// 		Offer both parties a chance to review the other.

	/**
	 * Progress Order
	 * 
	 * Used to progress an order by $order_id. Can either be a vendor or a buyer.
	 * Controls the flow of the order.
	 * 
	 * @param	int	$order_id
	 * @param	int	$current_progress
	 * @param	int	$set_progress
	 * @return	bool
	 */
	public function progress_order($order_id, $current_progress, $set_progress = 0) {
		$current_order = $this->get($order_id);
		
		if($current_order == FALSE || (isset($current_order['progress']) && $current_order['progress'] !== $current_progress))
			return FALSE;
			
		$update['time'] = time();
		if($current_progress == '1' && $this->general->matches_any($set_progress, array('2','4')) == TRUE) {
			$update['progress'] = ($set_progress == '2') ? '2' : '4';
			$this->set_selected_payment_type_time($order_id);
			
		} else if($current_progress == '3' && $this->general->matches_any($set_progress, array('4','5'))== TRUE) {
			$update['progress'] = ($set_progress == '5') ? '5' : '4';
			
		} else if($current_progress == '4' && $this->general->matches_any($set_progress, array('5','6')) == TRUE) {
			$update['progress'] = ($set_progress == '5') ? '5' : '7';
			
		} else {
			$update['progress'] = ($current_progress+1);
		}
		
		if($update['progress'] == '1'){
			$this->set_confirmed_time($order_id);
		}
		
		// Vendor chose escrow, record this & timestamp.
		if($current_progress == '1' && $update['progress'] == '4') {
			$this->set_selected_escrow($order_id);
			$this->set_dispatched_time($order_id);
		}
		// Buyer finalized early. Record timestamp.
		if($update['progress'] == '3' || $current_progress == '4' && $update['progress'] == '7') { 
			$this->set_finalized_time($order_id);
		}
		// Vendor setting item is dispatched. Record timestamp.
		if($current_progress == '3' && $update['progress'] == '4') {
			$this->set_dispatched_time($order_id);
		}
		// Buyer stating they received their item. Record timestamp.
		if($current_progress == '4' && $update['progress'] == '7') {
			$this->set_received_time($order_id);
		}
		// Dispute being created. Record timestamp.
		if($update['progress'] == '5'){
			$this->set_disputed_order($order_id);
			$this->set_disputed_time($order_id);
		}
		// If progress == 7, allow users to review eachother.
		if($update['progress'] == '7') {
			$this->load->model('review_auth_model');
			$this->review_auth_model->issue_tokens_for_order($order_id);
		}
		
		$this->db->where('id', $current_order['id']);
		return ($this->db->update('orders', $update) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Build Array
	 * 
	 * Used to build an array of orders into a more readable array.
	 * Contains information about the vendor, the items (removes vendor
	 * entry from each item).
	 * 
	 * @param	array $orders
	 * return 	array/FALSE
	 */
	public function build_array($orders ) {
		$this->load->model('currencies_model');
		if(count($orders) > 0) {
			$i = 0;
			$item_array = array();
			
			// Loop through each order.
			foreach($orders as $order) {
				// Extract product hash/quantities.
				$items = $order['items'];
				$items = explode(":", $items);
				$j = 0;
				
				$price_b = 0.00000000;
				$price_l = 0.00000000;
				foreach($items as $item) {
					// Load each item & quantity.
					$array = explode("-", $item);
					$item_info = $this->items_model->get($array[0]);
					$quantity = $array[1];
					
					// If the item no longer exists, display a message.
					if($item_info == FALSE) {
						$message = "Item ";
						$message .= (strtolower($this->current_user->user_role) == 'vendor') ? 'has been removed' : 'was removed, contact your vendor' ;
						$item_array[$j] = array('hash' => 'removed',
												'name' => $message);
					} else {
						// Remove the vendor array, reduces the size of responses.
						unset($item_info['vendor']);
						$item_array[$j] = $item_info;

						// Convert from whatever currency the item's price is in
						// to bitcoin, and add this up. Convert to local currency later.
						$price_b_tmp = $item_info['price']/$item_info['currency']['rate'];
						$price_b += $price_b_tmp*$quantity;
					}			
					$item_array[$j++]['quantity'] = $quantity;					
				}
				
				// Determine the progress message. Contains a status update
				// for the order, and lets the user progress to the next step.
				switch($order['progress']) {
					case '0':	
						$buyer_progress_message = '<input type="submit" class="btn btn-mini" name="recount['.$order['id'].']" value="Update" /> <input type="submit" class="btn btn-mini" name="place_order['.$order['id'].']" value="Proceed with Order" />';
						$vendor_progress_message = '';
						// no vendor progress message
						break;
					case '1':
						$buyer_progress_message = 'Awaiting vendor response.'; 
						$vendor_progress_message = "<input type='submit' name='dispatch[{$order['id']}]' value='Dispatch' class='btn btn-mini' /> <input type='submit' name='finalize_early[{$order['id']}]' value='Finalize Early' class='btn btn-mini' />";
						break;
					case '2':
						$buyer_progress_message = 'Must finalize early.<br /><input type="submit" class="btn btn-mini" name="cancel['.$order['id'].']" value="Cancel" /> <input type="submit" class="btn btn-mini" name="finalize['.$order['id'].']" value="Finalize Early" /> ';
						$vendor_progress_message = "Awaiting early finalization. <input type='submit' name='cancel[{$order['id']}]' value='Cancel' class='btn btn-mini'/>";
						break;
					case '3':
						$buyer_progress_message = "Awaiting dispatch.<br />".anchor('order/dispute/'.$order['id'], 'Dispute', 'class="btn btn-mini"');	
						$vendor_progress_message = "<input type='submit' name='dispatch[{$order['id']}]' value='Confirm Dispatch' class='btn btn-mini' />";
						break;
					case '4':
						$buyer_progress_message = "Item has been dispatched.<br /><input type=\"submit\" class=\"btn btn-mini\" name=\"finalize[{$order['id']}]\" value='".(($order['finalized'] == '0') ? 'Finalize' : 'Received')."' /> ".anchor('order/dispute/'.$order['id'], 'Dispute', 'class="btn btn-mini"');	
						$vendor_progress_message= "Awaiting ".(($order['finalized'] == '0') ? 'finalization' : 'delivery')." ".anchor('orders/dispute/'.$order['id'], 'Dispute', 'class="btn btn-mini"'); 
						break;
					case '5':
						$buyer_progress_message = "Disputed transaction.<br />".anchor('order/dispute/'.$order['id'], 'View', 'class="btn btn-mini"');
						$vendor_progress_message = "Disputed transaction.<br />".anchor('orders/dispute/'.$order['id'], 'View', 'class="btn btn-mini"');
						break;
					case '6':
						$buyer_progress_message = "Item received. Pending confirmation.";
						$vendor_progress_message = "Item received. Pending confirmation.";
						break;
					case '7':
						$buyer_progress_message = "Purchase complete.";
						$vendor_progress_message = "Order complete.";
						break;
				}
				$currency = $this->currencies_model->get($order['currency']);
				
				$price = ($currency !== '0') ? $order['price']/$currency['rate'] : $order['price'];
				
				// Load the users local currency.
				$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
				// Convert the order's price into the users own currency.
				$price_l = ($order['price']*$local_currency['rate']);
				$price_l = ($this->current_user->currency['id'] !== '0') ? round($price_l, '2', PHP_ROUND_HALF_UP) : round($price_l, '8', PHP_ROUND_HALF_UP);

				$tmp = array('id' => $order['id'],
									'vendor' => $this->accounts_model->get(array('user_hash' => $order['vendor_hash'])),
									'buyer' => $this->accounts_model->get(array('id' => $order['buyer_id'])),
									'price' => $price,
									'price_b' => (float)round($price_b, 8, PHP_ROUND_HALF_UP),
									'price_l' => $price_l,
									'fees' => $order['fees'],
									'currency' => $currency,
									'time' => $order['time'],
									'time_f' => $this->general->format_time($order['time']),
									'created_time_f' => $this->general->format_time($order['created_time']),
									'items' => $item_array,
									'finalized' => $order['finalized'],
									'disputed' => $order['disputed'],
									'vendor_selected_escrow' => $order['selected_escrow'],
									'progress' => $order['progress'],
									'progress_message' => ($this->current_user->user_role == 'Vendor') ? $vendor_progress_message : $buyer_progress_message);
				
				if($order['dispatched_time'] !== '') {
					$tmp['dispatched_time'] = $order['dispatched_time'];
				}
				if($order['disputed_time'] !== '') {
					$tmp['disputed_time'] = $order['disputed_time'];
				}
				if($order['selected_payment_type_time'] !== '') {
					$tmp['selected_payment_type_time'] = $order['selected_payment_type_time'];
				}
				if($order['finalized_time'] !== '') {
					$tmp['finalized_time'] = $order['finalized_time'];
				}
				if($order['received_time'] !== '') {
					$tmp['received_time'] = $order['received_time'];
				}
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
	 * Admin Orders By Progress
	 * 
	 * This function is used by autorun jobs. Loads all orders which have 
	 * progress=$progress, and finalized=$finalized.  Returns a n
	 * multidimensional array if any records exist, or FALSE on failure.
	 * 
	 * @param	int	$progress
	 * @param	int	$finalized
	 * @return	array/FALSE
	 */
	public function admin_orders_by_progress($progress, $finalized) {
		$this->db->where('progress', "$progress");
		$this->db->where('finalized', "$finalized");
		$query = $this->db->get('orders');
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
	 * @param	int	$order_id
	 * @param	int	$progress
	 * @return	boolean
	 */
	public function admin_set_progress($order_id, $progress) {
		$this->db->where('id', "$order_id");
		return ($this->db->update('orders', array('progress' => $progress)) == TRUE) ? TRUE : FALSE;
	}

};

/* End Of File: order_model.php */
