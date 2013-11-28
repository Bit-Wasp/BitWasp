<?php

/**
 * Bitcoin Test Controller
 * 
 * This controller is used to test out some bitcoin functions before
 * adding them to the live code. Also has a blockexplorer feature.
 */
 
class Bitcoin_Test extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
	}
	
	public function alert() {
		$alert = $this->bw_bitcoin->check_alert();
		if($alert !== FALSE) {
			$this->load->model('admin_model');
			$this->logs_model->add('Bitcoin Alert', 'Bitcoin Alert', $alert, 'Alert');
			$this->admin_model->set_mode('maintenance');
		}
	}
	
	public function keypair() {
		$this->load->library('bitcoin_crypto');
		$key = $this->bitcoin_crypto->getNewKeySet();
		echo '<pre>';print_r($key);echo '</pre>';
	}

	public function electrum() {
	
		$this->load->library('mpkgen');
		$account = "main";
	
		if(!isset($this->bw_config->electrum_mpk))
			return FALSE;
		// Load the MPK
		$mpk = trim($this->bw_config->electrum_mpk);
			
		// If the iteration record doesn't exist, create one at 0.
		if(!isset($this->bw_config->electrum_iteration)){
			$iteration = 0;
			// Exit if we can't create the record.
			if(!$this->config_model->create('electrum_iteration', '0'))
				return FALSE;
		} else {
			// Record exists, use that.
			$iteration = $this->bw_config->electrum_iteration;
		}
		$address = $this->mpkgen->address($mpk, $iteration);
		if(!is_string($address))
			return FALSE;
			
		// Now need to update electrum_iteration
		echo $address;
	}
	
	/*
	public function sendtome(){
		$value = 0.11158281;
		$from = "topup";
		$to_address = $this->bw_bitcoin->new_main_address();
		$send = $this->bw_bitcoin->sendfrom("topup", $to_address, 0.00113456/2);
		var_dump($send);
	}*/
	
	public function transaction($transaction) {	
		echo '<pre>';
			$transaction = $this->bw_bitcoin->gettransaction($transaction);
			$transaction['time_f'] = $this->general->format_time($transaction['time']);
			print_r($transaction);
		echo '</pre><br />';
	}
	
	public function get_block($block){
		echo '<pre>';
		$info = $this->bw_bitcoin->getblock($block);
		$info['time_f'] = $this->general->format_time($info['time']);
		print_r($info);
		echo '</pre>';
	}
	
	
};
