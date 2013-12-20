<?php

/**
 * Bitcoin Test Controller
 * 
 * This controller is used to test out some bitcoin functions before
 * adding them to the live code. Also has a blockexplorer feature.
 */
 
class Bitcoin_Test extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
	}
	
	public function transaction($transaction) {	
		echo '<pre>';
			$transaction = $this->bw_bitcoin->gettransaction($transaction);
			$transaction['time_f'] = $this->general->format_time($transaction['time']);
			print_r($transaction);
		echo '</pre><br />';
	}
	
	public function get_block($block) {
		 echo '<pre>';
		$info = $this->bw_bitcoin->getblock($block);
		$info['time_f'] = $this->general->format_time($info['time']);
		print_r($info);
		echo '</pre>';
	}

	public function asdf(){
		$this->load->library('bitcoin_crypto');
		$key1 = $this->bitcoin_crypto->getNewKeySet(); echo $key1['privWIF'].' - '.$key1['pubKey'].'<br />';
		$key2 = $this->bitcoin_crypto->getNewKeySet();echo $key2['privWIF'].' - '.$key2['pubKey'].'<br />';
		$key3 = $this->bitcoin_crypto->getNewKeySet();echo $key3['privWIF'].' - '.$key3['pubKey'].'<br />';
		
		$n = 2;
		$public_keys = array($key1['pubKey'],$key2['pubKey'],$key3['pubKey']);
		print_r($this->bw_bitcoin->addmultisigaddress($n, $public_keys));
								
	}

	public function txn(){
		$this->load->library('bw_transaction');
		
		$this->bw_transaction->add_input('26db814914b21e3e14243630a78ed7ebd0c058c51cdfc9c4239e4d19efd4c969',1);
		$this->bw_transaction->add_output('mmZ6i2xPjanqpWRhDREVjsmDgUw8TwjndH',0.02);
		$this->bw_transaction->add_output('mrTEhUdxn4Kv5KDpnjaTq4Zvtp8iz7Unqb',0.04);
		
		$transaction = $this->bw_transaction->generate();	
		$raw_transaction = $this->bw_bitcoin->createrawtransaction($transaction);
		$signed_transaction = $this->bw_bitcoin->signrawtransaction($raw_transaction);
		$send = $this->bw_bitcoin->sendrawtransaction($signed_transaction['hex']);
		
	}

	
};







