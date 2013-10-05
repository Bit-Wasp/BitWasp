<?php

class Btc_internal extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
	} 
	
	// Get the current block, signalling a checkpoint to scan from.
	public function install_config() {
		$bitcoin_info = $this->bw_bitcoin->getinfo();
		$latest_hash = $this->bw_bitcoin->getblockhash($bitcoin_info['blocks']);
		//$this->bitcoin_model->add_block($latest_hash, $bitcoin_info['blocks']);
	}
	
	public function walletnotify($txn_id){
		$this->bw_bitcoin->walletnotify($txn_id);
	}
	
	public function blocknotify($txn_id){
		$this->bw_bitcoin->blocknotify($txn_id);
	}

	public function ratenotify(){
		$this->bw_bitcoin->ratenotify();
	}

	public function reset(){
		$this->bitcoin_model->reset_a();
	}
};
