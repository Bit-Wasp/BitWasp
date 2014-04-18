<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Transaction Cache Model
 *
 * This class handles the queries for handling cached transaction id's.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Transaction Cache
 * @author		BitWasp
 * 
 */
class Transaction_cache_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	public function __construct() {
		parent::__construct();
	}

	//////////////////////////////////////////////////////////////////
	// Cached lists of transactions from blocks.
	//////////////////////////////////////////////////////////////////
	
	/**
	 * Add Cache List
	 * 
	 * Supply an array, containing child-arrays containing information 
	 * about a transaction. 
	 * 
	 * @param	array	$array
	 * @return	boolean
	 */
	public function add_cache_list($array) {
		return ($this->db->insert_batch('transactions_block_cache', $array) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Delete List
	 * 
	 * Deletes a list of cached transaction id's as specified by the
	 * tx_id key in each child-array. 
	 * 
	 * @param	array
	 * @return	boolean
	 */
	public function delete_cache_list($array) {
		$i = 0;
		foreach($array as $tx) {
			($i++ == 0) ? $this->db->where('tx_id', $tx['tx_id']) : $this->db->or_where('tx_id',$tx['tx_id']);
		}
		return $this->db->delete('transactions_block_cache');
	}

	/**
	 * Cache List
	 *
	 * This function loads the entire list of cached block transactions.
	 *
	 * @return	array
	 */
	public function cache_list() {
		$this->db->limit(2000);
		$query = $this->db->get('transactions_block_cache');
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE ;
	}

	//////////////////////////////////////////////////////////////////
	// Stored payments obtained through scraping the blockchain
	//////////////////////////////////////////////////////////////////

	/**
	 * Add Payments List
	 *
	 * Record an array containing payments on addresses we are watching.
	 *
	 * @param	array	$tx_array
	 * @return	boolean
	 */
	public function add_payments_list($tx_array) {
		$this->load->model('order_model');
		$this->load->model('users_model');
		$stripped = array();
		$final = array();

		foreach($tx_array as $payment) {
			// Check that we have not encountered this before.
			if($this->check_already_have_payment($payment['tx_id'], $payment['vout']) == FALSE) {
				// Treat order and fee payments differently!
				if($payment['purpose'] == 'order') {
					$order = $this->order_model->get_order_by_address($payment['address']);
					$payment['order_id'] = $order['id'];

					$total_payment = $payment['value']*1e8;
					$order_payments = $this->payments_to_address($payment['address']);
					foreach($order_payments as $record) {
						$total_payment += $record['value']*1e8;
					}
					$order_total = ($order['price']+$order['shipping_costs']+$order['fees'])*1e8;

					if($order['finalized'] == '0' && $order_total-$total_payment <= 0) {
						$this->record_paid_order($order['id']);
					}
				}

				if($payment['purpose'] == 'fees') {
					$user_hash = $this->users_model->get_payment_address_owner($payment['address']);
					// Set user hash for payment records.
					$payment['fees_user_hash'] = $user_hash;
					$entry_payment = $this->users_model->get_entry_payment($user_hash);

					if($entry_payment !== FALSE) {
						// Get current value, and add the previous ones.
						$paid_amount = $payment['value']*1e8;
						$fee_payments = $this->payments_to_address($payment['address']);
						foreach($fee_payments as $record) {
							$paid_amount += $record['value']*1e8;
						}
						// If the paid amount is correct, let the user log in.
						if($paid_amount >= $entry_payment['amount']*1e8) {
							$this->users_model->delete_entry_payment($user_hash);
							$this->users_model->set_entry_paid($user_hash);
						}
					}
				}
				$final[] = $payment;
			}
		}
		return (count($final) == 0 || $this->db->insert_batch('transactions_payments_cache', $final) == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Check Already Have Payment
	 * 
	 * This function accepts a $tx_id, and $vout, and checks if we have
	 * already recorded this input in the payments cache.
	 * 
	 * @param	string	$tx_id
	 * @param	int	$vout
	 * @return	boolean
	 */
	public function check_already_have_payment($tx_id, $vout) {
		$this->db->where('tx_id', $tx_id)
				 ->where('vout', $vout);
		$query = $this->db->get('transactions_payments_cache');
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Parse Outputs Into Array
	 * 
	 * This function accepts $outputs - the [vout] array from a decoded
	 * transaction. All vouts are converted into an individual row to
	 * be stored if it pays to an address we care about, once the $txid
	 * and $block_height are supplied.
	 * This is done when we are creating a list of outputs which are
	 * sent to our addresses.
	 * 
	 * @param	string	$txid
	 * @param	int		$block_height
	 * @param	array	$outputs
	 * @return	array
	 */
	public function parse_outputs_into_array($txid, $block_height,  $outputs) {
		$addrs = array();
		foreach($outputs as $v_out => $output) {
			
			// Only try to deal with what you can decode! 
			
			// Remove this if() if you want to get rid of the Raw_transaction::decode function.
			if(isset($output['scriptPubKey']['addresses'][0]))
				$addrs[] = array('address' => $output['scriptPubKey']['addresses'][0],
								'value' => $output['value'],
								'tx_id' => $txid,
								'vout' => $v_out,
								'block_height' => $block_height);
		}
		return $addrs;
	}

	/**
	 * Payments List
	 * 
	 * This function loads a list of all the transactions which send to
	 * addresses we care about. Returns an array containing two indexes:
	 *  - tx_ids contains an array where each entry is a txid,
	 *  - txs contains an array where each transaction is indexed by its txid.
	 * 
	 * @return	array
	 */
	public function payments_list($param = FALSE) {
		if($param !== FALSE)
			if(in_array($param, array('fees','order')))
				$this->db->where('purpose', $param);
		
		$query = $this->db->get('transactions_payments_cache');
		if($query->num_rows() > 0) {
			$txid_list = array();
			$tx_list = array();
			foreach($query->result_array() as $id => $tx) {
				$tx_list[$tx['tx_id']] = $tx;
				$txid_list[] = $tx['tx_id'];
			}
			return array(	'tx_ids' => $txid_list,
							'txs' => $tx_list);
		} else {
			return FALSE;
		}
	}

	/**
	 * Check Inputs Against Payments
	 * 
	 * Accepts $inputs, which is directly taken from a decoded transaction.
	 * $list is a the entire $this->payments_list(). Returns an array
	 * of transaction row's which spend from multisignature addresses.
	 * T
	 * 
	 * @param	array	$inputs
	 * @param	array	$list
	 * @return	array
	 */
	public function check_inputs_against_payments($inputs, $list) {
		$interesting = array();
		foreach($inputs as $input) {
			if( isset($input['txid'] )) {
				if( in_array($input['txid'], $list['tx_ids'] )) {
					if( $list['txs'][$input['txid']]['vout'] == $input['vout']) {
						$input['assoc_address'] = $list['txs'][$input['txid']]['address'];
						$interesting[] = $input;
					}
				}
			}
		}
		return $interesting;
	}

	/**
	 * Payments To Address
	 * 
	 * This function returns an array containing all inputs which we know
	 * about that pay to a particular address. Returns an empty array if
	 * no records exist.
	 * 
	 * @param	string	$address
	 * @return	array
	 */
	public function payments_to_address($address) {
		$this->db->where('address', $address);
		$query = $this->db->get('transactions_payments_cache');
		return $query->result_array();
	}
	
	
	////////////////////////////////////////////////////////////////
	// 'Complete Order' Trigger
	////////////////////////////////////////////////////////////////
	
	/**
	 * Record Paid Order
	 * 
	 * This is done in the add_payments_list() function. It adds a paid
	 * order to a cache in the database, where it can be processed later
	 * to have the unsigned transaction generated.
	 * 
	 * @param	int	$order_id
	 * @return	boolean
	 */
	public function record_paid_order($order_id) {
		return ($this->db->insert('paid_orders_cache', array('order_id' => $order_id)) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Delete Finalized Record
	 * 
	 * This function attempts to delete an order from the paid_orders_cache
	 * once it has been processed. Returns a boolean indicating the outcome.
	 * 
	 * @param	int	$order_id
	 * @return boolean
	 */
	public function delete_finalized_record($order_id) {
		$this->db->where('order_id', "$order_id");
		return ($this->db->delete('paid_orders_cache') == TRUE) ? TRUE : FALSE;
	}
	
	////////////////////////////////////////////////////////////////
	//	Log Functions - Used to see if transactions were expected or not.
	//  Prevent people cheating the site
	////////////////////////////////////////////////////////////////
	
	/**
	 * Outputs to Log Array
	 * 
	 * This function converts the outputs of a transaction to our log format.
	 * This is an array containing entries of the format: array('address' => '', 'value' => '').
	 * 
	 * This is hashed and stored in the database, to later confirm if
	 * an input was spent correctly.
	 * 
	 * @param	array	$outputs
	 * @return	boolean
	 */
	public function outputs_to_log_array($outputs) {
		$array = array();
		foreach($outputs as $vout => $output) {
			$array[] = array('address' => $output['scriptPubKey']['addresses'][0],
							'value' => $output['value']);
		}
		return $array;
	}
	
	/**
	 * Log Transaction
	 * 
	 * This function converts a generated, unsigned transactions outputs
	 * to a recognizable, and hopefuly reproducable hash whenever outgoing 
	 * payments are encountered in the future.
	 */
	public function log_transaction($outputs, $multisig_address, $order_id) {
		$outputs = $this->outputs_to_log_array($outputs);
		$outputs_hash = hash('sha256', json_encode($outputs));

		if($this->search_log_hashes($outputs_hash) !== FALSE)
			return FALSE;

		$insert = array('outputs_hash' => $outputs_hash,
						'address' => $multisig_address,
						'order_id' => $order_id);
		return ($this->db->insert('transactions_expected_cache', $insert) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Clear Expected For Address
	 * 
	 * Used to clear an expected outcome for inputs on a particular $address.
	 * This is done when the admin creates a new transaction when resolving
	 * a dispute. Returns a boolean indicating outcome.
	 * 
	 * @param	string	$address
	 * @return	boolean
	 */
	public function clear_expected_for_address($address) {
		$this->db->where('address', $address);
		return ($this->db->delete('transactions_expected_cache') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Search Log Hashes
	 * 
	 * This function searches for a raw outputs_hash, to see if it
	 * matches any on record.
	 * 
	 * @param	string	 $outputs_hash
	 * @return	ARRAY/FALSE
	 */
	public function search_log_hashes($outputs_hash) {
		$this->db->where('outputs_hash', $outputs_hash);
		$query = $this->db->get('transactions_expected_cache');
		return ($query->num_rows > 0) ? $query->row_array() : FALSE;
	}
	
	/**
	 * Check If Expected Spend
	 * 
	 * This function takes a transactions ['vout'] array and checks if
	 * this matches what we expected. Only done for order payments: 
	 * as the payments leaving these addresses would have been created
	 * by the site, and have explicitly agreed outputs, any deviation
	 * in the hash means the transaction has been tampered with.
	 * 
	 * Also done to verify partially signed transactions.
	 * 
	 * @param	array	$output
	 * @return	boolean
	 */
	public function check_if_expected_spend($output) {
		$outputs = $this->outputs_to_log_array($output);
		$outputs_hash = hash('sha256', json_encode($outputs));

		$search = $this->search_log_hashes($outputs_hash);
		return ($search == FALSE) ? FALSE : $search['address'];
	}

	//
	// Chain reorg functions
	//

	/**
	 * Check Block Seen
	 * 
	 * This function checks if the supplied block hash has been seen
	 * or not by returning a boolean.
	 * 
	 * @param	string	$block_hash
	 * @return	boolean
	 */
	public function check_block_seen($block_hash) {
		$this->db->where('hash', $block_hash);
		$this->db->from('blocks');
		return ($this->db->count_all_results() == 0) ? FALSE : TRUE;
	}
	
	public function check_block_height_set($height) {
		$this->db->where('height', $height);
		$this->db->from('blocks');
		return ($this->db->count_all_results() == 0) ? FALSE : TRUE ; 
	}
	
	public function add_seen_block($block_hash, $height, $prev_hash) {
		$insert = array('hash' => $block_hash,
						'height' => $height,
						'prev_hash' => $prev_hash);
		return ($this->db->insert('blocks', $insert) == TRUE) ? TRUE : FALSE;
	}

	public function last_added_block() {
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}
	
	public function block_info($info) {
		if(!isset($info['block']) && !isset($info['height']) )
			return FALSE;
			
		(isset($info['block'])) ? $this->db->where('block',$info['block']) : $this->db->where('height', $info['height']) ;
		
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}
};
