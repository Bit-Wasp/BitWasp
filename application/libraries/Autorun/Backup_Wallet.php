<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Backup Wallet Job
 * 
 * Used to securely transfer excessive funds from the live wallet to 
 * an offline wallet.
 * Will check if the ECDSA or electrum option is chosen. ECDSA will generate
 * a new keypair, send the bitcoins to that bitcoin address. Electrum will
 * generate a deterministic public key and address, send coins to there,
 * and increase the iteration index on record by one.
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	Backup Wallet
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
		$this->CI->load->library('bw_bitcoin');		
		
		// If the backups are disabled, then abort.
		if($this->CI->bw_config->balance_backup_method == 'Disabled')
			return FALSE;
		
		// Check if there are any accounts/bitcoind is offline.
		$accounts = $this->CI->bw_bitcoin->listaccounts();
		if(count($accounts) == 0 || $this->CI->general->matches_any($accounts, array(NULL, FALSE)) ) 
			return FALSE;
			
		$bitcoin_info = $this->CI->bw_bitcoin->getinfo();
		//if($bitcoin_info['testnet'] == TRUE)
			//return FALSE;
						
		// Load models and libraries.
		$this->CI->load->model('accounts_model');
		$this->CI->load->model('messages_model');
		$this->CI->load->library('bw_messages');

		$admin = $this->CI->accounts_model->get(array('user_name' => 'admin'));
		
		// Loop through each account
		$success = TRUE;
		foreach($accounts as $account => $balance) {
			$var = "max_".$account."_balance";
			// Do not touch the accounts "", "topup", ones with a zero balance, or 
			// accounts whos balance is not above the backup threshold.
			if(!isset($this->CI->bw_config->$var) || $account == 'topup' || $balance <= 0 || isset($this->CI->bw_config->$var) && $balance < $this->CI->bw_config->$var) 
				continue;

			// Send the excess amount to the newly generated public address.
			$send_amount = ($balance-$this->CI->bw_config->$var);
	
			// Send coins to newly generated ECDSA keypair's address
			if($this->CI->bw_config->balance_backup_method == 'ECDSA') {
				// Load the ECDSA library.
				$this->CI->load->library('bitcoin_crypto');
				
				// Generate a new keypair.
				$key = $this->CI->bitcoin_crypto->getNewKeySet();
				
				// Send to the derived bitcoin address
				$send = $this->CI->bw_bitcoin->sendfrom($account, $key['pubAdd'], (float)$send_amount);
				if(!isset($send['code'])) {
					
					// Send the wallet to the admin user.
					$data['from'] = $admin['id'];
					$details = array('username' => $admin['user_name'],
									 'subject' => ucfirst($account)." Wallet Backup");
					$time = date("j F Y ga", time());
					$details['message'] = ucfirst($account)." Wallet Backup\n ------ $time\n\nPrivate Key: ".$key['privKey']." \nWIF Format: ".$key['privWIF']." \n\nAmount: BTC ".$send_amount." \nBitcoin Address: ".$key['pubAdd']." \nTransaction ID: ".$send." \n";
					// If the user has GPG, encrypt the message.
					if( isset($admin['pgp']) ) {
						$this->CI->load->library('gpg');			
						$details['message'] = $this->CI->gpg->encrypt($admin['pgp']['fingerprint'], $details['message']);
					}
					// Prepare the input.
					$message = $this->CI->bw_messages->prepare_input($data, $details);
					if($this->CI->messages_model->send($message) !== TRUE) {
						$success = FALSE; 
					}
					
				} else {
					$success = FALSE;
				}
			} else if($this->CI->bw_config->balance_backup_method == 'Electrum') {
				// Use electrum backup method. 
				$this->CI->load->library('Mpkgen');
				if(!isset($this->CI->bw_config->electrum_mpk))
					return FALSE;

				// Load the MPK
				$mpk = trim($this->CI->bw_config->electrum_mpk);

				// If the iteration record doesn't exist, create one at 0.
				if(!isset($this->CI->bw_config->electrum_iteration)) {
					$iteration = 0;
					if(!$this->CI->config_model->create('electrum_iteration', '0')) {
						// Exit if we can't create the record.
						continue;
					}
				} else {
					// Record exists, use that.
					$iteration = $this->CI->bw_config->electrum_iteration;
				}
				// Generate the address from the MPK and iteration.
				$address = $this->CI->mpkgen->address($mpk, $iteration);
				if(!is_string($address))
					return FALSE;

				// All checks are done. Send the coins.
				$send = $this->CI->bw_bitcoin->sendfrom($account, $address, (float)$send_amount);
				if(!isset($send['code'])) {
					$this->CI->config_model->update(array('electrum_iteration' => $iteration+1));
					
					// Send the wallet to the admin user.
					$data['from'] = $admin['id'];
					$details = array('username' => $admin['user_name'],
									 'subject' => ucfirst($account)." Wallet Backup");
					$time = date("j F Y ga", time());
					$details['message'] = ucfirst($account)." Wallet Backup\n ------ $time\nAmount: BTC ".$send_amount." \nBitcoin Address: ".$address." \nElectrum Index: ".$iteration." \nTransaction ID: ".$send." \n";
					
					// If the user has GPG, encrypt the message.
					if( isset($admin['pgp']) ) {
						$this->CI->load->library('gpg');			
						$details['message'] = $this->CI->gpg->encrypt($admin['pgp']['fingerprint'], $details['message']);
					}
					// Prepare the input.
					$message = $this->CI->bw_messages->prepare_input($data, $details);
					if($this->CI->messages_model->send($message) !== TRUE) {
						$success = FALSE; 
					}
				} else {
					$success = FALSE;
				}
			}
		}
		return $success;
	}
};
