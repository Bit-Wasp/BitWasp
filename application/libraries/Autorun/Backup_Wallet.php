<?php
/**
 * Backup Wallet Job
 * 
 * Used to securely transfer excessive funds from the live wallet to 
 * an offline wallet.

 * @package		BitWasp
 * @subpackage	Autorun
 * @category	User Inactivity
 * @author		BitWasp
 */


class Backup_Wallet {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Backup Wallet',
							'description' => 'Job to back up excessive funds from the wallet.',
							'index' => 'backup_wallet',
							'interval' => '0',
							'interval_type' => 'hours');
	public $CI;

	/**
	 * Constructor
	 * 
	 * Loads the CodeIgniter framework
	 */	
	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->library('bw_bitcoin');		
	}
	
	/**
	 * Job
	 * 
	 * This function is called by the Autorun script. 
	 * We load the oldest age of messages from the config library. If 
	 * set to zero, we don't need to run the job and execution terminates.
	 * 
	 */	
	public function job() {
		
		// Check if there are any accounts/bitcoind is offline.
		$accounts = $this->CI->bw_bitcoin->listaccounts(0);
		if(count($accounts) == 0) 
			return TRUE;

		$bitcoin_info = $this->CI->bw_bitcoin->getinfo();
		//if($bitcoin_info['testnet'] == TRUE)
			//return FALSE;
						
		// Load models and libraries.
		$this->CI->load->model('accounts_model');
		$this->CI->load->model('messages_model');
		$this->CI->load->library('bw_messages');
		$this->CI->load->library('bitcoin_crypto');		

		$admin = $this->CI->accounts_model->get(array('user_name' => 'admin'));
		// Loop through each account
		$success = TRUE;
		foreach($accounts as $account => $balance){
			$var = "max_".$account."_balance";
			// Do not touch the accounts "", "topup", ones with a zero balance, or 
			// accounts whos balance is not above the backup threshold.
			if(!isset($this->CI->bw_config->$var) || $account == 'topup' || $balance <= 0 || $balance < $this->CI->bw_config->$var) 
				continue;
			
			//if($this->CI->general->matches_any($account, array("", "topup")) == TRUE || $balance <= 0 || (float)$balance < (float)$this->CI->bw_config->$var )
				//continue;		
						
			// Generate a new keypair.
			$key = $this->CI->bitcoin_crypto->getNewKeySet();
			
			// Send the excess amount to the newly generated public address.
			$send_amount = ($balance-$this->CI->bw_config->$var);
			$send = $this->CI->bw_bitcoin->sendfrom($account, $key['pubAdd'], (float)$send_amount);
			if(!isset($send['code'])){
				// Send the wallet to the admin user.
				$data['from'] = $admin['id'];
				$details = array('username' => $admin['user_name'],
								 'subject' => ucfirst($account)." Wallet Backup");
			
				$time = date("j F Y ga", time());
				$details['message'] = ucfirst($account)." Wallet Backup\n ------ $time\n\n";
				$details['message'] = "Private Key: ".$key['privKey']." \n";
				$details['message'].= "WIF Format: ".$key['privWIF']." \n\n";
				$details['message'].= "Amount: BTC ".$send_amount." \n";
				$details['message'].= "Bitcoin Address: ".$key['pubAdd']." \n";
				$details['message'].= "Transaction ID: ".$send." \n";
				
				// If the user has GPG, encrypt the message.
				if( isset($admin['pgp']) ) {
					$this->CI->load->library('gpg');			
					$details['message'] = $this->CI->gpg->encrypt($admin['pgp']['fingerprint'], $details['message']);
				}
				// Prepare the input.
				$message = $this->CI->bw_messages->prepare_input($data, $details);
				if($this->CI->messages_model->send($message) !== TRUE)
					$success = FALSE; 
				
			} else {
				$success = FALSE;
			}
		}
		return $success;
	}
};
