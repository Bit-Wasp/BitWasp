<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** 
 * Config Library
 * 
 * Used to load the sites configuration from the database, and for 
 * easy access throughout the application.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Config
 * @author		BitWasp
 */ 
class Bw_config {

	protected $CI;
	
	/**
	 * Registration Allowed
	 * 
	 * An administrator can disable registration for the site. The default is to allow users to register an account.
	 */
	public $registration_allowed	= TRUE;
	
	/**
	 * OpenSSL Keysize
	 * 
	 * This is the default keysize for RSA private keys. These keys are used to 
	 * protect user private messages while stored in the database. The default
	 * is 2048 bits.
	 */
	public $openssl_keysize			= 2048;
	
	/**
	 * Site Description
	 * 
	 * This is the sites META description, which is processed by search
	 * engines to describe your site.
	 */
	public $site_description		= "Bitcoin Marketplace";
	
	/**
	 * Site Title
	 * 
	 * The title of the marketplace.
	 */
	public $site_title				= "";
	
	/**
	 * Login Timeout
	 * 
	 * This specifies how long a user can remain idle before being logged out.
	 * The default setting is 30 minutes inactivity before forcing the user
	 * to login again.
	 */
	public $login_timeout			= 30;
	
	/**
	 * Vendor Registration Allowed
	 * 
	 * It is possible to disable vendor registration for the website. 
	 * Default setting is to allow vendors to register.
	 */
	public $vendor_registration_allowed = TRUE;
	
	/**
	 * Encrypt Private Messages
	 * 
	 * Private messages can be encrypted with RSA keys to store them securely 
	 * until the user log's in. Private key's are protected by a password,
	 * generated from the users salt and a chosen PIN. The password is 
	 * never stored on the server.
	 */
	public $encrypt_private_messages = TRUE;
	
	/**
	 * Force Vendor PGP
	 * 
	 * Administrators can require that vendors have GPG keys enabled. 
	 * The default setting is to require vendors to register PGP keys when
	 * they sign up for an account.
	 */
	public $force_vendor_pgp		= TRUE;
	
	/**
	 * Captcha Length
	 * 
	 * Administrators can set the length of the capthca using this setting.
	 * The default length is 5 characters.
	 */
	public $captcha_length			= 5;
	
	/**
	 * Allow Guests
	 * 
	 * The administrator may chose to force users to sign up for an account
	 * before they can see the sites items, users, categories, and homepage.
	 */
	public $allow_guests			= TRUE;
	
	/**
	 * Price Index
	 * 
	 * Bitcoin exchange rates can be loaded at a specified frequency by
	 * the system. This is driven by users clicking on the site. The default
	 * is to have this feature disabled.
	 */
	public $price_index 			= "Disabled";
	
	/**
	 * Ban After Inactivity
	 * 
	 * Users can be banned after a certain period of inactivity. This ban
	 * will trigger an automatic refund of bitcoins to the users cashout address.
	 * The default is to allow accounts to remain active indefinitely.
	 */
	public $refund_after_inactivity	= 0;
	
	/**
	 * Delete Transactions After
	 * 
	 * Administrators may chose to clear transaction history after a certain
	 * period of time. The default setting is to store all transaction history.
	 */
	public $delete_transactions_after = 0;
	
	/**
	 * Delete Messages After
	 * 
	 * Administrators may chose to clear user messages after a certain period 
	 * of time. The default setting is to store all transaction history.
	 */
	public $delete_messages_after	= 0;
	
	/**
	 * Max Main Balance
	 * 
	 * Administrators may chose to send excessive funds in the "main"
	 * wallet account to a secured, offline wallet. By setting this threshold,
	 * the script will check daily for a balance in the main account exceding
	 * this amount. If this is the case, the excess will be sent to the offline
	 * address. The default setting is to let the funds remain in the wallet.
	 */
	public $max_main_balance		= 0.00000000;	
	
	/**
	 * Max Fees Balance
	 * 
	 * Administrators may chose to send excessive funds in the "fees"
	 * wallet account to a secured, offline wallet. By setting this threshold,
	 * the script will check daily for a balance in the fees account exceding
	 * this amount. If this is the case, the excess will be sent to the offline
	 * address. The default setting is to let the funds remain in the wallet.
	 */
	public $max_fees_balance		= 0.00000000;

	/**
	 * Base URL
	 * 
	 * (planned feature) Administrators can hard code the base URL of the
	 * code, or allow CodeIgniter to try and work it out. The default is
	 * to leave this to CodeIgniter.
	 */
	public $base_url;
	 
	/**
	 * Index Page
	 * 
	 * (planned feature) Administrators can specify whether they want URL's
	 * to include the index.php string, or use mod_rewrite to tidy the URL's.
	 * The default is to support mod_rewrite.
	 */
	public $index_page;
	  
	/**
	 * Electrum MPK
	 * 
	 * This setting is for the electrum master key, from which bitcoin
	 * addresses are derived for backups
	 */
	public $electrum_mpk			= "";
	  
	/**
	 * Entry Payment Vendor
	 * 
	 * This setting determines how much the vendor must pay in order to 
	 * create an account on the website. The default is for vendors to
	 * be able to register without payment
	 */
	public $entry_payment_vendor	= 0.00000000;

	/**
	 * Entry Payment Buyer
	 * 
	 * This setting determines how much a buyer has to pay in order
	 * to create an account on the site. The default is for this to be 
	 * disabled
	 */
	public $entry_payment_buyer		= 0.00000000;
	
	/**
	 * Auto Finalize Threshold
	 * 
	 * This setting determines how long an order can go on where:
	 * (1) the progress=3 but the vendor hasn't logged in for X days.
	 * (2) the progress=4 and finalized=0 but the buyer hasn't logged in for X days.
	 * In 1, the buyer will receive a refund, and in 2, the vendor will
	 * receive the funds for the item.
	 */
	public $auto_finalize_threshold	= 0;

	/**
	 * Default Rate (Fees)
	 * 
	 * This is the default rate at which orders are charged. This is used
	 * when the order_price does not fall within a range specified in the 
	 * database. An admin might just set a flat rate for all transactions.
	 * Default is 1%
	 */
	public $default_rate 			= 1;
	
	/**
	 * Minimum fee
	 * 
	 * This is the minimum fee which an order can have. Usually this 
	 * could be set to a figure which ensures that bitcoins sent within
	 * the system can be covered re transaction fee's. Default is 0.0004 
	 * BTC.
	 */
	public $minimum_fee				= 0.0003;
	
	/**
	 * Balance Backup Method
	 * 
	 * This setting determines which way it should back up balances.
	 * At this moment, it is possible to generate a new private key
	 * which is securely stored for the admin, or to generate deterministic
	 * addresses via electrum. Be default, this is disabled.
	 */
	public $balance_backup_method 	= '';
	
	/**
	 * Constructor
	 * 
	 * Load the CodeIgniter framework, along with the config/currencies 
	 * model, and the bitcoin index configuration.
	 */
	public function __construct(){
		$this->CI = &get_instance();
		
		$this->CI->load->model('config_model');
		$this->CI->load->model('currencies_model');
		$this->CI->config->load('bitcoin_index', TRUE);
		
		$this->reload();
	}

	public function reload() {
		// Pull from the DB. See phpmyadmin. 
		$config = $this->CI->config_model->get();
		if($config == FALSE)
			die('Error, BitWasp configuration not found.');
		
		foreach($config as $key => $value) {
			$this->$key = $value;
		}

		// Convert ENUM's to boolean values.
		$this->registration_allowed = ($this->registration_allowed == '1') ? TRUE : FALSE;
		$this->vendor_registration_allowed = ($this->vendor_registration_allowed == '1') ? TRUE : FALSE;
		$this->encrypt_private_messages = ($this->encrypt_private_messages == '1') ? TRUE : FALSE;
		$this->force_vendor_pgp = ($this->force_vendor_pgp == '1') ? TRUE : FALSE;
		
		$this->currencies = $this->CI->currencies_model->get_exchange_rates();
		$this->price_index_config = $this->CI->config->item('bitcoin_index');	
		
		// Automatically convert to seconds
		$this->login_timeout = $this->login_timeout*60;
	}

	/**
	 * Load Admin
	 * 
	 * Loads particular information for the admin panels. 
	 * 
	 * @param		string
	 * @return		array
	 */
	public function load_admin($panel, $reload = FALSE){
		if($reload == TRUE){
			$this->reload();
		}
		
		if($panel == '') {
			$result = array('site_description' => $this->site_description,
							'site_title' => $this->site_title,
							'openssl_keysize' => $this->openssl_keysize,
							'base_url' => $this->base_url,
							'index_page' => $this->index_page,
							'allow_guests' => $this->allow_guests);
		} else if($panel == 'bitcoin') {
			//$this->CI->load->library('bw_bitcoin');
			$result = array('price_index' => $this->price_index,
							'price_index_config' => $this->price_index_config,
							'electrum_mpk' => $this->electrum_mpk,
							'delete_transactions_after' => $this->delete_transactions_after,
							'balance_backup_method' => $this->balance_backup_method);
			$accounts = $this->CI->bw_bitcoin->listaccounts();
			foreach($accounts as $account => $balance) {
				$var = 'max_'.$account.'_balance';
				if(isset($this->CI->bw_config->$var))
					$result[$var] = $this->CI->bw_config->$var;
			}
							
		} else if($panel == 'users') {
			$result = array('registration_allowed' => $this->registration_allowed,
							'vendor_registration_allowed' => $this->vendor_registration_allowed,
							'encrypt_private_messages' => $this->encrypt_private_messages,
							'force_vendor_pgp' => $this->force_vendor_pgp,
							'login_timeout' => $this->login_timeout/60,
							'captcha_length' => $this->captcha_length,
							'refund_after_inactivity' => $this->refund_after_inactivity,
							'delete_messages_after' => $this->delete_messages_after,
							'entry_payment_vendor' => $this->entry_payment_vendor,
							'entry_payment_buyer' => $this->entry_payment_buyer);
		} else if($panel == 'items') {
			$result = array('auto_finalize_threshold' => $this->auto_finalize_threshold);
		} else if($panel == 'fees') {
			$result = array('default_rate' => $this->default_rate,
							'minimum_fee' => $this->minimum_fee);
		} else if($panel == 'autorun') {
			$result = array('refund_after_inactivity' => $this->refund_after_inactivity,
							'delete_messages_after' => $this->delete_messages_after,
							'delete_transactions_after' => $this->delete_transactions_after,
							'price_index' => $this->price_index);
		}
		return $result;
	}

	/**
	 * Status
	 * 
	 * Loads all the variables from this library but the CI framework.
	 * 
	 * @return		array
	 */
	public function status() {
		$vars = get_object_vars($this);
		unset($vars['CI']);
		return $vars;
	}

	/**
	 * Bitcoin Rate Config
	 * 
	 * Loads the chosen bitcoin indexing config.
	 * 
	 * @return		array/FALSE
	 */
	public function bitcoin_rate_config(){
		$array = $this->price_index_config;	
		return ($this->price_index == '') ? FALSE : $array[$this->price_index];
	}

};


 /* End of file Bw_config.php */
