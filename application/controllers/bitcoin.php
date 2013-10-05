<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bitcoin extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
	}
	
	public function cashout() {
		$this->load->library('form_validation');
		
		$data['cashout_address'] = $this->bitcoin_model->get_cashout_address($this->current_user->user_hash);
		if($data['cashout_address'] == '')
			redirect('bitcoin');
		
		$data['current_balance'] = $this->bitcoin_model->current_balance();
		
		if($this->form_validation->run('cashout_form') == TRUE) {
			
			$amount = $this->input->post('amount');
				
			$send = $this->bw_bitcoin->cashout($data['cashout_address'], (float)$amount);
			if(!isset($send['code'])) {			
				// Set up flashdata to display information about the transaction on the bitcoin panel.
				$info = json_encode(array(	'value' => $amount,
											'address' => $data['cashout_address'],
											'txn_id' => $send,
											'category' => 'send')); 
				$this->session->set_flashdata('info', $info);
				$update = $this->bw_bitcoin->walletnotify($send); // Add it immediately to prevent duplicates.
				redirect('bitcoin');
			} else {
				$data['returnMessage'] = 'There was an error processing your transaction. Please ensure you entered a positive, decimal number to send.';
			}
		} 
		
		$data['current_balance'] = $this->bitcoin_model->current_balance();

		$data['page'] = 'bitcoin/cashout';
		$data['title'] = 'Cashout Funds';
		$this->load->library('Layout', $data);	
	}
	
	public function panel() {
		$this->load->library('form_validation');
		
		if($this->input->post('generate_new') == 'Replace') {
			$this->bw_bitcoin->new_address($this->current_user->user_hash);
		} else if($this->form_validation->run('update_cashout_address') === TRUE) {
			$this->bitcoin_model->set_cashout_address($this->current_user->user_hash, $this->input->post('cashout_address'));
		}
		
		$info = (array)json_decode($this->session->flashdata('info'));
		if(count($info) !== 0){
			$action = ($info['category'] == 'send') ? 'sent to' : 'received on';
			$data['returnMessage'] = "{$info['value']}BTC was $action {$info['address']}.<br/ >Transaction ID: {$info['txn_id']}";
		}
		
		$data['current_balance'] = $this->bitcoin_model->current_balance();
		$data['unverified_balance'] = $this->bitcoin_model->unverified_transactions($this->current_user->user_hash);
		$data['topup_address'] = $this->bitcoin_model->get_user_address($this->current_user->user_hash);
		$data['cashout_address'] = $this->bitcoin_model->get_cashout_address($this->current_user->user_hash);
		$data['transactions'] = $this->bitcoin_model->user_txns($this->current_user->user_hash);
		
		$data['title'] = 'Bitcoin Panel';
		$data['page'] = 'bitcoin/panel';
		$this->load->library('Layout', $data);
	}


	// Callbacks
	public function check_bitcoin_address($param) {
		if($param == '')
			return TRUE;
			
		return $this->bw_bitcoin->validateaddress($param);
	}
	
	public function has_sufficient_balance($param) {
		$balance = $this->bitcoin_model->current_balance();
		if(($param > 0) && ((float)$param <= (float)$balance))
			return TRUE;
			
		return FALSE;
	}
};
