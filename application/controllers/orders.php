<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Orders extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		$this->load->model('order_model');
		$this->load->model('items_model');
		$this->load->model('accounts_model');
		$this->load->model('bitcoin_model');
		$this->load->model('escrow_model');	
		$this->load->model('messages_model');
	}
	
	// Buyer, view their orders
	public function list_purchases($status = NULL) {
		$this->load->library('form_validation');		
	
		$place_order = $this->input->post('place_order');
		$recount = $this->input->post('recount');
		if(is_array($place_order) || is_array($recount)) {
			$id = (is_array($place_order)) ? array_keys($place_order) : array_keys($recount); 
			$id = $id[0];
			$current_order = $this->order_model->load_order($id, array('0'));
			if($current_order == FALSE)
				redirect('order/list');

			$list = $this->input->post('quantity');
			foreach($list as $hash => $quantity) {
				$item_info = $this->items_model->get($hash);
				if($item_info !== FALSE) {
					$update = array('item_hash' => $hash,
									'quantity' => $quantity);
					$this->order_model->update_items($current_order['id'], $update, 'force');
				}
			}
			if(is_array($place_order))
				redirect('order/place/'.$current_order['id']);
		}
		
		$cancel_order = $this->input->post('cancel');
		if(is_array($cancel_order)) {
			$id = array_keys($cancel_order); $id = $id[0];
			$current_order = $this->order_model->load_order($id, array('2'));
			
			if($current_order == FALSE) 	redirect('order/list');
			
			if(	$this->escrow_model->pay($current_order['id'], 'buyer') == TRUE &&
				$this->order_model->cancel($current_order['id']) == TRUE ){
				// Send message to vendor
				$data['from'] = $this->current_user->user_id;
				$details = array('username' => $get['vendor']['user_name'],
								 'subject' => "Order #{$get['id']} has been cancelled.");
				$details['message'] = "{$this->current_user->user_name} has cancelled their order with you for the following items:<br /><br />\n";
				for($i = 0; $i < count($get['items']); $i++){
					$details['message'] .= "{$get['items'][$i]['quantity']} x {$get['items'][$i]['name']}<br />\n";
				}
				$details['message'] .= "<br />Total price: {$get['currency']['symbol']}{$get['price']}";
			 
				$message = $this->bw_messages->prepare_input($data, $details);
				$message['order_id'] = $get['id'];
				$this->messages_model->send($message);
				
				redirect('order/list');
			}
		}

		$finalize_order = $this->input->post('finalize');
		if(is_array($finalize_order)) {
			$id = array_keys($finalize_order); $id = $id[0];
			$current_order = $this->order_model->load_order($id, array('2'));
			$success = FALSE;
			// Forcing finalize early.
			if($current_order !== FALSE && $current_order['finalized'] == '0') {
				if($this->escrow_model->pay($current_order['id'], 'vendor') == TRUE) {
					if($this->order_model->progress_order($current_order['id'], '2') == TRUE)
						$success = TRUE;
				} 
			} 

			if($success == FALSE){
				$current_order = $this->order_model->load_order($id, array('4'));
				// Item has been dispatched - either finalized already, or is in escrow and must be paid.
				if(	($current_order['finalized'] == '0' && $this->escrow_model->pay($current_order['id'], 'vendor') == TRUE) ||
					$current_order['finalized'] == '1'){
					if($this->order_model->progress_order($current_order['id'],'4', '6') == TRUE) 
						$success = TRUE;
				}
			}
			
			if($success == TRUE) {
				$data['from'] = $this->current_user->user_id;
				$details = array('username' => $current_order['vendor']['user_name'],
								 'subject' => "Order #{$current_order['id']} has been finalized");
				$details['message'] = "{$this->current_user->user_name} has issued payment for Order #{$current_order['id']}. BTC {$current_order['price']} has been credited to your account.<br />\n";
				$details['message'] = ($current_order['progress'] == '2') ? 'You may now dispatch the order<br />\n' : 'Please review this order now.<br />\n';
				$message = $this->bw_messages->prepare_input($data, $details);
				$message['order_id'] = $current_order['id'];
				$this->messages_model->send($message);
			}
			redirect('order/list');
		}
		
		$data['orders'] = $this->order_model->my_purchases(); 
		$data['balance'] = $this->bitcoin_model->current_balance();
		$data['escrow_balance'] = $this->escrow_model->balance();		
		$data['page'] = 'orders/purchases';
		$data['title'] = 'My Purchases';
		$data['local_currency'] = $this->current_user->currency;		
		$this->load->library('Layout', $data);
	}

	// Vendor, orders.
	public function list_orders($status = NULL) {
		if($status == 'update')
			$data['returnMessage'] = "Order has been updated.";
			
		$this->load->library('form_validation');
		
		$dispatch = $this->input->post('dispatch');
		if(is_array($dispatch)) {
			foreach($dispatch as $id => $order) {
				if(!is_numeric($id)) break;
				
				$successful = FALSE;
				$get = $this->order_model->load_order($id, array('1'));
				
				if($get !== FALSE && $get['progress'] == '1') {					// Confirm an item is dispatched.
					if($this->order_model->progress_order($id, '1', '4') == TRUE) {
						$successful = TRUE;
						$buyer = $get['buyer']['user_name'];
					}
				}
				
				if(!isset($buyer)){
					$get = $this->order_model->load_order($id, array('3'));					
					if($get !== FALSE && $get['progress'] == '3') {
						if($this->order_model->progress_order($id, '3') == TRUE) {
							$successful = TRUE;
							$buyer = $get['buyer']['user_name'];
						}
					}
				}
				
				if($successful == TRUE) {
					// Send message to the buyer
					$data['from'] = $this->current_user->user_id;
					$details = array('username' => $buyer,
									 'subject' => "Order #{$get['id']} has been dispatched");
					$details['message'] = "{$this->current_user->user_name} has dispatched your order. Please confirm when you receive the item. If the item does not arrive, please raise a dispute with an administrator to discuss resolving the matter.<br /><br />\n";
				 
					$message = $this->bw_messages->prepare_input($data, $details);
					$message['order_id'] = $get['id'];
					$this->messages_model->send($message);
				}
			}
			redirect('orders');
		}
		
		$finalize_early = $this->input->post('finalize_early');
		if(is_array($finalize_early)) {
			foreach($finalize_early as $id => $order){
				$get = $this->order_model->load_order($id, array('1'));
				if($get !== FALSE){
					if($this->order_model->progress_order($id, '1', '2') == TRUE) {
							
						// Send message to vendor
						$data['from'] = $this->current_user->user_id;
						$details = array('username' => $get['buyer']['user_name'],
										 'subject' => "Must finalize early for Order #{$get['id']}");
						$details['message'] = "{$this->current_user->user_name} has requested that you finalize this transaction early before they dispatch the item. Please authorize payment to the vendor to continue with this purchase. You may cancel the transaction at this point to receive a refund.<br /><br />\n";
						for($i = 0; $i < count($get['items']); $i++){
							$details['message'] .= "{$get['items'][$i]['quantity']} x {$get['items'][$i]['name']}<br />\n";
						}
						$details['message'] .= "<br />Total price: {$get['currency']['symbol']}{$get['price']}";
					 
						$message = $this->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $get['id'];
						$this->messages_model->send($message);
										
					}
				}
			}
			redirect('orders');
		}

		// Load orders..
		$data['new_orders'] = $this->order_model->order_by_progress('1');
		$data['await_finalize_early'] = $this->order_model->order_by_progress('2');
		$data['await_dispatch'] = $this->order_model->order_by_progress('3');
		$data['await_finalization'] = $this->order_model->order_by_progress('4');
		$data['in_dispute'] = $this->order_model->order_by_progress('5');
		
		// Load info for display..
		$data['local_currency'] = $this->current_user->currency;	
		$data['balance'] = $this->bitcoin_model->current_balance();
		$data['escrow_balance'] = $this->escrow_model->balance();		

		$data['page'] = 'orders/list_orders';
		$data['title'] = 'My Orders';
		$this->load->library('Layout', $data);
	}

	// Buyer, add an item to the order
	public function purchase_item($item_hash) {	
		$this->load->library('form_validation');
		$item_info = $this->items_model->get($item_hash);
		if($item_info == FALSE) 
			redirect('items');

		$order = $this->order_model->load($item_info['vendor_hash'],'0');
		if($order == FALSE) {
			// New order; Need to create
			$new_order = array(	'buyer_id' => $this->current_user->user_id,
								'vendor_hash' => $item_info['vendor_hash'],
								'items' => $item_info['hash']."-1",
								'price' => $item_info['price_b'],
								'currency' => '0',
								'time' => time() );
			if($this->order_model->add($new_order) == TRUE) {
				$data['returnMessage'] = 'Your order has been created.';
				$data['success'] = true;
			} else {
				$data['returnMessage'] = 'Unable to add your order at this time, please try again later.';
			}
		} else {
			// Already have order, update it
			if($order['progress'] == '0') {
				$update = array('item_hash' => $item_hash,
								'quantity' => '1');
				$data['returnMessage'] = ($this->order_model->update_items($order['id'], $update) == TRUE) ? 'Your order has been updated.' : 'Unable to update your order at this time.';
			} else {
				$data['returnMessage'] = 'Your order has already been created, please contact your vendor to discuss any further changes';
			}
		}
		
		$data['title'] = 'My Purchases';
		$data['page'] = 'orders/purchases';
		$data['orders'] = $this->order_model->my_purchases(); 
		$data['escrow_balance'] = $this->escrow_model->balance();		
		$data['balance'] = $this->bitcoin_model->current_balance();		
		$data['local_currency'] = $this->current_user->currency;		
		$this->load->library('Layout', $data);
		
	}

	// Buyer, submit order.
	public function place($id) {
		$this->load->library('form_validation');
		$this->load->model('bitcoin_model');
	
		$data['order'] = $this->order_model->load_order($id, array('0'));
		if($data['order'] == FALSE)
			redirect('order/list');
		
		$balance = $this->bitcoin_model->current_balance();
		
		$data['title'] = 'Place Order #'.$data['order']['id'];
		$data['page'] = 'orders/place';
		$data['header_meta'] = $this->load->view('orders/encryption_header', NULL, true);
		
		if($this->form_validation->run('order_place') == TRUE) {

			if($balance < $data['order']['price']) {
				$data['returnMessage'] = 'You have insufficient funds to place this order. Please top up and try again';
			} else {
				$escrow = array('order_id' => $data['order']['id'],
								'buyer_id' => $this->current_user->user_id,
								'vendor_id' => $data['order']['vendor']['id'],
								'buyer_hash' => $this->current_user->user_hash,
								'amount' => $data['order']['price']);			
				
				if($this->escrow_model->add($escrow) == FALSE) {
					$data['returnMessage'] = 'Unable to place your order at this time, please try again later.';
				} else {
					if($this->order_model->progress_order($data['order']['id'], '0') == FALSE){
						$data['returnMessage'] = 'Unable to place your order at this time, please try again later.';
					} else {
					
						// Send message to vendor
						$info['from'] = $this->current_user->user_id;
						$details = array('username' => $data['order']['vendor']['user_name'],
										 'subject' => "New Order #{$data['order']['id']} from ".$this->current_user->user_name);
						$details['message'] = "You have received a new order from {$this->current_user->user_name}.<br />\nOrder ID: #{$data['order']['id']}<br />\n";
						for($i = 0; $i < count($data['order']['items']); $i++){
							$details['message'] .= "{$data['order']['items'][$i]['quantity']} x {$data['order']['items'][$i]['name']}<br />\n";
						}
						$details['message'] .= "<br />Total price: {$data['order']['currency']['symbol']}{$data['order']['price']}<br /><br />\n";
						$details['message'] .= "Buyer Address: <br />\n".$this->input->post('buyer_address');
					 
						$message = $this->bw_messages->prepare_input($info, $details);
						$message['order_id'] = $data['order']['id'];
						$this->messages_model->send($message);
					
						$data['success'] = TRUE;
						$data['returnMessage'] = 'Your order has been placed. Funds have been added to escrow pending a response from your vendor.';
						$data['page'] = 'orders/purchases';
						$data['title'] = 'My Purchases';
					}
				}
			}
		} 			
		$data['orders'] = $this->order_model->my_purchases();
		$data['escrow_balance'] = $this->escrow_model->balance();		
		$data['local_currency'] = $this->current_user->currency;
		$this->load->library('Layout', $data);
	}
	
	/* User can finalize at either 2 (forced early) or 4 after receiving order. */
	public function finalize($id) {
		$current_order = $this->order_model->load_order($id, array('2'));
		$success = FALSE;
		// Forcing finalize early.
		if($current_order['finalized'] == '0') {
			if($this->escrow_model->pay($current_order['id'], 'vendor') == TRUE) {
				if($this->order_model->progress_order($current_order['id'], '2') == TRUE)
					$success = TRUE;
			} 
		} 

		if($success == FALSE){
			$current_order = $this->order_model->load_order($id, array('4'));
			// Item has been dispatched - either finalized already, or is in escrow and must be paid.
			if(	($current_order['finalized'] == '0' && $this->escrow_model->pay($current_order['id'], 'vendor') == TRUE) ||
				$current_order['finalized'] == '1'){
				if($this->order_model->progress_order($current_order['id'],'4', '6') == TRUE) 
					$success = TRUE;
			}
		}
		redirect('order/list');
	}
	
	public function dispute($id) {
		$current_order = $this->order_model->load_order($id, array('4','2'));
		if($current_order == FALSE)
			redirect('order/list');
			
		$this->load->library('form_validation');	
			
		$data['dispute'] = $this->escrow_model->get_dispute($id);
		$data['form'] = FALSE;
		
		if($data['dispute'] == FALSE) {
			$data['role'] = strtolower($this->current_user->user_role);
			$data['other_role'] = (strtolower($this->current_user->user_role) == 'vendor') ? 'buyer' : 'vendor';
				
			if($this->form_validation->run('order_dispute') == TRUE) {
					
				$dispute = array('order_id' => $id,
								 'disputee' => $this->current_user->user_id,
								 'dispute_message' => $this->input->post('dispute_message'),
								 'last_update' => time());
					
				if($this->escrow_model->dispute($dispute) == TRUE) {
					if($this->order_model->progress_order($id, '5') == TRUE) {
						// Send message to vendor
						$data['from'] = $this->current_user->user_id;
						$details = array('username' => $current_order[$data['other_role']]['user_name'],
										'subject' => "Dispute raised for Order #{$current_order['id']}"); 
						$details['message'] = "{$this->current_user->user_name} has made a dispute regarding Order #{$current_order['id']}. Their issue has been outlined below. An administrator will contact you soon to discuss the issue, but you should contact the other party to try come to some resolution.<br /><br />\n";
						$details['message'].= "Dispute Reason:<br />\n".$this->input->post('dispute_message')."\n<br /><br />";
						for($i = 0; $i < count($current_order['items']); $i++){
							$details['message'] .= "{$current_order['items'][$i]['quantity']} x {$current_order['items'][$i]['name']}<br />\n";
						}
						$details['message'] .= "<br />Total price: {$current_order['currency']['symbol']}{$current_order['price']}";
					 
						$message = $this->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $current_order['id'];
						$this->messages_model->send($message);
						
						$data['returnMessage'] = 'Your dispute has been logged, and will be checked by an administrator soon.';
					}
				}			
			} 
			$data['form'] = TRUE;
		} 
		
		$data['current_order'] = $current_order;
		$data['page'] = 'orders/dispute';
		$data['title'] = 'Raise Dispute';
		$this->load->library('Layout', $data);
	}
	
	// Callback for placing an order.
	public function has_sufficient_balance($param) {
		$balance = $this->bitcoin_model->current_balance();
		if(($param > 0) && ((float)$param <= (float)$balance))
			return TRUE;
			
		return FALSE;
	}
	
};

/* End of File: Order.php */
