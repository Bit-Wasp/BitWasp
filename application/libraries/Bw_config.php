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
		
		// Pull from the DB. See phpmyadmin. 
		$config = $this->CI->config_model->get();
		
		if($config == FALSE)
			die('Error, BitWasp configuration not found.');
		
		// If any fields are missing, set them to NULL.
		$config = $this->CI->general->expect_keys('site_description, delete_messages_after, ban_after_inactivity, price_index, site_title, openssl_keysize, allow_guests, vendor_registration_allowed, login_timeout, encrypt_private_messages, registration_allowed, base_url, captcha_length, index_page, force_vendor_pgp', $config);
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
	public function load_admin($panel){
		if($panel == '') {
			$result = array('site_description' => $this->site_description,
							'site_title' => $this->site_title,
							'openssl_keysize' => $this->openssl_keysize,
							'base_url' => $this->base_url,
							'index_page' => $this->index_page,
							'allow_guests' => $this->allow_guests);
		} else if($panel == 'bitcoin') {
			$result = array('price_index' => $this->price_index,
							'price_index_config' => $this->price_index_config,
							'delete_transactions_after' => $this->delete_transactions_after);
		} else if($panel == 'users') {
			$result = array('registration_allowed' => $this->registration_allowed,
							'vendor_registration_allowed' => $this->vendor_registration_allowed,
							'encrypt_private_messages' => $this->encrypt_private_messages,
							'force_vendor_pgp' => $this->force_vendor_pgp,
							'login_timeout' => $this->login_timeout/60,
							'captcha_length' => $this->captcha_length,
							'ban_after_inactivity' => $this->ban_after_inactivity,
							'delete_messages_after' => $this->delete_messages_after);
		} else if($panel == 'items') {
			$result = array();
		} else if($panel == 'autorun') {
			$result = array('ban_after_inactivity' => $this->ban_after_inactivity,
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
