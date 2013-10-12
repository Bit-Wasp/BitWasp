<?php

class Delete_Message_History {

	public $config = array(	'name' => 'Delete Message History',
							'description' => 'Autorun job to remove messages older than a defined age.',
							'index' => 'delete_message_history',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	public function job() {
	}
	
};
