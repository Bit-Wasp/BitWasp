<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Bitcoin Library
 * 
 * This library is a socket for the JSON RPC interface. 
 * Configuration is loaded from ./application/config/bitcoin.php
 * The class contains functions for bitcoind and functions for 
 * bitcoind to callback in order to track information about new transactions.
 * Also contains a function to update exchange rates from the selected
 * provider.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Bitcoin
 * @author		BitWasp
 */
class Bw_bitcoin {
	
	public $CI;
	
	/**
	 * Config
	 * 
	 * This variable contains the bitcoin credentials for the JSON rpc
	 * interface. 
	 */
	public $config;
	
	/** 
	 * Testnet
	 * 
	 * Flag to tell the site if we are currently working in the testnet or
	 * the main bitcoin chain.
	 */
	public $testnet;
		
	/**
	 * Constructor
	 * 
	 * Load the bitcoin configuration using CodeIgniters config library.
	 * Load the jsonRPCclient library with the config, and the bitcoin 
	 * model
	 */
	public function __construct() {
		$this->CI = &get_instance();
		
		$this->CI->config->load('bitcoin', TRUE);
		$this->config = $this->CI->config->item('bitcoin');	
		
		$this->CI->load->library('jsonrpcclient', $this->config);
		$this->CI->load->model('bitcoin_model');
	}
	
	/**
	 * Get Exchange Rates
	 * 
	 * Load exchange rates from the defined BPI. Called by bw_bitcoin/ratenotify().
	 * 
	 * @return		array/FALSE
	 */
	public function get_exchange_rates(){
		$this->CI->load->library('bw_curl');
		
		$source = $this->CI->bw_config->bitcoin_rate_config();
		$source_name = $this->CI->bw_config->price_index;
		
		$json_result = $this->CI->bw_curl->get_request($source['url']);
		
		if($json_result == NULL)
			return FALSE;
			
		$array =  json_decode($json_result);
		if($array !== FALSE && $array !== NULL){
			$array->price_index = $source_name;
			return $array;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get Account
	 * 
	 * Function to query bitcoind, to see which account owns $address.
	 * Returns a string containing the account name if successful, or
	 * an array describing the error on failure.
	 * 
	 * @param		string	$address
	 * @return		string / array
	 */
	public function getaccount($address) {
		$account = $this->CI->jsonrpcclient->getaccount($address);
		return ($account !== NULL) ? $account : FALSE;
	}

	/**
	 * Get Account Address
	 * 
	 * This function asks bitcoind for an address for the specified 
	 * account. It will return the same address each time, until funds
	 * are received at that address. This is used only to display 
	 * addresses for the admin to top up, all other requests for addresses
	 * will provide brand new addresses. 
	 * 
	 * @param	string	$account_name
	 * @return	string/FALSE
	 */
	public function getaccountaddress($account_name) {
		$address = $this->CI->jsonrpcclient->getaccountaddress($account_name);
		return ($address !== NULL) ? $address : FALSE;
	}
	
	/**
	 * Get Balance
	 * 
	 * Function to query bitcoind, to get the balance of the account $account.
	 * Returns a float in each case, whether the account exists or not. 
	 * 
	 * @param		string	$account
	 * @return		float
	 */
	public function getbalance($account) {
		return $this->CI->jsonrpcclient->getbalance($account);
	}
	
	/**
	 * Get Block
	 * 
	 * Function to query bitcoind, to get information about a block ($block_hash)
	 * Returns an array containing the account name if successful, or
	 * an array describing the error on failure.
	 * 
	 * @param		string	$block_hash
	 * @return		array
	 */
	public function getblock($block_hash) {
		return $this->CI->jsonrpcclient->getblock($block_hash);
	}
	
	/**
	 * Get Block Hash
	 * 
	 * Function to query bitcoind, to get the block hash for a particular
	 * height.
	 * Returns a string containing the block hash if successful, or an 
	 * array describing the error on failure.
	 * 
	 * @param		string	$block_no
	 * @return		string / array
	 */	
	public function getblockhash($block_no) {
		return $this->CI->jsonrpcclient->getblockhash($block_no);
	}
		
	/**
	 * Get Info
	 * 
	 * Function to query bitcoind for general information, like version,
	 * block height, balance, difficulty, 
	 * 
	 * @param		string
	 * @return		string / array
	 */		
	public function getinfo() {
		return $this->CI->jsonrpcclient->getinfo();
	}
	
	/**
	 * Get Recevied By Address
	 * 
	 * Query bitcoind to get the balance this particular address has
	 * received.
	 */
	public function getreceivedbyaddress($address){
		return $this->CI->jsonrpcclient->getreceivedbyaddress($address);
	}
	
	/**
	 * Get Transaction
	 * 
	 * Function to query bitcoind for a transaction ($tx_id). The transaction
	 * must belong to this wallet in order read this information. Returns
	 * an array containing transaction information, or an error array
	 * on failure.
	 * 
	 * @param		string	$tx_hash
	 * @return		array
	 */		
	public function gettransaction($tx_hash) {
		return $this->CI->jsonrpcclient->gettransaction($tx_hash);
	}

	/**
	 * Import Private Key
	 * 
	 * Function to ask bitcoind to import the wallet import format private
	 * key.
	 *
	 * @param		string	$wif
	 * @param		string	$account
	 * @return		????
	 */
	public function importprivkey($wif, $account) {
		return $this->CI->jsonrpcclient->importprivkey("$wif", "$account", TRUE);
	}
	/**
	 * List Accounts
	 * 
	 * Function to query bitcoind for information about the accounts
	 * in the wallet. Displays transactions with 0 confirmations.
	 * 
	 * @param		string
	 * @return		array
	 */			
	public function listaccounts($confirmations = 6) {
		$tmp = (array)$this->CI->jsonrpcclient->listaccounts($confirmations);
		$res = array();
		foreach($tmp as $acc => $bal){
			if(!preg_match('/\s+/', $acc) && $acc !== '')
				$res[$acc] = (float)$bal;			
		}
		return $res;
	}

	/**
	 * Cashout
	 * 
	 * Function to ask bitcoind to send $amount to the bitcoin address 
	 * $to. Sends the funds from the "main" account. Returns a transaction
	 * id ($tx_id) if the transaction is successful, otherwise returns 
	 * an error on failure.
	 * 
	 * @param		string	$to
	 * @param		float	$amount
	 * @return		array
	 */		
	public function cashout($to, $amount) {
		return $this->CI->jsonrpcclient->sendfrom("main", $to, (float)$amount);
	}
	
	/**
	 * Move
	 * 
	 * Function to ask bitcoind to move funds from the $from account
	 * to the $to account. Will create the account if it doesn't exist.
	 * Already have error-checked this account name, we want it to exist 
	 * already. Does not broadcast a transaction to the bitcoin network.
	 * 
	 * @param		string	$from
	 * @param		string	$to
	 * @param		float	$amount
	 * @return		boolean
	 */			
	public function move($from, $to, $amount) {
		return $this->CI->jsonrpcclient->move($from, $to, (float)$amount);
	}
	
	/**
	 * Send From
	 * 
	 * Function to ask bitcoind to send $value BTC from any $src_ac, to 
	 * $to_address. The transaction must belong to this wallet in order 
	 * read this information. Returns a transaction id ($tx_id) if the 
	 * transaction is successful, otherwise returns an error on failure.
	 * 
	 * @param		string	$src_ac
	 * @param		string	$to_address
	 * @param		float	$value
	 * @return		bool
	 */			
	public function sendfrom($src_ac, $to_address, $value){
		return $this->CI->jsonrpcclient->sendfrom($src_ac, $to_address, (float)$value);
	}

	/**
	 * Validate Address
	 * 
	 * Function to validate a bitcoin address. Checks if there is a 
	 * base58 address, and other tests. Returns a boolean with the answer.
	 * 
	 * @param		string	$address
	 * @return		bool
	 */		
	public function validateaddress($address) {
		$valid = $this->CI->jsonrpcclient->validateaddress($address);
		return $valid['isvalid'];
	}

	/**
	 * New Address
	 * 
	 * Function to ask bitcoind to create a new address for the topup 
	 * account. Associates the address with the $user_hash. Sets the
	 * users current bitcoin address - this function also logs it to the
	 * users list of previously used addresses (in case the user ends up
	 * reusing addresses). Does not return the address, sets it in the DB
	 * to be read then.
	 * se
	 * @param		string	$user_hash
	 * @return		boolean
	 */			
	public function new_address($user_hash) {
		$address = $this->CI->jsonrpcclient->getnewaddress("topup");
		if(isset($address['code']) || $address == NULL)
			return FALSE;
			
		return ($this->CI->bitcoin_model->set_user_address($user_hash, $address)) ? $address : FALSE;
	}

	/**
	 * New "Main" Address
	 * 
	 * Function to ask bitcoind to create an address for the "main" account.
	 * Used to forward funds from the topup account to the main account.
	 * A new address is created for this every time.
	 * 
	 * @return		string/FALSE
	 */			
	public function new_main_address(){
		$address = $this->CI->jsonrpcclient->getnewaddress("main");
		return ($address == NULL || isset($address['code'])) ? FALSE : $address;
	}

	/**
	 * New "Fees" Address
	 * 
	 * Function to ask bitcoind to create an address for the "fees" account
	 * Used to accept payments for accounts, and for order fees.
	 *
	 * @return	string/FALSE
	 */
	public function new_fees_address(){
		$address = $this->CI->jsonrpcclient->getnewaddress("fees");
		return ($address == NULL || isset($address['code'])) ? FALSE : $address;
	}
	
	/**
	 * Wallet Notify
	 * 
	 * Function to notify the marketplace about any new transactions. 
	 * Checks if transaction is for our wallet, and if not, continues with
	 * execution. Processes send/receive information to see if there are 
	 * balances to credit/debit.
	 * 
	 * If a transaction is for paying a registration fee, check if the
	 * user has paid the required figure. Activate the account if the
	 * user has met the amount.
	 * Immediately deducts a users balance if the transaction is a user
	 * cashing out their account.
	 * If the user is topping up, we create a new address for them to 
	 * use next time.
	 * Cashout/Topup functions are not themselves in an if/else block, because
	 * a user may be putting in another users topup address to cashout.
	 * Don't know why they'd do this, but this way we do the accounting properly.
	 * 
	 * @param		string	$txn_hash
	 */			
	public function walletnotify($txn_hash) {

		$transaction = $this->gettransaction($txn_hash);
		// Abort if there's an error obtaining the transaction (not for our wallet)
		if(isset($transaction['code']) || $transaction == NULL)
			return FALSE;

		// Extract details for send/receive from inputs and oupputs.
		$send = array();
		$receive = array();
		foreach($transaction['details'] as $detail) {
			if($detail['category'] == 'send')
				array_push($send, $detail);
			if($detail['category'] == 'receive')
				array_push($receive, $detail);
		}

		// Work out if the transaction is for a registration payment.
		if(isset($receive[0]) && $receive[0]['account'] == 'fees' && $receive[0]['category'] == "receive") {
			$this->CI->load->model('users_model');
			$address = $receive[0]['address'];
			
			$user_hash = $this->CI->users_model->get_payment_address_owner($address);
			if($user_hash !== FALSE) {
				// Add payment.
				$update = array('txn_id' => "Fee's Payment",
								'user_hash' => $user_hash,
								'value' => $receive[0]['amount'],
								'confirmations' => $transaction['confirmations'],
								'address' => $receive[0]['address'],
								'category' => 'receive',
								'credited' => '0',
								'time' => $transaction['time']);
				
				// If we don't have the payment, add it.
				if($this->CI->bitcoin_model->user_transaction($user_hash, $txn_hash, $update['category']) == FALSE) {
					$this->CI->bitcoin_model->add_pending_txn($update);
				
				}
			}
		}
		
		// Work out if the transaction is cashing out anything.
		if(isset($send[0]) && $send[0]['account'] == "main" && $send[0]['category'] == "send") {
			$user_hash = $this->CI->bitcoin_model->get_cashout_address_owner($send[0]['address']);
			if($user_hash !== FALSE) {
				$update = array('txn_id' => $txn_hash,
								'user_hash' => $user_hash,
								'value' => $send[0]['amount'],
								'confirmations' => $transaction['confirmations'],
								'address' => $send[0]['address'],
								'category' => 'send',
								'credited' => '1',
								'time' => $transaction['time']);

				// If we do not already have this transaction, add it, and deduct the users balance.
				if($this->CI->bitcoin_model->user_transaction($user_hash, $txn_hash, $update['category']) == FALSE) {

					$this->CI->bitcoin_model->add_pending_txn($update);
					
					// Immediately deduct a users balance if cashing out.
					$debit = array( 'user_hash' => $user_hash,
									'value' => $update['value']);
					$this->CI->bitcoin_model->update_credits(array($debit));
				}
			}
		}
		
		// Workout if the transaction is topping an account up.
		if(isset($receive[0]) && $receive[0]['account'] == 'topup' && $receive[0]['category'] == "receive") {
			$user_hash = $this->CI->bitcoin_model->get_address_owner($receive[0]['address']);
			if($user_hash !== FALSE) {
			
				$update = array('txn_id' => $txn_hash,
								'user_hash' => $user_hash,
								'value' => $receive[0]['amount'],
								'confirmations' => $transaction['confirmations'],
								'address' => $receive[0]['address'],
								'category' => 'receive',
								'time' => $transaction['time']);

				// If we do not already have the transaction, add it, and generate a new address if needed.
				if($this->CI->bitcoin_model->user_transaction($user_hash, $txn_hash, $update['category']) == FALSE) {
					$this->CI->bitcoin_model->add_pending_txn($update);

					// If the users current topup equals the one used to top up, generate a new one.
					if($this->CI->bitcoin_model->get_user_address($user_hash) == $update['address']) 
						@$this->new_address($user_hash);					
				}
			}
		}
	}
	
	/**
	 * Block Notify
	 * 
	 * Function to inform the marketplace about new blocks. Upon receiving
	 * a new block, we get the list of all pending transactions (uncredited
	 * and have confirmations < 50) and check how many confirmations they
	 * have now that we are looking at a new leading block. By refreshing
	 * the number of confirmations every time we should be able to deal 
	 * with a reorg if we were on the wrong fork.
	 * 
	 * If the transaction is a topup, and confirmations has reached 7,
	 * then we set the transaction as credited, and move the funds from
	 * the topup account (For unconfirmed transactions) to the main account.
	 * 
	 * If the number of confirmations for a transaction is >50, then we stop
	 * tracking this transaction, by setting confirmations='>50'. This
	 * causes the 'check' icon to appear in the transaction log.
	 * 
	 * @param		string	$block_hash
	 * @return		void
	 */		
	public function blocknotify($block_hash){
		$this->CI->load->model('users_model');
		$this->CI->load->model('logs_model');
					
		// First task, maintain a record of the processed blocks.
		if($this->CI->bitcoin_model->have_block($block_hash) == FALSE) {
			$block = $this->getblock($block_hash);
			
			if($block == NULL)
				return FALSE;
			
			if(!isset($block['code']) && isset($block['height']))
				$this->CI->bitcoin_model->add_block($block_hash, $block['height']);
		}
		
		// Load all pending transactions, and abort if there's none.
		$pending = $this->CI->bitcoin_model->get_pending_txns();
		if($pending == FALSE)
			return FALSE;
			
		// Prepare to build arrays of any transactions who need to be credited or have their confirmations changed.
		$credits = array();
		$confirmations = array();
		
		// Loop through pending transactions.
		foreach($pending as $txn){
			$transaction = $this->gettransaction($txn['txn_id']);

			// Probably don't need this check as it will have been done before,
			// but do it just in case.
			if(!isset($transaction['code'])) {
				$array = array('txn_id' => $txn['txn_id'],
							   'user_hash' => $txn['user_hash'],
							   'confirmations' => $transaction['confirmations'],
							   'category' => $txn['category'],
							   'value' => $txn['value'] );	// Re-cast as float, avoids error code.
				
				// If the transaction is to do with fee's, then check if the number of transactions exceeds one.
				// If the entry_payment still exists, and the confirmed balance >= the amount, then delete the 
				// record and activate the account.
				if($txn['category'] == 'receive' && $transaction['details'][0]['account'] == 'fees' && $transaction['confirmations'] >1 ){
					$user_hash = $this->CI->users_model->get_payment_address_owner($txn['address']);
					$entry = $this->CI->users_model->get_entry_payment($user_hash);
			
					if($entry !== FALSE){	
						$addr_balance = $this->getreceivedbyaddress($txn['address']);

						if($addr_balance >= $entry['amount'])
							if($this->CI->users_model->delete_entry_payment($user_hash))
								$this->CI->users_model->set_entry_paid($user_hash);
					}
				}
				
				// Try to credit the balance to a users account if the topup transaction has reached 7 confirmations.
				if($txn['category'] == 'receive' && $transaction['details'][0]['account'] == 'main' && $txn['credited'] == '0' && $array['confirmations'] > 6){
					array_push($credits, $array);
					$this->CI->bitcoin_model->set_credited($txn['txn_id']);
					
					$to_address = $this->new_main_address();
					$send = $this->sendfrom("topup", $to_address, (float)$array['value']);
					if(isset($send['code'])) {
						$accounts = $this->listaccounts();
						$this->CI->logs_model->add('Block Notify Callback', "Error moving funds from 'topup' account", "Current Balance: BTC {$accounts['topup']}\nMove some funds into the topup address to cover transaction fee's.", "Severe.");
					}	
				}
				
				// If the transaction has more than 50 confirmations, stop tracking it.
				if($array['confirmations'] > 50)
					$array['confirmations'] = '>50';
					
				// If the number of confirmations has changed, update it.
				if($txn['confirmations'] !== $transaction['confirmations'])
					array_push($confirmations, $array);
			}
		}
	
		// Update confirmations/balances in the database.
		$this->CI->bitcoin_model->update_confirmations($confirmations);
		$this->CI->bitcoin_model->update_credits($credits);
	}
	
	/**
	 * Rate Notify
	 * 
	 * Function to query the selected bitcoin price index provider
	 * for the latest exchange rates between USD/GBP/EUR.
	 * 
	 * @return		boolean
	 */		
	public function ratenotify() {
		$this->CI->load->model('currencies_model');
		// Abort if price indexing is disabled.
		if($this->CI->bw_config->price_index == 'Disabled')
			return TRUE;
	
		// Function to get the exchange rates via an API.
		$rates = $this->get_exchange_rates();

		if($rates == FALSE){
			$this->CI->logs_model->add('Price Index', 'Unable to fetch exchange rates', 'An attempt to update the Bitcoin Exchange rates failed. Please review your ./application/config/bitcoin_index.php file for any errors, or that the proxy is correctly configured','Error');
			return TRUE;
		}

		// Parse results depending on where they're from.
		if($this->CI->bw_config->price_index == 'CoinDesk') {
			$update = array('time' => strtotime($rates->time->updated),
							'usd' => $rates->bpi->USD->rate,
							'gbp' => $rates->bpi->GBP->rate,
							'eur' => $rates->bpi->EUR->rate,
							'price_index' => $rates->price_index
					);
		} else if($this->CI->bw_config->price_index == 'BitcoinAverage') {
			$update = array('time' => strtotime($rates->timestamp),
							'usd' => ($rates->USD->averages->last !== '0.0000') ? $rates->USD->averages->last : $this->CI->currencies_model->get_exchange_rate('usd'),
							'gbp' => ($rates->GBP->averages->last !== '0.0000') ? $rates->GBP->averages->last : $this->CI->currencies_model->get_exchange_rate('gbp'),
							'eur' => ($rates->EUR->averages->last !== '0.0000') ? $rates->EUR->averages->last : $this->CI->currencies_model->get_exchange_rate('eur'),
							'price_index' => $rates->price_index
					);
		}

		return (isset($update) && $this->CI->currencies_model->update_exchange_rates($update) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Check Alert
	 * 
	 * Query bitcoin daemon for an alert. Returns the alert message if
	 * there is currently a network alert, otherwise it returns FALSE.
	 * 
	 * @return	string/FALSE
	 */
	public function check_alert() {
		// Return false if the bitcoin daemon is offline.
		$info = $this->getinfo();
		if(!is_array($info))
			return FALSE;
		
		// Return the string if there's an alert, otherwise false.
		return (is_string($info['errors']) && strlen($info['errors']) > 0) ? $info['errors'] : FALSE;
	}

};
