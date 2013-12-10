<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Delete Transaction History Job
 *
 * This job is used to delete old and unneeded transaction records. Called 
 * by the autorun library. It is run periodically (default is to run every 
 * 24 hours)
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	Delete Transaction History
 * @author		BitWasp
 */
class Delete_Transaction_History {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Delete Transaction History',
							'description' => 'Clear transactions older than a defined age.',
							'index' => 'transaction_history',
							'interval' => '0',
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
	 * We load the oldest age of transactions from the config library. If 
	 * set to zero, we don't need to run the job and execution terminates.
	 * Otherwise, we delete all transactions made before the specified
	 * time.
	 * 
	 */
	public function job() {
		$interval = $this->CI->bw_config->delete_transactions_after*24*60*60;
		
		if($interval == 0)		// Check if this feature is disabled.
			return FALSE;
			
		$time = (time()-$interval);
		$old_transactions = $this->CI->general_model->rows_before_time('pending_txns', $time);
		
		if($old_transactions == FALSE)	// No work to be done. Abort.
			return TRUE;
			
		$result = TRUE;
		foreach($old_transactions as $transaction) {
			if($this->CI->general_model->drop_id('pending_txns', $transaction['id']) == FALSE){
				$result = FALSE;
			}
		}
		
		@$this->logs_model->add("Clear Transactions", "Cleared old transacations", count($old_transactions)." transactions older than ".$this->CI->bw_config->delete_transactions_after." days have been deleted.", "Info");
						
		return $result;		
		
	}
	
};
