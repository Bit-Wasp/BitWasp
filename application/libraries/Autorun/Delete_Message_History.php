<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Delete Message History Job
 *
 * This job is used to delete old and unneeded messages. Called by the 
 * autorun library. It is run periodically (default is to run every 
 * 24 hours)
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	User Inactivity
 * @author		BitWasp
 */
class Delete_Message_History {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Delete Message History',
							'description' => 'Autorun job to remove messages older than a defined age.',
							'index' => 'delete_message_history',
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
		$interval = $this->CI->bw_config->delete_messages_after*24*60*60;
		
		if($interval == 0)		// Check if this feature is disabled.
			return FALSE;
			
		$time = (time()-$interval);
		$old_messages = $this->CI->general_model->rows_before_time('messages', $time);
		
		if($old_messages == FALSE)	// No work to be done. Abort.
			return TRUE;
			
		$result = TRUE;
		foreach($old_messages as $message) {
			if($this->CI->general_model->drop_id('messages', $message['id']) == FALSE)
				$result = FALSE;
		}
		
		@$this->logs_model->add("Delete Messages", "Deleted old messages", count($old_messages). " messages older than {$this->CI->bw_config->delete_messages_after} days have been deleted.", "Info");
						
		return $result;		
		
	}
	
};
