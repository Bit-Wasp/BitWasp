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
							'interval' => '24',
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
	}
	
};
