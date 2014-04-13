<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Orders Controller
 *
 * This class handles the buyer and vendor side of the order process.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Orders
 * @author		BitWasp
 * 
 */
class Orders extends CI_Controller {

	public $coin;

	/**
	 * Constructor
	 * 
	 * Load libs/models.
	 *
	 * @access	public
	 * @see		Libraries/Bw_Bitcoin
	 * @see		Libraries/Bw_Messages
	 * @see		Models/Order_Model
	 * @see		Models/Items_Model
	 * @see		Models/Accounts_Model
	 * @see		Models/Bitcoin_Model
	 * @see		Models/Escrow_Model
	 * @see		Models/Messages_Model
	 */
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		$this->load->library('bw_messages');
		$this->load->model('order_model');
		$this->load->model('items_model');
		$this->load->model('accounts_model');
		$this->load->model('bitcoin_model');
		$this->load->model('messages_model');
		$this->load->model('currencies_model');
		$this->coin = $this->currencies_model->get('0');
	}
	
	/**
	 * Vendor Orders
	 * User Role: Vendor
	 * URI: /orders
	 * 
	 * Displays a vendors orders.
	 * 
	 * @param	string or null
	 * @return	void
	 */
	public function vendor_orders($status = NULL) {
		if($status == 'update')
			$data['returnMessage'] = "Order has been updated.";
			
		$info = (array)json_decode($this->session->flashdata('returnMessage'));
		if(count($info) !== 0)
			$data['returnMessage'] = $info['message'];			
		
		$cancel = $this->input->post('cancel');
		if(is_array($cancel)) {
			$id = array_keys($cancel); $id = $id[0];
			$current_order = $this->order_model->load_order($id, array('2'));
			if($current_order == FALSE)
				redirect('purchases');
				
			$this->load->model('transaction_cache_model');
			if(count($this->transaction_cache_model->payments_to_address($current_order['address'])) > 0) {
				$data['returnMessage'] == 'Payments have been made to this address - cannot cancel!';
				break;
			}
				
			if($this->order_model->buyer_cancel($id) == TRUE) 
				$data['returnMessage'] = 'This order has been cancelled.';
		}
			
		$this->load->library('form_validation');
		$data['coin'] = $this->coin;
		
		$data['orders'] = $this->order_model->vendor_orders();
		$this->load->model('review_auth_model');
		$id_list = array();
		foreach($data['orders'] as $t_order) {
			$id_list[] = $t_order['id'];
		}
		$data['review_auth'] = $this->review_auth_model->user_tokens_by_order($id_list);		
		$data['local_currency'] = $this->current_user->currency;	
		$data['page'] = 'orders/vendor_orders';
		$data['title'] = 'My Orders';
		$this->load->library('Layout', $data);
	}

	/**
	 * Vendor Accept
	 * 
	 * This page displays the form for vendors to approve an order. This
	 * involves selecting if the payment will be up-front, or escrow. 
	 * 
	 * @param	int	$id
	 */
	public function vendor_accept($id) {
		$data['order'] = $this->order_model->load_order($id, array('1'));
		if($data['order'] == FALSE)
			redirect('orders');

		$this->load->library('form_validation');
		$this->load->model('bitcoin_model');
		$this->load->model('accounts_model');
		$data['available_public_keys'] = $this->accounts_model->bitcoin_public_keys($this->current_user->user_id);
		
		$data['fees']['shipping_cost'] = $data['order']['shipping_costs'];
		$data['fees']['fee'] = $data['order']['fees'];
		$data['fees']['total'] = $data['order']['shipping_costs']+$data['order']['fees'];

		if($this->input->post('vendor_accept_order') == 'Accept Order') {
			if($this->form_validation->run('vendor_accept_order') == TRUE) {
				if(count($data['available_public_keys']) == 0) {
					$data['returnMessage'] = 'You have no available public keys to use in this order!';
				} else {
					$this->load->library('Electrum');
					$this->load->library('BitcoinLib');
					$this->load->library('Raw_transaction');
					$this->load->library('Bw_bitcoin');
					$vendor_public_key = $data['available_public_keys'][0];
					$admin_public_key = $this->bitcoin_model->get_next_key();
					
					$public_keys = array($data['order']['buyer_public_key'], $vendor_public_key['public_key'], $admin_public_key['public_key']);
					$multisig_details = Raw_transaction::create_multisig('2', $public_keys);
					
					// If no errors, we're good!
					if($multisig_details !== FALSE) {
						$this->bitcoin_model->log_key_usage('order', $this->bw_config->electrum_mpk, $admin_public_key['iteration'], $admin_public_key['public_key'], $data['order']['id']);
						$this->accounts_model->delete_bitcoin_public_key($vendor_public_key['id']);
						if($this->order_model->progress_order($data['order']['id'], '1') == TRUE) {
							
							$this->bitcoin_model->add_watch_address($multisig_details['address'], 'order');
							$this->order_model->set_user_public_key($data['order']['id'], 'vendor', $vendor_public_key['public_key']);
							$this->order_model->set_user_public_key($data['order']['id'], 'admin', $admin_public_key['public_key']);
							if($this->input->post('selected_escrow') == '1') {
								$this->order_model->set_selected_escrow($data['order']['id']);
								$extra = ((($data['order']['price']+$data['order']['shipping_costs'])/100)*$this->bw_config->escrow_rate);
								$this->order_model->set_extra_fees($data['order']['id'], $extra);
							}
							$this->order_model->set_address_details($data['order']['id'], $multisig_details);
							
							$subject = 'Vendor has confirmed order #'.$data['order']['id'];
							$message = 'Your order with '.$data['order']['vendor']['user_name'].' has been confirmed.\n'.(($this->input->post('selected_escrow') == '1') ? 'Escrow payment was chosen. Once you pay to the address, the vendor will ship the goods. You do not need to sign before you receive the goods. You can raise a dispute if you have any issues.' : 'You must make payment up-front to complete this order. Once the full amount is sent to the address, you must sign a transaction paying the vendor.' );
							$this->order_model->send_order_message($data['order']['id'], $data['order']['buyer']['user_name'], $subject, $message);
							$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'You have accepted this order! Visit the orders page to see the bitcoin address!')));
														
							redirect('orders');
							
						} else {
							$data['returnMessage'] = 'There was an error creating your order.';
						}
					} else {
						$data['returnMessage'] = 'Unable to create address.';
					}
				}
			}
		}
		
		$data['title'] = 'Accept Order #'.$data['order']['id'];
		$data['page'] = 'orders/vendor_accept';
		$data['local_currency'] = $this->current_user->currency;	
		$this->load->library('Layout', $data);
	}
	
	// Buyer pages
	
	/**
	 * Purchase an item/add item to order.
	 * User Role: Buyer
	 * URI: /purchase/$item_hash
	 * 
	 * @access	public
	 * @see		Models/Items_Model
	 * @see		Models/Order_Model
	 * @see		Models/Bitcoin_Model
	 * 
	 * @param	string
	 * @return	void
	 */
	public function purchase_item($item_hash) {	
		$this->load->library('form_validation');
		$this->load->model('accounts_model');
		$this->load->model('shipping_costs_model');
		$item_info = $this->items_model->get($item_hash);
		if($item_info == FALSE) 
			redirect('items');
		
		$account = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash));
		$shipping_costs = $this->shipping_costs_model->find_location_cost($item_info['id'], $account['location']);
		var_dump($shipping_costs);
		if($shipping_costs == FALSE) {
			$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'This item is not available in your location. Message the vendor to discuss availability.')));
			redirect('item/'.$item_info['hash']);
		}
		$order = $this->order_model->load($item_info['vendor_hash'],'0');
		if($order == FALSE) {
			// New order; Need to create
			$new_order = array(	'buyer_id' => $this->current_user->user_id,
								'vendor_hash' => $item_info['vendor_hash'],
								'items' => $item_info['hash']."-1",
								'price' => $item_info['price_b'],
								'currency' => '0');
			$this->session->set_flashdata('returnMessage', json_encode(array('message' => (($this->order_model->add($new_order) == TRUE) ? 'Your order has been created!' : 'Unable to add your order at this time, please try again later.'))));
		} else {
			// Already have order, update it
			if($order['progress'] == '0') {
				$update = array('item_hash' => $item_hash,
								'quantity' => '1');
				
				$this->session->set_flashdata('returnMessage', json_encode(array('message' => (($this->order_model->update_items($order['id'], $update) == TRUE) ? 'Your order has been updated.' : 'Unable to update your order at this time.'))));
			} else {
				$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'Your order has already been created, please contact your vendor to discuss any further changes')));
			}
		}
		redirect('purchases');
	}

	/**
	 * Confirm Order
	 * User Role: Buyer
	 * URI: /purchases/confirm/$id
	 * 
	 * @param	int	$id
	 * @return	void
	 */	
	public function buyer_confirm($id) {
		$this->load->library('form_validation');
		$this->load->model('bitcoin_model');
		$this->load->model('fees_model');
		$this->load->model('shipping_costs_model');
		$this->load->model('accounts_model');
		
		$data['order'] = $this->order_model->load_order($id, array('0'));
		if($data['order'] == FALSE)
			redirect('purchases');
		
		$data['title'] = 'Place Order #'.$data['order']['id'];
		$data['page'] = 'orders/buyer_confirm_purchase';
		$data['header_meta'] = $this->load->view('orders/encryption_header', NULL, true);
		
		$data['fees']['shipping_cost'] = $this->shipping_costs_model->costs_to_location($data['order']['items'], $data['order']['buyer']['location']);
		$data['fees']['fee'] = $this->fees_model->calculate(($data['order']['price']+$data['fees']['shipping_cost']));
		$data['fees']['total'] = $data['fees']['shipping_cost']+$data['fees']['fee'];
		$data['total'] = $data['order']['price']+$data['fees']['total'];
	
		if($this->form_validation->run('order_place') == TRUE) {
			if($this->order_model->set_user_public_key($id, 'buyer', $this->input->post('bitcoin_public_key')) == TRUE) {
				if($this->order_model->progress_order($data['order']['id'], '0') == FALSE) {
					$data['returnMessage'] = 'Unable to place your order at this time, please try again later.';
				} else {
					$this->order_model->set_price($data['order']['id'], $data['order']['price']);
					$this->order_model->set_fees($data['order']['id'], $data['fees']['fee']);
					$this->order_model->set_shipping_costs($data['order']['id'], $data['fees']['shipping_cost']);
					// Send message to vendor						
					$subject = "New Order #{$data['order']['id']} from ".$this->current_user->user_name;
					$message = "You have received a new order from {$this->current_user->user_name}.<br />\nOrder ID: #{$data['order']['id']}<br />\n";
					for($i = 0; $i < count($data['order']['items']); $i++) {
						$message .= "{$data['order']['items'][$i]['quantity']} x {$data['order']['items'][$i]['name']}<br />\n";
					}
					$message .= "<br />Total price: {$data['order']['currency']['symbol']}{$data['order']['price']}<br /><br />\nBuyer Address: <br />\n".$this->input->post('buyer_address');
					$this->order_model->send_order_message($data['order']['id'], $data['order']['vendor']['user_name'], $subject, $message);

					$this->session->set_userdata('returnMessage', json_encode(array('message' => 'Your order has been placed. Once accepted you will be able to pay to the address')));
					redirect('purchases');
				}
			}
		} 			
		$data['orders'] = $this->order_model->buyer_orders();
		$data['local_currency'] = $this->current_user->currency;
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Buyer Orders
	 * URI: /purchases
	 * 
	 * Lists all purchases a buyer has made in the past. Required User Role: Buyer.
	 * Also handles updating an order, placing/cancelling/finalizing an order.
	 * 
	 * @return	void
	 */
	public function buyer_orders() {
		$info = (array)json_decode($this->session->flashdata('returnMessage'));
		if(count($info) !== 0)
			$data['returnMessage'] = $info['message'];
			
		$this->load->library('form_validation');		
		$data['coin'] = $this->coin;
		
		// Process Form Submission
		// Check if we are Proceeding an order, or Recounting it.
		$place_order = $this->input->post('place_order');
		$recount = $this->input->post('recount');
		if(is_array($place_order) || is_array($recount)) {
			// Load the ID of the order.
			$id = (is_array($place_order)) ? array_keys($place_order) : array_keys($recount); $id = $id[0];
			
			// If the order cannot be loaded (progress == 0), redirect to Purchases page.
			$current_order = $this->order_model->load_order($id, array('0'));
			if($current_order == FALSE)
				redirect('purchases');

			// Loop through items in order, and update each.
			$list = $this->input->post('quantity');
			foreach($list as $hash => $quantity) {
				$item_info = $this->items_model->get($hash);
				if($item_info !== FALSE) {
					$update = array('item_hash' => $hash,
									'quantity' => $quantity);
					$this->order_model->update_items($current_order['id'], $update, 'force');
				}
			}
			// If the order is being placed, redirect to there.
			$url = (is_array($place_order)) ? 'purchases/confirm/'.$current_order['id'] : 'purchases';
			redirect($url);
		}
		
		// Process 'cancelled' orders
		$cancel = $this->input->post('cancel');
		if(is_array($cancel)) {
			$id = array_keys($cancel); $id = $id[0];
			$current_order = $this->order_model->load_order($id, array('1'));
			if($current_order == FALSE)
				redirect('purchases');
			
			if($this->order_model->buyer_cancel($id) == TRUE) 
				$data['returnMessage'] = 'This order has been cancelled';
		}
		
		// Process 'received' orders during up-front payments
		$received = $this->input->post('received');
		if(is_array($received)) {
			$id = array_keys($received); $id = $id[0];
			$current_order = $this->order_model->load_order($id, array('5'));
			if($current_order == FALSE)
				redirect('purchases');

			// Prevent escrow orders from being marked as 'received'.
			if($current_order['vendor_selected_escrow'] == '1') {
				$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'You must sign & broadcast this transaction!')));
				redirect('purchases');
			}
			
			if($this->order_model->progress_order($id, '5', '7') == TRUE) {
				$this->order_model->set_received_time($id);
				$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'Your order has been marked as received. Please leave feedback for this user!')));
				redirect('purchases');
			}
		} 
		
		// Page Data
		// Load information about orders.
		$this->load->model('review_auth_model');
		$data['orders'] = $this->order_model->buyer_orders(); 
		if($data['orders'] !== FALSE) {
			$id_list = array();
			foreach($data['orders'] as $t_order) {
				$id_list[] = $t_order['id'];
			}
			$data['review_auth'] = $this->review_auth_model->user_tokens_by_order($id_list);
		}
		
		$data['page'] = 'orders/buyer_orders';
		$data['title'] = 'My Purchases';
		$data['local_currency'] = $this->current_user->currency;		
		$this->load->library('Layout', $data);
	}


	// All users can view these pages

	/**
	 * Raise a Dispute over an order.
	 * User Role: Buyer/Vendor
	 * URI: /order/dispute/$id or orders/dispute/$id
	 * 
	 * @access	public
	 * @see		Models/Order_Model
	 * @see		Models/Escrow_Model
	 * @see		Models/Messages_Model
	 * @see		Models/Items_Model
	 * @see		Libraries/Form_Validation
	 * @see		Libraries/Bw_Messages
	 * 
	 * @param	int
	 * @return	void
	 */
	public function dispute($id) {
		$list_page = ($this->current_user->user_role == 'Vendor') ? 'purchases' : 'orders';
		$data['dispute_page'] = ($this->current_user->user_role == 'Vendor') ? 'orders/dispute/'.$id : 'purchases/dispute/'.$id;
		$data['cancel_page'] = ($this->current_user->user_role == 'Vendor') ? 'orders' : 'purchases';
		
		$data['current_order'] = $this->order_model->load_order($id, array('7','6','5','4'));
		if($data['current_order'] == FALSE)
			redirect($list_page);

		$this->load->library('form_validation');	
		$this->load->model('disputes_model');
		
		$data['dispute'] = $this->disputes_model->get_by_order_id($id);
		$data['disputing_user'] = ($data['dispute']['disputing_user_id'] == $data['current_order']['buyer']['id']) ? $data['current_order']['buyer'] : $data['current_order']['vendor'];
		$data['other_user'] = ($data['dispute']['other_user_id'] == $data['current_order']['buyer']['id']) ? $data['current_order']['buyer'] : $data['current_order']['vendor'];
		
		$data['form'] = TRUE;			// Tell the view whether to display the create dispute form
		$data['post_update'] = TRUE;	// Tell the view whether to display the post_update (depends on $record['final_response'])
		
		if($data['dispute'] == FALSE && !in_array($data['current_order']['progress'], array('6','7'))) {
			// Display form to allow user to raise a dispute.
			$data['role'] = strtolower($this->current_user->user_role);
			$data['other_role'] = ($data['role'] == 'vendor') ? 'buyer' : 'vendor';
			
			if($this->form_validation->run('order_dispute') == TRUE) {
					
				$other_user = ($this->current_user->user_id == $data['current_order']['buyer']['id']) ? $data['current_order']['vendor']['id'] : $this->current_user->user_id;
					
				$dispute = array('disputing_user_id' => $this->current_user->user_id,
								 'dispute_message' => $this->input->post('dispute_message'),
								 'last_update' => time(),
								 'other_user_id' => $other_user,
								 'order_id' => $id);
					
				// Need to force the new_progress to 6 if the order is at 3 or 4.
				$new_progress = (in_array($data['current_order']['progress'], array('4','3'))) ? '6' : '0';// 0 means unset, default value.
				
				if($this->disputes_model->create($dispute) == TRUE && $this->order_model->progress_order($id, $data['current_order']['progress'], $new_progress) == TRUE) {
					// Send message to vendor
					$info['from'] = $this->current_user->user_id;
					$details = array('username' => $data['current_order'][$data['other_role']]['user_name'],
									'subject' => "Dispute raised for Order #{$data['current_order']['id']}"); 
					$details['message'] = "{$this->current_user->user_name} has made a dispute regarding Order #{$data['current_order']['id']}. Their issue has been outlined below. An administrator will contact you soon to discuss the issue, but you should contact the other party to try come to some resolution.<br /><br />\nDispute Reason:<br />\n".$this->input->post('dispute_message')."\n<br /><br />";
					$message = $this->bw_messages->prepare_input($info, $details);
					$message['order_id'] = $data['current_order']['id'];
					$this->messages_model->send($message);

					redirect($data['dispute_page']);
				} else {
					$data['returnMessage']='There was an error';
				}
			} 
		} else {
			$data['form'] = FALSE;

			// If the message is updated: 

			if($data['dispute']['final_response'] == '0' && $this->input->post('post_dispute_message') == 'Post Message') {
				if($this->form_validation->run('add_dispute_update') == TRUE) {
					// Update the dispute record.
					$update = array('posting_user_id' => $this->current_user->user_id,
									'order_id' => $data['current_order']['id'],
									'dispute_id' => $data['dispute']['id'],
									'message' => $this->input->post('update_message'));
					if($this->disputes_model->post_dispute_update($update) == TRUE)
						redirect($data['dispute_page']);
				}
			}

		}
		
		$data['page'] = 'orders/dispute';
		$data['title'] = 'Raise Dispute';
		$this->load->library('Layout', $data);
	}

	public function details($order_id) {
		$data['order'] = $this->order_model->get($order_id); // no restriction on buyer/vendor
		if($data['order'] == FALSE)
			redirect('');
		
		// Work out if the user is allowed to view this order.
		if(!$this->current_user->user_role == 'Admin' 
		&& !($this->current_user->user_id == $data['order']['buyer']['id']) 
		&& !($this->current_user->user_id == $data['order']['vendor']['id'])) 
			redirect('');
		// Only allow access when the order is confirmed by the buyer.
		if($data['order']['progress'] == '0')
			redirect('');
		
		if($this->current_user->user_role == 'Buyer'){
			$data['action_page'] = 'purchases/details/'.$order_id;
		} else if($this->current_user->user_role == 'Vendor'){
			$data['action_page'] = 'orders/details/'.$order_id;
		} else if($this->current_user->user_role == 'Admin'){
			$data['action_page'] = 'admin/order/'.$order_id;
		} 
		
		$this->load->library('form_validation');
		$this->load->library('bw_bitcoin');
		$this->load->library('Raw_transaction');
		
		$data['display_form'] = FALSE;

		// This block works out if the 'input partially signed transaction' 
		// form should be displayed. This happens if the partially signed 
		// transaction is unset, and any of the following is true:
		// - The order is progress 3, and the user is the buyer
		// - The order is progress 4, escrow, and the role is the vendor.
		// - The order has been disputed, and anyone may sign.
		if($data['order']['partially_signed_transaction'] == '' && $data['order']['unsigned_transaction'] !== '') 
			if($data['order']['progress'] == '3' && $this->current_user->user_role == 'Buyer'
			|| $data['order']['progress'] == '4' && $data['order']['vendor_selected_escrow'] == '1' && $this->current_user->user_role == 'Vendor'
			|| $data['order']['progress'] == '6') 
				$data['display_form'] = TRUE;

		// Only allow access to the form handling script if the form is allowed to be displayed.
		if($data['display_form'] == TRUE && $this->input->post('submit_signed_transaction') == 'Submit Transaction') {
			if($this->form_validation->run('input_transaction') == TRUE) { 
				$validate = Raw_transaction::validate_signed_transaction($this->input->post('partially_signed_transaction'), str_replace("'", "", $data['order']['json_inputs']));

				if($validate == FALSE) {
					$data['invalid_transaction_error'] = 'Enter a valid partially signed transaction.';
				} else {
					$decode = Raw_transaction::decode($this->input->post('partially_signed_transaction'));
					$this->load->model('transaction_cache_model');
					
					// Check that the outputs are acceptable.
					$check = $this->transaction_cache_model->check_if_expected_spend($decode['vout']);
					
					// $check will contain the order address if the vouts
					// lead to the same unique hash we store when generating the transaction.
					if($check == $data['order']['address']) {
						if($data['order']['progress'] == '3') {
							// Buyer must sign early before vendor dispatches.
							if($this->order_model->progress_order($order_id, '3') == TRUE) 
								$this->order_model->set_partially_signed_transaction($order_id, $this->input->post('partially_signed_transaction'));
						
						} else if($data['order']['progress'] == '4') {
							// Vendor indicates they have dispatched.
							if($this->order_model->progress_order($order_id, '4') == TRUE) 
								$this->order_model->set_partially_signed_transaction($order_id, $this->input->post('partially_signed_transaction'));
						
						} else if($data['order']['progress'] == '6') {
							$this->order_model->set_partially_signed_transaction($order_id, $this->input->post('partially_signed_transaction'));
							// Nothing happens. Progressed when payment is broadcast.
						}
						$this->session->set_flashdata('returnMessage', json_encode(array('message' => 'Your partially signed transaction has been saved!')));
						redirect($data['action_page']);
					} else {
						$data['invalid_transaction_error'] = 'This transaction is invalid.';
					}
				}
			}
		}
		
		$addrs = array(	BitcoinLib::public_key_to_address($data['order']['buyer_public_key'], $this->coin['crypto_magic_byte']) => 'buyer',
						BitcoinLib::public_key_to_address($data['order']['vendor_public_key'], $this->coin['crypto_magic_byte']) => 'vendor',
						BitcoinLib::public_key_to_address($data['order']['admin_public_key'], $this->coin['crypto_magic_byte']) => 'admin');
		$data['paying_to'] = array();
		if(strlen($data['order']['partially_signed_transaction']) > 0) {
			$disp_tx = Raw_transaction::decode($data['order']['partially_signed_transaction']);
			foreach($disp_tx['vout'] as $out) { 	$data['paying_to'][] = array('address' => $out['scriptPubKey']['addresses'][0], 'value' => $out['value'], 'user' => $addrs[$out['scriptPubKey']['addresses'][0]]); 		}
		} else if(strlen($data['order']['unsigned_transaction']) > 0) {
			$disp_tx = Raw_transaction::decode($data['order']['unsigned_transaction']);
			foreach($disp_tx['vout'] as $out) { 	$data['paying_to'][] = array('address' => $out['scriptPubKey']['addresses'][0], 'value' => $out['value'], 'user' => $addrs[$out['scriptPubKey']['addresses'][0]]);  	}
		}
		$data['fees']['shipping_cost'] = $data['order']['shipping_costs'];
		$data['fees']['fee'] = $data['order']['fees'];
		$data['fees']['escrow_fees'] = $data['order']['extra_fees'];
		$data['fees']['total'] = $data['order']['shipping_costs']+$data['order']['fees'];
		$data['user_role'] = $this->current_user->user_role;
		$data['local_currency'] = $this->current_user->currency;		
		
		$info = (array)json_decode($this->session->flashdata('returnMessage'));
		if(count($info) !== 0)
			$data['returnMessage'] = $info['message'];			
		
		$this->load->library('ciqrcode');
		$data['payment_url'] = "bitcoin:{$data['order']['address']}?amount={$data['order']['order_price']}&message=Order+{$data['order']['id']}&label=Order+{$data['order']['id']}";
		$data['qr'] = $this->ciqrcode->generate_base64(array('data' => $data['payment_url']));

		$data['page'] = 'orders/details';
		$data['title'] = 'Order Details: #'.$data['order']['id'];
		$this->load->library('Layout', $data);
	}

	// Callback functions

	/**
	 * Check Public Key
	 * 
	 * Checks if 
	 * 
	 * @param	string	$public_key
	 * @return	boolean
	 */
	public function check_bitcoin_public_key($public_key) {
		$this->load->library('BitcoinLib');
		return (BitcoinLib::validate_public_key($public_key) == TRUE) ? TRUE : FALSE;
	}
		
};

/* End of File: Order.php */
