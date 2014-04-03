<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bw_Transaction {

	/**
	 * CI
	 * 
	 * This variable contains an instance of the CodeIgniter framework
	 */
	public $CI;
	
	/**
	 * Inputs
	 * 
	 * This variable is used to contain inputs for a transaction (added by
	 * calling the add_input() function). Each key in the array contains 
	 * an input for the newly constructed transaction.
	 */
	protected $inputs		= array();
	
	/**
	 * Outputs
	 * 
	 * This variable is used to contain outputs for a transaction - added
	 * by calling the add_output() function. Each key in the array contains
	 * an output for the newly constructed transaction.
	 */
	protected $outputs		= array();
	
	/**
	 * Input Count
	 * 
	 * This variable keeps track of the amount of Bitcoin available to 
	 * spend in the supplied inputs. Each transaction that is added as
	 * an input is scanned using getrawtransaction for this information.
	 */
	protected $input_count	= 0.00000000;
	
	/**
	 * Output Count
	 * 
	 * This variable keeps track of the total bitcoins which are being
	 * sent in the outputs of the transaction.
	 */
	protected $output_count	= 0.00000000;
	
	/**
	 * Construct
	 * 
	 * This function preloads the CodeIgniter framework, and loads the
	 * bitcoin library, providing access to the bitcoin daemon JSON interface.
	 */
	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->library('bw_bitcoin');
	}
	
	/** 
	 * Add Input
	 * 
	 * This function takes a $transaction_id, and the specified $vout to
	 * create an input for a transaction. Extra parameters, like the 
	 * redeemScript can be supplied in the $extras array. The function
	 * will attempt to get the scriptPubKey from the previous transaction.
	 * The total amount of BTC specified in inputs is recorded in the
	 * objects $input_count variable.
	 * 
	 * @param	string	$transaction_id
	 * @param	int	$vout
	 * @param	array	$extras(opt)
	 * @return	void
	 */
	public function add_input($transaction_id, $vout, $extras = array()) {
		// Get the raw transaction from the network
		$transaction = $this->CI->bw_bitcoin->getrawtransaction($transaction_id);
		$decode = $this->CI->bw_bitcoin->decoderawtransaction($transaction);
		
		// Scan for the specified $vout and get the scriptPubKey and value.
		foreach($decode['vout'] as $output_v => $output) {
			if($output_v == $vout){
				$scriptPubKey = $output['scriptPubKey']['hex'];
				$value = $output['value'];
				break;
			}
		}
		
		// Add the value of the input to $input_count.
		$this->input_count += $value;
		$tmp =  array(	'txid' => $transaction_id,
						'vout' => (int)$vout,
						'scriptPubKey' => $scriptPubKey);
		// Add extra parameters.
		foreach($extras as $key => $extra){
			$tmp[$key] = $extra;
		}				
		
		// Add the transaction to the inputs array.
		$this->inputs[] = $tmp;
									
	}
	
	/**
	 * Add Output
	 * 
	 * This function takes the $address and $value of the output, and 
	 * records the value in $output_count, and the transaction output in $outputs.
	 * 
	 * @param	string	$address
	 * @param	int	$value
	 * @return	void
	 */
	public function add_output($address, $value) {
		$this->outputs[$address] = (float)$value;
		$this->output_count += $value;
	}
	
	/**
	 * Remaining Fee
	 * 
	 * Subtract the $output_count from the $input_count to obtain the
	 * fee to be paid for this transaction. 
	 * 
	 * @return	int
	 */
	public function remaining_fee() {
		return $this->input_count-$this->output_count;
	}
	
	/**
	 * Generate
	 * 
	 * This function returns the inputs and outputs for the transaction 
	 * as an array. This is suitable for use with the jsonRPCclient to
	 * create the transaction using the bitcoin daemon. 
	 * 
	 * @return	array
	 */
	public function generate() {
		$output = array('inputs' => $this->inputs,
						'outputs' =>$this->outputs);
		$this->inputs = array();
		$this->outputs = array();
		$this->input_count = 0.00000000;
		$this->output_count = 0.00000000;
		return $output;
	}

	/**
	 * Print JSON
	 * 
	 * This function can be run before generate() is called. It will return
	 * a string which can be used to manually create the raw transaction
	 * in the bitcoin daemon.
	 * 
	 * @return	string
	 */
	public function print_json() {
		return "'".json_encode($this->inputs)."' '".json_encode($this->outputs)."'";
	}
};

/* End of File: Transaction.php */
