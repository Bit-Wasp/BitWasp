<?php

/**
 * Auto-Finalize Orders
 *
 * This job is used to finalize orders for which the buyer has not logged
 * in for 30 days or more. It will also check for orders where the vendor
 * has not logged in for 30 days, but the order has been finalized early
 * by the buyer.
 * The default frequency of this job is 24 hours.
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	Auto-Finalize Orders
 * @author		BitWasp
 */
class Auto_Finalize_Orders {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Auto Finalize Orders',
							'description' => 'An autorun job to automatically finalize, or refund, orders where the buyer or vendor has not logged in for a set period of time.',
							'index' => 'auto_finalize_orders',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	/**
	 * Threshold
	 * 
	 * This setting is set on the admin/edit/items page. It is the number
	 * of days orders covered by this job are allowed to proceed without
	 * change. If not set in the config table, the default is for this 
	 * feature to be disabled.
	 */
	 public $threshold;
	
	/**
	 * Constructor
	 * 
	 * Loads the CodeIgniter framework, and other dependencies.
	 */
	public function __construct() {
		
		$this->CI = &get_instance();
		$this->threshold = $this->CI->bw_config->auto_finalize_threshold;
			
		$this->CI->load->model('order_model');
		$this->CI->load->model('accounts_model');
		$this->CI->load->model('escrow_model');
		$this->CI->load->model('messages_model');
		$this->CI->load->model('bitcoin_model');
		$this->CI->load->library('bw_messages');
	}
	
	/**
	 * Job
	 * 
	 * This job will check for orders which have not progressed due to 
	 * one of the parties not logging in for excessive periods of time.
	 * For orders already finalized, but the vendor has not confirmed dispatch
	 * over the allowed period of time, the funds will be returned to the
	 * buyer automatically. A message is sent to both parties informing 
	 * them of this, as well as a transaction on their bitcoin panel.
	 * 
	 * For orders in escrow, where the items have been dispatched and 
	 * the order unfinalized, if the buyer does not log in over the 
	 * defined period, the funds they have pledged in the escrow will 
	 * automatically be sent on to the vendor. Both users will receive
	 * a message and a transaction in their bitcoin panel informing them
	 * of this.
	 * 
	 */
	public function job() {
		
		// If the threshold is zero, job is disabled. Return false
		// so last_update doesn't change.
		if($this->threshold == '0')
			return FALSE;
			
		$result = TRUE;
		
		// Load all orders at progress=3, not updated for 30 days.
		// Give a refund to the buyer (as vendor has not logged in to send
		// the product). 
		$orders = $this->CI->order_model->admin_orders_by_progress('3','1');
		if($orders !== FALSE) {
			
			foreach($orders as $order){
				
				$vendor = $this->CI->accounts_model->get(array('user_hash' => $order['vendor_hash']));
				
				if($vendor !== FALSE && $vendor['login_activity'] < (time()-$this->threshold*24*60*60)) {
					
					$payment_message = "Autorefund #{$order['id']}";
					// Debit vendor account manually.
					// As escrow record does not exist, update transactions manually.
					$debit = array('user_hash' => $vendor['user_hash'],
									'value' => (float)$order['amount']);
					$credit = array('user_hash' => $buyer['user_hash'],
									'value' => (float)$order['amount']);
					$transactions = array($debit, $credit);
					
					// Update balances and set progress to 7.
					if($this->CI->bitcoin_model->update_credits($transactions) == TRUE
					  && $this->CI->order_model->admin_set_progress($order['id'], '7') == TRUE ) {
						
						// Send message to vendor
						$data['from'] = $admin['id'];
						$details = array('username' => $vendor['user_name'],
										 'subject' => "Order #{$order['id']} has been auto-refunded.");
						$details['message'] = "Today the balance for order #{$order['id']} has been refunded to {$buyer['user_name']}, as you have not logged in to confirm dispatch in {$this->threshold} days. BTC {$order['amount']} has been debited from your account and credited back to the user. Please note that failure to confirm dispatch, even if you have done so, within the allowed period of time, will result in this happening again, and will give users a reasonable opportunity to rate you negatively. If you wish to discuss this further, click reply to send a message to admin.";
						$message = $this->CI->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $order['id'];
						$this->CI->messages_model->send($message);
					
						// Send message to buyer
						$data['from'] = $admin['id'];
						$details = array('username' => $buyer['user_name'],
										 'subject' => "Order #{$order['id']} has been auto-finalized.");
						$details['message'] = "Today the balance for order #{$order['id']} has automatically refunded to you, as {$vendor['user_name']} has not logged in for the last {$this->threshold} days. If you have any concerns after the order has been made, you can always raise a dispute regarding an order, and bring it to the attention of the site administrator, but in cases where the vendor does not log in, you will receive an automatic update. Please note the threshold of time for this may change as the administrator sees fit. If you wish to discuss this further, click reply to send a message to admin.";
						$message = $this->CI->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $order['id'];
						$this->CI->messages_model->send($message);
						
					} else {
						$result = FALSE;
					}
				}
			}
		}
		
		// Load all orders at progress=4, and finalized='0' (for escrow)
		// and buyer has not logged in for 30 days. Give funds to vendor
		// as buyer has not logged in to send funds, or raise a dispute.
		$orders = $this->CI->order_model->admin_orders_by_progress('4','0');
		if($orders !== FALSE){
			foreach($orders as $order){
				$buyer = $this->CI->accounts_model->get(array('id' => $order['buyer_id']));
				
				if($buyer !== FALSE && $buyer['login_activity'] < (time()-$this->threshold*24*60*60)) {
					
					$escrow_record = $this->CI->escrow_model->get($order['id']);
					$payment_message = "Autofinalize #{$order['id']}";
					
					// Attempt to pay the vendor and automatically update the progress.
					if($escrow_record !== FALSE 
					  && $this->CI->escrow_model->pay($order['id'], 'vendor', $payment_message) == TRUE
					  && $this->CI->order_model->admin_set_progress($order['id'], '7') == TRUE ) {
						
						$vendor = $this->CI->accounts_model->get(array('user_hash' => $order['vendor_hash']));	   
						$admin = $this->CI->accounts_model->get(array('user_name' => "admin"));	   
						
						// delete escrow record (removes escrow balances)
						// show a transaction in both users accounts - account for balance change.
						// set order progress to 7
						// send message to vendor & buyer
						
						// Send message to vendor
						$data['from'] = $admin['id'];
						$details = array('username' => $vendor['user_name'],
										 'subject' => "Order #{$order['id']} has been auto-finalized.");
						$details['message'] = "Today the order #{$order['id']} has automatically been finalized on the behalf of {$buyer['user_name']}, as they have not logged in for {$this->threshold} days. BTC {$escrow_record['amount']} has been credited to your account to complete this order. Please now review this user. If you wish to discuss this further, click reply to send a message to admin.";
						$message = $this->CI->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $order['id'];
						$this->CI->messages_model->send($message);
					
						// Send message to buyer
						$data['from'] = $admin['id'];
						$details = array('username' => $buyer['user_name'],
										 'subject' => "Order #{$order['id']} has been auto-finalized.");
						$details['message'] = "Today the order #{$order['id']} has automatically been finalized on your behalf, as the goods have been dispatched however you have not logged in to confirm receipt. If you have an issue with a vendor please raise a dispute regarding an order, as unfulfilled agreements can be the basis for users to rate you negatively. If you wish to discuss this further, click reply to send a message to admin.";
						$message = $this->CI->bw_messages->prepare_input($data, $details);
						$message['order_id'] = $order['id'];
						$this->CI->messages_model->send($message);
					
					} else {
						$result = FALSE;
					}
				}
			}
		}
		return $result;
	}
	
};
