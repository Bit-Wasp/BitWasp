<?php

class Price_Index {

	public $config = array(	'name' => 'Bitcoin Price Index',
							'description' => 'An autorun job to update the Bitcoin exchange rates.',
							'index' => 'price_index',
							'interval' => '10',
							'interval_type' => 'minutes');
	public $CI;
	
	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->library('bw_bitcoin');
	}
	
	public function job() {
		$this->CI->bw_bitcoin->ratenotify();
		$this->CI->autorun_model->set_updated('price_index');
	}
	
};
