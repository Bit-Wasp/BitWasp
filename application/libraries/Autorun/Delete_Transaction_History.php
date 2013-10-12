<?php

class Delete_Transaction_History {

	public $config = array(	'name' => 'Delete Transaction History',
							'description' => 'Clear transactions older than a defined age.',
							'index' => 'transaction_history',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	public function job() {
	}
	
};
