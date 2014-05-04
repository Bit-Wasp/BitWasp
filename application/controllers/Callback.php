<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Callback Controller
 *
 * This controller is used for internal callbacks and requests, such
 * as the bitcoin daemon and cronjob's. 
 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Callback
 * @author		BitWasp
 * 
 */
class Callback extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct() {
		parent::__construct();
		
		// Prevent access via web. Uncomment when enough people have changed.
		//if(!$this->input->is_cli_request())
			//die("Not Allowed");
	}
	
	/**
	 * Block function - Inform site about a new block.
	 * URI: /callback/block/$block_hash
	 * 
	 * @access	public
	 * @see		Models/Bitcoin_Model	 
	 * @see		Libraries/Bw_Bitcoin
	 */
	public function block($block_hash = NULL) {
		// Abort if no block hash is supplied.
		if($block_hash == NULL)
			return FALSE;
			
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');
		$this->load->model('transaction_cache_model');

		// Die if bitcoind is actually offline.
		if($this->bw_bitcoin->getinfo() == NULL) {
			return FALSE;
		}
		// Reject already known blocks.
		if($this->transaction_cache_model->check_block_seen($block_hash) == TRUE)
			return FALSE;
		$block = $this->bw_bitcoin->getblock($block_hash);
		
		if(!is_array($block))
			return FALSE;
		
		$watched_addresses = $this->bitcoin_model->watch_address_list();
		if(count($watched_addresses) == 0)
			return FALSE;
		
		$txs = array();
		foreach($block['tx'] as $id => $tx_id) {
			array_push($txs, array(	'tx_id' => $tx_id,
									'tx_raw' => $this->bw_bitcoin->getrawtransaction($tx_id),
									'block_height' => $block['height']));
		}
		
		if(count($txs) > 0)
			$this->transaction_cache_model->add_cache_list($txs);
	}	
	
	/**
	 * Process
	 */
	public function process() {

		// Die if the callback is already running
		if($this->bw_config->bitcoin_callback_running == 'true') {
			// Hack to get the script running again if it's been running for over 10 minutes.
			if((time()-$this->bw_config->bitcoin_callback_starttime) > 2*60) {
				echo "Reset callback running\n";
				$this->config_model->update(array('bitcoin_callback_running' => 'false'));
			} else {
				echo "Fail, as still running\n";
				// If not over 10 minutes, it might still be working, so just do nothing.
				return FALSE;
			}
		}

		$this->load->model('transaction_cache_model');

		// Load the cached transactions to process. Die if nothing to do.
		$list = $this->transaction_cache_model->cache_list();
		if($list == FALSE || count($list) == 0 ) {
			return FALSE;
		}

		$this->load->library('Raw_transaction');
		$this->load->model('order_model');
		$this->load->model('bitcoin_model');

		// No problems, so prevent other instances from running!
		$this->config_model->update(array(	'bitcoin_callback_running' => 'true',
											'bitcoin_callback_starttime' => time()));

		// Load watched addresses, and payments received on addresses.
		$watched_addresses = $this->bitcoin_model->watch_address_list();
		var_dump($watched_addresses);
		$payments_list = $this->transaction_cache_model->payments_list('order');
		// Try to scrape payments to and from our multisig addresses.
		$order_finalized = array();
		$received_payments = array();
		$fee_payments = array();

		foreach($list as $cached_tx) {
			// Raw_transaction library is way faster than asking bitcoind.
			$tx = Raw_transaction::decode($cached_tx['tx_raw']);

			if(	count($tx['vin']) > 0 && $payments_list !== FALSE) {
				$spending_transactions = $this->transaction_cache_model->check_inputs_against_payments($tx['vin'], $payments_list);
				if(count($spending_transactions) > 0) {
					foreach($spending_transactions as $tmp) {
						$check = $this->transaction_cache_model->check_if_expected_spend($tx['vout']);
						// Put transaction into scam or successful array.
						$order_finalized[] = array(	'final_id' => $cached_tx['tx_id'], 
													'address' => $tmp['assoc_address'],
													'valid' => (($check == FALSE) ? FALSE : TRUE));
					}
				}
			}

			if( count($tx['vout']) > 0 ) {
				$output_list = $this->transaction_cache_model->parse_outputs_into_array($cached_tx['tx_id'], $cached_tx['block_height'], $tx['vout']);
				foreach($output_list as $tmp) {
					// Someone is paying money to a watched address. Record the transaction.
					if( in_array($tmp['address'], $watched_addresses['addresses']) == TRUE) {
						$tmp['purpose'] = $watched_addresses['data'][$tmp['address']]['purpose'];
						$received_payments[] = $tmp;
 					}
				}
			}
			$delete_cache[] = array('tx_id' => $cached_tx['tx_id']);
		}

		// Log all incoming payments.
		if(count($received_payments) > 0) {
			echo "Handling ".count($received_payments)." received_payments\n";
			$this->transaction_cache_model->add_payments_list($received_payments);
		}

		// Log all outgoing payments: orders being finalized.
		if(count($order_finalized) > 0){
			echo "Handling ".count($order_finalized)." order_finalized\n";
			$this->order_model->order_finalized_callback($order_finalized);
		}
		// Delete payments from the block cache.
		if(count($delete_cache) > 0)
			$this->transaction_cache_model->delete_cache_list($delete_cache);

		// This could be made into an autorun job:
		$this->order_model->order_paid_callback();

		$this->config_model->update(array('bitcoin_callback_running' => 'false'));
	} 
	
	/**
	 * Alert
	 * 
	 * This callback is used by the bitcoin daemon to inform the site
	 * of an alert, and to put it into maintenance mode. The alert
	 * message is stored in the log for the admin to see.
	 * 
	 */
	public function alert() {
		$this->load->library('bw_bitcoin');		
		
		// Load the current, if any, bitcoin alert.
		$alert = $this->bw_bitcoin->check_alert();
		if($alert !== FALSE) {
			$this->load->model('alerts_model');

			// If the site has never responded to this error before, proceed:
			if($this->alerts_model->check($alert['message']) == FALSE) {
				// If there is an alert, log the alert message for the admin.
				$this->load->model('admin_model');
				$this->logs_model->add('Bitcoin Alert', 'Bitcoin Alert', $alert['message'], 'Alert');
				
				// Record the alert
				$this->alerts_model->add($alert);
				
				// If the site is not already in maintenance mode, go into that now.
				if($this->bw_config->maintenance_mode == FALSE)
					$this->admin_model->set_mode('maintenance');
			}
		}
	}
	
	/**
	 * Autorun function 
	 * 
	 * This function loads the autorun library, which is loads the 
	 * jobs, checks the run intervals, to determine which jobs need
	 * to be run. This is called by a cronjob.
	 * URI: /callback/autorun
	 * 
	 * @access	public
	 * @see		Libraries/Autorun
	 */
	public function autorun() {
		$this->load->library('autorun');
	}
};

/* End of file Image.php */
