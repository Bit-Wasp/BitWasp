<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bw_transaction {

	public $CI;
	public $inputs		= array();
	public $outputs 	= array();
	
	public $input_count = 0.00000000;
	public $output_count = 0.00000000;
	
	public function __construct(){
		$this->CI = &get_instance();
		$this->CI->load->library('bw_bitcoin');
		
	}
	
	
	
	public function add_input($transaction_id, $vout){
		$transaction = $this->CI->bw_bitcoin->gettransaction($transaction_id);
		$this->input_count += $transaction['details'][0]['amount'];
		$this->inputs[] = array(	'txid' => $transaction_id,
									'vout' => (int)$vout);
	}
	
	public function add_output($address, $value){
		$this->outputs[$address] = (float)$value;
		$this->output_count += $value;
	}
	
	public function remaining_fee(){
		return $this->input_count-$this->output_count;
	}
	
	public function generate(){
		$output = array('inputs' => $this->inputs,
						'outputs' =>$this->outputs);
		$this->inputs = array();
		$this->outputs = array();
		$this->output_count = 0.00000000;
		return $output;
	}

};

