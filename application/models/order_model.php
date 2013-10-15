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
	public function cancel($order_id){
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
		if($query->num_rows() > 0){
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
	public function load_order($id, $allowed_progress = array()){
		switch(strtolower($this->current_user->user_role)){
			case 'vendor':
				$this->db->where('vendor_hash', $this->current_user->user_hash);
				break;
			case 'buyer':
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
				if($this->general->matches_any($res['progress'], $allowed_progress) == TRUE){
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
/*	public function get($order_id) {
		$this->db->where('id', $order_id);
		$query =$this->db->get('orders');
		if($query->num_rows() > 0) {
			$row = $this->build_array($query->result_array());
			return $row[0];
		}
		return FALSE;
	}*/

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
		foreach($order_info['items'] as $item){
			if($item['hash'] == $update['item_hash']){
				$found_item = TRUE;
				$quantity = ($act == 'update') ? ($item['quantity']+$update['quantity']) : ($update['quantity']);
			} else {
				$quantity = $item['quantity'];
			}
			
			if($quantity > 0){
				if($place++ !== 0)		$item_string .= ":";
					
				$item_string .= $item['hash']."-".$quantity;
			}
		}
		// If we haven't encountered the item on the list, add it now.
		if($found_item == FALSE) {
			if($update['quantity'] > 0)
				$item_string .= ":".$update['item_hash']."-".$update['quantity'];
		}
		
		if(empty($item_string)){
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
	 * Set Finalized
	 * 
	 * Mark an order as finalized, when it comes to receiving item or finalizing (escrow)
	 * 
	 * @param	int	$order_id
	 * @return	bool
	 */
	public function set_finalized($order_id) {
		$this->db->where('id', $order_id);
		return ($this->db->update('orders', array('finalized' => '1')) == TRUE) ? TRUE : FALSE;
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
	// 4: Item is dispatched.
	//		Buyer: May Dispute because product hasnt arrived. (set=5)
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
		if($current_progress == '1' && $this->general->matches_any($set_progress, array('2','4')) == TRUE){
			$update['progress'] = ($set_progress == '2') ? '2' : '4';
		} else if($current_progress == '4' && $this->general->matches_any($set_progress, array('5','6')) == TRUE){
			$update['progress'] = ($set_progress == '5') ? '5' : '7';
		} else {
			$update['progress'] = ($current_progress+1);
		}
		
		$this->db->where('id', $current_order['id']);
		if($this->db->update('orders', $update) == TRUE)
			return TRUE;
		
		return FALSE;
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
						$progress_message = '<input type="submit" class="btn btn-mini" name="recount['.$order['id'].']" value="Update" /> ';
						$progress_message.= '<input type="submit" class="btn btn-mini" name="place_order['.$order['id'].']" value="Proceed with Order" />';
						break;
					case '1':
						$progress_message = 'Awaiting vendor response.'; 
						break;
					case '2':
						$progress_message = 'Must finalize early.<br />'; 
						$progress_message.= '<input type="submit" class="btn btn-mini" name="cancel['.$order['id'].']" value="Cancel" /> ';
						$progress_message.= '<input type="submit" class="btn btn-mini" name="finalize['.$order['id'].']" value="Finalize Early" /> ';
						break;
					case '3':
						$progress_message = "Awaiting dispatch.<br />";
						$progress_message.= '<a class="btn btn-mini" href="'.site_url().'order/dispute/'.$order['id'].'">Dispute</a>';	
						break;
					case '4':
						$progress_message = "Item has been dispatched.<br />";
						$progress_message.= '<input type="submit" class="btn btn-mini" name="finalize['.$order['id'].']" value="';
						$progress_message.= ($order['finalized'] == '0') ? 'Finalize' : 'Received'; $progress_message.='" /> ';
						$progress_message.= '<a class="btn btn-mini" href="'.site_url().'order/dispute/'.$order['id'].'">Dispute</a>';	
						break;
					case '5':
						$progress_message = "Disputed transaction. Awaiting outcome.";
						break;
					case '6':
						$progress_message = "Item received. Pending confirmation.";
						break;
					case '7':
						$progress_message = "Purchase completed. Please review.";
						break;
				}
				
				
				// Load the users local currency.
				$local_currency = $this->currencies_model->get($this->current_user->currency['id']);
				// Convert the order's price into the users own currency.
				$price_l = ($order['price']*$local_currency['rate']);
				$price_l = ($this->current_user->currency['id'] !== '0') ? round($price_l, '2', PHP_ROUND_HALF_UP) : round($price_l, '8', PHP_ROUND_HALF_UP);

				$currency = $this->currencies_model->get($order['currency']);
				$orders[$i++] = array('id' => $order['id'],
									'vendor' => $this->accounts_model->get(array('user_hash' => $order['vendor_hash'])),
									'buyer' => $this->accounts_model->get(array('id' => $order['buyer_id'])),
									'price' => (float)$order['price'],
									'price_b' => (float)round($price_b, 8, PHP_ROUND_HALF_UP),
									'price_l' => $price_l,
									'currency' => $currency,
									'time' => $order['time'],
									'time_f' => $this->general->format_time($order['time']),
									'items' => $item_array,
									'finalized' => $order['finalized'],
									'progress' => $order['progress'],
									'progress_message' => $progress_message);
				
				unset($item_array);
			}
			return $orders;
			
		} else {
			return FALSE;
		}
	}

};

/* End Of File: order_model.php */
