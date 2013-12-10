<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bitcoin Panel Controller
 *
 * This class handles requesting a password from a user to access a
 * restricted page. 
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Bitcoin
 * @author		BitWasp
 * 
 */

class Bitcoin extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Bitcoin_Model
	 * @see		Libraries/Bw_Bitcoin
	 */
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
	}

	/**
	 * Cash out bitcoins to an address in the database.
	 *
	 * @see 	Libraries/Form_Validation
	 * @see		Libraries/Bw_Bitcoin
	 * @see		Models/Bitcoin_Model
	 * @return	void
	 */	
	public function cashout() {
		$this->load->library('form_validation');
		
		// Abort if there is no cashout address set up.
		$data['cashout_address'] = $this->bitcoin_model->get_cashout_address($this->current_user->user_hash);
		if($data['cashout_address'] == '')
			redirect('bitcoin');
		
		// Load the users current balance.
		$data['current_balance'] = $this->bitcoin_model->current_balance();
		
		if($this->form_validation->run('cashout_form') == TRUE) {
			// If the form is successfully submitted.
			$amount = $this->input->post('amount');
				
			$send = $this->bw_bitcoin->cashout($data['cashout_address'], (float)$amount);
			if(!isset($send['code'])) {			
				$this->bw_bitcoin->walletnotify($send); // Add it immediately to prevent duplicates.
				
				// Set up flashdata to display information about the transaction on the bitcoin panel.
				$info = json_encode(array(	'value' => $amount,
											'address' => $data['cashout_address'],
											'txn_id' => $send,
											'category' => 'send')); 
				$this->session->set_flashdata('info', $info);
				redirect('bitcoin');
			} else {
				// Leave an error message if the user was not redirected.
				$data['returnMessage'] = 'There was an error processing your transaction. Please ensure you entered a positive, decimal number to send.<br />DEBUG: ';print_r($send);
			}
		} 
		
		// Reload the balance.
		$data['current_balance'] = $this->bitcoin_model->current_balance();

		$data['page'] = 'bitcoin/cashout';
		$data['title'] = 'Cashout Funds';
		$this->load->library('Layout', $data);	
	}

	/**
	 * Load the users Bitcoin Panel (Transactions, Topup Address, Cashout Address)
	 *
	 * @see 	Libraries/Form_Validation
	 * @see		Libraries/Bw_Bitcoin
	 * @see		Models/Bitcoin_Model
	 * @return	void
	 */		
	public function panel() {
		$this->load->library('form_validation');
		
		// Check if user is looking to generate a new address.
		if($this->input->post('generate_new') == 'Replace') {
			// If 'Generate' has been clicked, create a new address for the user.
			$this->bw_bitcoin->new_address($this->current_user->user_hash);
		}
		
		// Check if the user is updating their cashout address.
		if($this->input->post('update_cashout') == 'Update') {
			if($this->form_validation->run('update_cashout_address') === TRUE) {
				// If the form is submitted correctly, set a cashout address.
				$this->bitcoin_model->set_cashout_address($this->current_user->user_hash, $this->input->post('cashout_address'));
			}
		}
		
		// If there is any information about a recent transaction, display it.
		$info = (array)json_decode($this->session->flashdata('info'));
		if(count($info) !== 0) {
			$action = ($info['category'] == 'send') ? 'sent to' : 'received on';
			$data['returnMessage'] = "{$info['value']}BTC was $action {$info['address']}.<br/ >Transaction ID: {$info['txn_id']}";
		}
		 
		// Load information about the user.
		$data['current_balance'] = $this->bitcoin_model->current_balance();
		$data['unverified_balance'] = $this->bitcoin_model->unverified_transactions($this->current_user->user_hash);
		$data['topup_address'] = $this->bitcoin_model->get_user_address($this->current_user->user_hash);
		$data['cashout_address'] = $this->bitcoin_model->get_cashout_address($this->current_user->user_hash);
		$data['transactions'] = $this->bitcoin_model->user_txns($this->current_user->user_hash);
		
		$data['title'] = 'Bitcoin Panel';
		$data['page'] = 'bitcoin/panel';
		$this->load->library('Layout', $data);
	}

	// Callback functions for form validation.
	
	/**
	 * Check the bitcoin address is valid (may be empty to reset).
	 *
	 * @param	string
	 * @return	bool
	 */
	public function check_bitcoin_address($param) {
		return ($param == '' || $this->bw_bitcoin->validateaddress($param) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Check the user has sufficient balance to cover the transaction.
	 *
	 * @param	int
	 * @return	bool
	 */
	public function check_has_sufficient_balance($param) {
		$balance = $this->bitcoin_model->current_balance();
		return (($param > 0) && ((float)$param <= (float)$balance)) ? TRUE : FALSE;
	}
};
/* End of File: Bitcoin.php */
