<?php

/**
 * Refund Inactive Users
 *
 * This job is used to refund inactive users. Called by the autorun class,
 * it is run periodically to check for users who have not logged in for
 * a set period. 
 * The default is to run every 24 hours, where stale users will have their
 * balances refunded to their cashout address.
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	User Inactivity
 * @author		BitWasp
 */

class Refund_Inactive_Users {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Refund Inactive Users',
							'description' => 'An autorun job to refund inactive users balances.',
							'index' => 'refund_inactive_users',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	/**
	 * Constructor
	 * 
	 * Load's the CodeIgniter framework
	 */
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	/**
	 * Job
	 * 
	 * This function is called by the Autorun script. 
	 * We load the time users may be inactive for from the config library. If 
	 * set to zero, we don't need to run the job and execution terminates.
	 * Otherwise, load the inactive users, and refund their coins to their
	 * bitcoin cashout address if it's set.
	 * 
	 * @see		Models/Accounts_Model
	 * @see		Libraries/Bw_Bitcoin
	 */
	public function job() {
		$interval = $this->CI->bw_config->refund_after_inactivity*24*60*60;

		if($interval == 0)		// Check if this feature is disabled.
			return TRUE;		
		
		$time = (time()-$interval);
		$stale_users = $this->CI->general_model->get_stale_users($time);
		
		if($stale_users == FALSE)	// No work to be done. Abort.
			return TRUE;
			
		$this->CI->load->model('accounts_model');
		$this->CI->load->library('bw_bitcoin');
		$result = TRUE;
		foreach($stale_users as $user) {
			
			// If the users balance is > 0, and they have a valid cashout address, refund their bitcoins.
			if($user['bitcoin_balance'] > 0 && $this->CI->bw_bitcoin->validateaddress($user['bitcoin_cashout_address']) !== FALSE) {
					
				$send = $this->bw_bitcoin->cashout($data['cashout_address'], (float)$user['bitcoin_balance']);
				if(!isset($send['code'])) 
					$this->bw_bitcoin->walletnotify($send); // Add it immediately to prevent duplicates.
			}
		}
		return $result;
	}
	
};
