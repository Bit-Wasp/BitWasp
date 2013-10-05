<?php

// 0b489baf8746b3633dded74d5540d815fc250550b47ff9d853d898da9142edb0
// 4d5d434f4ddd5e6460cb23827524bee8303c9ce63b5160457eb3132edc8b105b

class Bitcoin_Test extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		
	}
	
	// block 00000000004f2378f62f747cd58e7e7b23794f36fca70507c75b52bf569a9d02
	// txn 32ba34ff38365fb8977611a1cbe9fad60732c28e69ef2eebfb18dd2bdd38d5f7
	public function sendtome(){
		$to = $this->bw_bitcoin->new_main_address();
		$value = 0.11158281;
		$from = "topup";
//		$a = $this->bw_bitcoin->move("topup", "main", $value);
		//$a = $this->bw_bitcoin->sendfrom($from, $to, $value);
		$to_address = $this->bw_bitcoin->new_main_address();
		$send = $this->bw_bitcoin->sendfrom("topup", $to_address, 0.00113456/2);
		var_dump($send);
	}
	
	public function transaction($transaction) {
	
	//	$this->bitcoin->getinfo();
		
		//$this->bitcoin->received_by_address();
		echo '<pre>';
			$transaction = $this->bw_bitcoin->gettransaction($transaction);
			$transaction['time_f'] = $this->general->format_time($transaction['time']);
			print_r($transaction);
//		 print_r($this->bw_bitcoin->listaccounts());
		//print_r($this->bitcoin->CI->jsonrpcclient->listreceivedbyaddress(0, TRUE));
		echo '</pre><br />';
	}
	
	public function get_block($block){
		echo '<pre>';
		$info = $this->bw_bitcoin->getblock($block);
		$info['time_f'] = $this->general->format_time($info['time']);
		print_r($info);
		echo '</pre>';
	}
	
	public function current_balance() {
		$bal = $this->bw_bitcoin->current_balance();
		print_r($bal);
	}
	
};
