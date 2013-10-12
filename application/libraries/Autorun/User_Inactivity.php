<?php

class User_Inactivity {

	public $config = array(	'name' => 'Ban User: Inactivity',
							'description' => 'An autorun job to ban inactive users.',
							'index' => 'user_inactivity',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	public function job() {
		
	}
	
};
