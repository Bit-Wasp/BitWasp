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
	public function wallet($txn_id){
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
	public function block($block_hash){
		$this->load->library('bw_bitcoin');
		$this->load->model('bitcoin_model');	
		
		$this->bw_bitcoin->blocknotify($block_hash);
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
