<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Config Library
 *
 * Used to load the sites configuration from the database, and for
 * easy access throughout the application.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Config
 * @author        BitWasp
 */
class Bw_config
{

    /**
     * CI
     */
    protected $CI;

    /**
     * Registration Allowed
     *
     * An administrator can disable registration for the site. The default is to allow users to register an account.
     */
    public $registration_allowed = TRUE;

    /**
     * OpenSSL Keysize
     *
     * This is the default keysize for RSA private keys. These keys are used to
     * protect user private messages while stored in the database. The default
     * is 2048 bits.
     */
    public $openssl_keysize = 2048;

    /**
     * Site Description
     *
     * This is the sites META description, which is processed by search
     * engines to describe your site.
     */
    public $site_description = "Bitcoin Marketplace";

    /**
     * Site Title
     *
     * The title of the marketplace. Default is to be empty.
     */
    public $site_title = "";

    /**
     * Login Timeout
     *
     * This specifies how long a user can remain idle before being logged out.
     * The default setting is 30 minutes inactivity before forcing the user
     * to login again.
     */
    public $login_timeout = 30;

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
    public $force_vendor_pgp = TRUE;

    /**
     * Request Emails
     * @var bool
     */
    public $request_emails = FALSE;

    /**
     * Captcha Length
     *
     * Administrators can set the length of the capthca using this setting.
     * The default length is 5 characters.
     */
    public $captcha_length = 5;

    /**
     * Allow Guests
     *
     * The administrator may chose to force users to sign up for an account
     * before they can see the sites items, users, categories, and homepage.
     */
    public $allow_guests = TRUE;

    /**
     * Price Index
     *
     * Bitcoin exchange rates can be loaded at a specified frequency by
     * the system. This is driven by users clicking on the site. The default
     * is to have this feature disabled.
     */
    public $price_index = "Disabled";

    /**
     * Price Index Config
     * Contains config for available exchange rate sources
     *
     * @var array
     */
    public $price_index_config = array();

    /**
     * Delete Messages After
     *
     * Administrators may chose to clear user messages after a certain period
     * of time. The default setting is to store all transaction history.
     */
    public $delete_messages_after = 0;

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
     * BIP32 MPK
     *
     * This is the MPK which is used for admin multisig keys,
     */
    public $bip32_mpk = "";

    /**
     * BIP32 Iteration
     *
     * This setting is for the BIP32 master key - this is the next child
     * to be derived.
     */
    public $bip32_iteration = 0;

    /**
     * Entry Payment Vendor
     *
     * This setting determines how much the vendor must pay in order to
     * create an account on the website. The default is for vendors to
     * be able to register without payment
     */
    public $entry_payment_vendor = 0.00000000;

    /**
     * Entry Payment Buyer
     *
     * This setting determines how much a buyer has to pay in order
     * to create an account on the site. The default is for this to be
     * disabled
     */
    public $entry_payment_buyer = 0.00000000;

    /**
     * Default Rate (Fees)
     *
     * This is the default rate at which orders are charged. This is used
     * when the order_price does not fall within a range specified in the
     * database. An admin might just set a flat rate for all transactions.
     * Default is 1%
     */
    public $default_rate = 1;

    /**
     * Escrow Rate (Fees)
     *
     * This is the rate which is charged to the vendor from the balance
     * they expect to receive if they choose an escrow payment.
     */
    public $escrow_rate = 1;

    /**
     * Minimum fee
     *
     * This is the minimum fee which an order can have. Usually this
     * could be set to a figure which ensures that bitcoins sent within
     * the system can be covered re transaction fee's. Default is 0.0004
     * BTC.
     */
    public $minimum_fee = 0.0003;

    /**
     * Global Proxy Type
     *
     * This setting determines the type of the proxy which is to be used
     * when making CURL requests. The default setting is 'Disabled'.
     */
    public $global_proxy_type = 'Disabled';

    /**
     * Global Proxy URL
     *
     * This setting is for the host:port which is to be used when setting
     * a proxy. This will be used by the version checking job, and also
     * the exchange rates job. The default is unset, as the $global_proxy_type
     * parameter is disabled.
     */
    public $global_proxy_url = '';

    /**
     * Settings Preserve
     *
     * This entry is a place to store a backup of the sites configuration
     * as it enters maintenance mode. This could be up to the admin,
     * or can be triggered by a bitcoind alert or by the source code
     * checker. Default is this is empty.
     */
    public $settings_preserve = '';

    /**
     * Maintenance Mode
     *
     * This settings is configured by the admin to put the site into
     * offline mode. This can also be triggered by bitcoind alerts,
     * and source code checking. The default setting is disabled.
     */
    public $maintenance_mode = 0;

    /**
     * Terms Of Service
     *
     * A place to store the terms of service, which can be displayed if
     * the admin wishes. This can be disabled using the terms_of_service_toggle
     * setting. The default value is empty ''.
     */
    public $terms_of_service = '';

    /**
     * Terms Of Service Toggle
     *
     * This is an integer value, where '0' will be FALSE, '1' will mean
     * TRUE. This is converted to boolean as normal. Default value is
     * turned off; 0;
     */
    public $terms_of_service_toggle = 0;

    /**
     * Location List Source
     *
     * This is a string which determines what list of locations will be
     * used throughout the program. Options are 'Default' for the custom
     * list of locations (list of countries), but 'Custom' allows a
     * predefined, heirarchal tree of locations to be built.
     *
     * Default value is 'Default'.
     */
    public $location_list_source = 'Default';


    /**
     * Currencies
     * @var
     */
    public $currencies;
    /**
     * Categories
     * @var
     */
    public $categories;
    /**
     * Exchange Rates
     * @var
     */
    public $exchange_rates;
    /**
     * Locations
     * @var
     */
    public $locations;

    /**
     * Trusted User Review Count
     * Used to determine if a vendor can request up-front payment.
     * @var int
     */
    public $trusted_user_review_count;
    /**
     * Trusted User Order Count
     * Used to determine if a vendor can request up-front payment.
     * @var int
     */
    public $trusted_user_order_count;
    /**
     * Trusted User Rating
     * Used to determine if a vendor can request up-front payment.
     * @var int
     */
    public $trusted_user_rating;

    /**
     * Constructor
     *
     * Load the CodeIgniter framework, along with the config/currencies
     * model, and the bitcoin index configuration.
     */
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->CI->load->model('config_model');
        $this->CI->load->model('currencies_model');
        $this->CI->load->model('categories_model');
        $this->CI->load->model('location_model');
        $this->CI->config->load('bitcoin_index', TRUE);

        $this->reload();
    }

    public function reload()
    {
        // Pull from the DB. See phpmyadmin.
        $config = $this->CI->config_model->get();
        if ($config == FALSE)
            die('Error, BitWasp configuration not found.');

        // Update the config values in the class with the
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->vendor_registration_allowed = (bool)$this->vendor_registration_allowed;
        $this->encrypt_private_messages = (bool)$this->encrypt_private_messages;
        $this->force_vendor_pgp = (bool)$this->force_vendor_pgp;
        $this->maintenance_mode = (bool)$this->maintenance_mode;
        $this->terms_of_service_toggle = (bool)$this->terms_of_service_toggle;

        // Load the currencies and exchange rates.
        $this->currencies = $this->CI->currencies_model->get();
        $this->exchange_rates = $this->CI->currencies_model->get_rates($this->currencies);
        $this->locations = $this->CI->location_model->get_list($this->location_list_source, FALSE);
        $this->categories = $this->CI->categories_model->list_all();

        // Load the configuration of the bitcoin_index options.
        $this->price_index_config = $this->CI->config->item('bitcoin_index');

        // Convert to seconds
        $this->login_timeout = $this->login_timeout * 60;
    }

    /**
     * Load Admin
     *
     * Loads particular information for the admin panels.
     *
     * @param        string
     * @return        array
     */
    public function load_admin($panel)
    {
        $result = array();
        if ($panel == '') {
            $result = array('site_description' => $this->site_description,
                'site_title' => $this->site_title,
                'openssl_keysize' => $this->openssl_keysize,
                'base_url' => $this->base_url,
                'index_page' => $this->index_page,
                'allow_guests' => $this->allow_guests,
                'global_proxy_url' => $this->global_proxy_url,
                'global_proxy_type' => $this->global_proxy_type,
                'maintenance_mode' => $this->maintenance_mode,
                'terms_of_service_toggle' => $this->terms_of_service_toggle,
                'location_list_source' => $this->location_list_source);
        } else if ($panel == 'bitcoin') {
            $result = array('price_index' => $this->price_index,
                'price_index_config' => $this->price_index_config,
                'bip32_mpk' => $this->bip32_mpk,
                'bip32_iteration' => $this->bip32_iteration);
        } else if ($panel == 'users') {
            $result = array('registration_allowed' => $this->registration_allowed,
                'request_emails' => $this->request_emails,
                'vendor_registration_allowed' => $this->vendor_registration_allowed,
                'encrypt_private_messages' => $this->encrypt_private_messages,
                'force_vendor_pgp' => $this->force_vendor_pgp,
                'login_timeout' => $this->login_timeout / 60,
                'captcha_length' => $this->captcha_length,
                'delete_messages_after' => $this->delete_messages_after,
                'entry_payment_vendor' => $this->entry_payment_vendor,
                'entry_payment_buyer' => $this->entry_payment_buyer);
        } else if ($panel == 'items') {
            $result = array('trusted_user_review_count' => $this->trusted_user_review_count,
                'trusted_user_order_count' => $this->trusted_user_order_count,
                'trusted_user_rating' => $this->trusted_user_rating);
        } else if ($panel == 'fees') {
            $result = array('default_rate' => $this->default_rate,
                'minimum_fee' => $this->minimum_fee,
                'escrow_rate' => $this->escrow_rate);
        } else if ($panel == 'autorun') {
            $result = array('delete_messages_after' => $this->delete_messages_after,
                'price_index' => $this->price_index);
        }
        return $result;
    }

    /**
     * Status
     *
     * Loads all the variables from this library but the CI framework.
     *
     * @return        array
     */
    public function status()
    {
        $vars = get_object_vars($this);
        unset($vars['CI']);
        return $vars;
    }

    /**
     * Bitcoin Rate Config
     *
     * Loads the chosen bitcoin indexing config.
     *
     * @return        array/FALSE
     */
    public function bitcoin_rate_config()
    {
        $array = $this->price_index_config;
        return ($this->price_index == '') ? FALSE : $array[$this->price_index];
    }

}

;

/* End of file Bw_config.php */
