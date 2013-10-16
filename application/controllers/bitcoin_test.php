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
		$bitcoin_info = $this->bw_bitcoin->getinfo();
		$this->load->library('bitcoin_crypto');
	}
	
	public function keypair() {
		$key = $this->bitcoin_crypto->getNewKeySet();
		print_r($key);
	}
	
	public function key() {
		
		$accounts = $this->bw_bitcoin->listaccounts(0);
		if(count($accounts) == 0) 
			return TRUE;

		$this->load->model('accounts_model');
		$this->load->model('messages_model');
		$this->load->library('bw_messages');

		$admin = $this->accounts_model->get(array('user_name' => 'admin'));
		print_r($accounts);
		foreach($accounts as $account => $balance){
			$var = "max_".$account."_balance";
			
			if($this->general->matches_any($account, array("", "topup")) == TRUE || $balance == 0 || (float)$balance < (float)$this->bw_config->$var )
				continue;
			echo "<br />".$account."---------<br />\n";
			
			echo "Threshold: {$this->bw_config->$var}";echo "<br />\n";
			$send_amount = $balance-$this->bw_config->$var;
				
			$key = $this->bitcoin_crypto->getNewKeySet();
			$send = $this->bw_bitcoin->sendfrom($account, $key['pubAdd'], $send_amount);
			if(!isset($send['code'])){
			
				$data['from'] = $admin['id'];
				$details = array('username' => $admin['user_name'],
								 'subject' => ucfirst($account)." Wallet Backup");
			
				$time = date("j F Y ga", time());
				$details['message'] = ucfirst($account)." Wallet Backup<br />------ $time <br /><br />\n\nPrivate Key: ".$key['privKey']." <br />";
				$details['message'].= "WIF Format: ".$key['privWIF']." <br /><br />\n";
				$details['message'].= "Amount: BTC ".$send_amount." <br />\n";
				$details['message'].= "Bitcoin Address: ".$key['pubAdd']." <br />\n";
				$details['message'].= "Transaction ID: ".$send." <br />\n";
				
				echo $details['message']."\n";			
				if( isset($admin['pgp']) ) {
					$this->load->library('gpg');			
					$details['message'] = $this->gpg->encrypt($admin['pgp']['fingerprint'], $details['message']);
					
				}
				
				// Prepare the input.
				$message = $this->bw_messages->prepare_input($data, $details);
				if($this->messages_model->send($message)){
					echo "wallet backed up successfully\n\n";
				} else {
					echo "error sending message\n\n";
				}
			} else {
				var_dump($send);
			}
		}
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
