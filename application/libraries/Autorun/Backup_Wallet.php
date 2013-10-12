<?php

class Backup_Wallet {

	public $config = array(	'name' => 'Backup Wallet',
							'description' => 'Job to back up excessive funds from the wallet.',
							'index' => 'backup_wallet',
							'interval' => '24',
							'interval_type' => 'hours');
	public $CI;
	
	public function __construct() {
		$this->CI = &get_instance();
	}
	
	public function job() {
	}
	
};
