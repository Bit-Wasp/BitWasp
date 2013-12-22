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

	public function gavin(){
		$key1="0491bba2510912a5bd37da1fb5b1673010e43d2c6d812c514e91bfa9f2eb129e1c183329db55bd868e209aac2fbc02cb33d98fe74bf23f0c235d6126b1d8334f86";
		$key2="04865c40293a680cb9c020e7b1e106d8c1916d3cef99aa431a56d253e69256dac09ef122b1a986818a7cb624532f062c1d1f8722084861c5c3291ccffef4ec6874";
		$key3="048d2455d2403e08708fc1f556002f1b6cd83f992d085097f9974ab08a28838f07896fbab08f39495e15fa6fad6edbfb1e754e35fa1c7844c41f322a1863d46213";
		
		$multisig = $this->bw_bitcoin->createmultisig(2, array($key1, $key2, $key3));
		
		echo 'Address: '.$multisig['address'].'<br />';
		echo 'Redeem Script: '.$multisig['redeemScript'].'<br /><b>Now please pay into the address</b><br />';
		
		

	}

	public function asdf(){
		$this->load->library('bitcoin_crypto');
		$key1 = $this->bitcoin_crypto->getNewKeySet(); echo $key1['privWIF'].' - '.$key1['pubKey'].'<br />';
		$key2 = $this->bitcoin_crypto->getNewKeySet();echo $key2['privWIF'].' - '.$key2['pubKey'].'<br />';
		$key3 = $this->bitcoin_crypto->getNewKeySet();echo $key3['privWIF'].' - '.$key3['pubKey'].'<br />';
		echo '<br />';
		$n = 2;
		$public_keys = array($key1['pubKey'],$key2['pubKey'],$key3['pubKey']);
		print_r(json_encode($public_keys));echo '<Br />';
		print_r($this->bw_bitcoin->addmultisigaddress($n, $public_keys));
								
	}

	public function txn(){
		$this->load->library('bw_transaction');
		
		$this->bw_transaction->add_input('e7717cdadb48dab9429f024fa6fa30853c79b3139b5a466b0fe62c8838e61b7b',0);
		$this->bw_transaction->add_output('mfeFGjmKuHunyEBuBvA9Ja95LBEW4qz4Ut',0.298);

		$transaction = $this->bw_transaction->print_json();	
		
		echo "Transaction<br />\n";
		echo $transaction;
		echo '<hr />';
		
		$transaction = $this->bw_transaction->generate();
		$raw_transaction = $this->bw_bitcoin->createrawtransaction($transaction);
		echo "<br /> \n$raw_transaction \n<br />";
		//$raw_transaction = $this->bw_bitcoin->createrawtransaction($transaction);
		//echo 'Raw Transaction<Br />'.$raw_transaction.'<br />';
		//$signed_transaction = $this->bw_bitcoin->signrawtransaction($raw_transaction);
		//$send = $this->bw_bitcoin->sendrawtransaction($signed_transaction['hex']);
		
	}

	
};







