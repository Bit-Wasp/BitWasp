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
	}
	
	/**
	 * Wallet function - Inform site about transactions affecting
	 * the wallet.
	 * URI: /callback/wallet/$txn_id
	 *
	 * @access	public
	 * @see		Models/Bitcoin_Model
	 * @see		Libraries/Bw_Bitcoin
	 */	
	public function wallet($txn_id = NULL){
		// Abort if no transaction ID is supplied.
		if($txn_id == NULL)
			return FALSE;
			
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');	
		
		$this->bw_bitcoin->walletnotify($txn_id);
	}
	
	/**
	 * Block function - Inform site about a new block.
	 * Also important for updating confirmations of transactions.
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
		
		$this->bw_bitcoin->blocknotify($block_hash);
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
	public function autorun(){
		$this->load->library('autorun');
	}
};

/* End of file Image.php */
