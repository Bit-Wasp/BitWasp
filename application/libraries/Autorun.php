<?php

class Autorun {

	public $CI; 
	public $config;
	
	public function __construct() { 
		$this->CI = &get_instance();
		
		$intervals = $this->CI->config_model->load_autorun_intervals();
		
		foreach($intervals as $interval) {
			
			if($interval['interval'] !== '0' && $interval['last_update'] <= (time()-($interval['interval']*60*60))) {
			
				if($interval['index'] == 'user_inactivity') {
					// Ban users who are logged out for longer than allowed (specified by number of days)
					$ban_inactivity_timeout = $this->CI->bw_config->ban_after_inactivity;
					
					if($ban_inactivity_timeout !== '0') {
						$time_threshold = (time()-$ban_inactivity_timeout*7*24*60*60);
				
						$users = $this->CI->general_model->get_stale_users($time_threshold);
						if($users !== FALSE) {
							$this->CI->load->model('accounts_model');
							foreach($users as $user) {
								$this->CI->accounts_model->toggle_ban($user['id'], '1');
							}
						}
					}
					$this->CI->config_model->set_autorun_updated('user_inactivity');
					continue;
				}
			
				// Scan for updates for the bitcoin exchange rates.
				if($interval['index'] == 'price_index') {
					$this->CI->load->library('bw_bitcoin');
					$this->CI->bw_bitcoin->ratenotify();
					$this->CI->config_model->set_autorun_updated('price_index');	
					continue;
				}
				
				// Delete transactions older than a specified amount of time.
				if($interval['index'] == 'transaction_history') {
					$rows = $this->CI->general_model->rows_before_time('pending_txns', (time()-($interval['interval']*60)));
					if($rows !== FALSE) {
						foreach($rows as $row) {
							$this->CI->general_model->drop_id('pending_txns', $row['id']);
						}
					}
					$this->CI->config_model->set_autorun_updated('transaction_history');
					continue;
				}
				
				// Delete messages older than a specified amount of time.
				if($interval['index'] == 'message_history') {
					$rows = $this->CI->general_model->rows_before_time('messages', (time()-($interval['interval']*60)));
					if($rows !== FALSE) {
						foreach($rows as $row) {
							$this->CI->general_model->drop_id('messages', $row['id']);
						}
					}
					$this->CI->config_model->set_autorun_updated('message_history');					
					continue;
				}
				
			}
			
		}
	}
	
};


