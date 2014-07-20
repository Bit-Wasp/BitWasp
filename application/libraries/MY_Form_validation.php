<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package        CodeIgniter
 * @author        EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @license        http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link        http://codeigniter.com
 * @since        Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form Validation Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Validation
 * @author        EllisLab Dev Team
 * @link        http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation
{

    /**
     * Construct
     *
     * Extend initial class, accept configuration
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        log_message('debug', 'BW Form Validation Extras Initialized');
    }

    /**
     * __get
     *
     * magic function.
     *
     * @param    object $object
     */
    public function __get($object)
    {
        $this->CI = & get_instance();
        return $this->CI->$object;
    }

    // --------------------------------------------------------------------

    /**
     * Check Autorun Interval
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_autorun_interval($str)
    {
        return $this->greater_than_equal_to($str, 0);
    }

    /**
     * Check Bitcoin Amount
     *
     * Check that the amount is greater than or equal to one satoshi.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_bitcoin_amount($str)
    {
        return $this->greater_than_equal_to($str, 0.00000001);
    }

    /**
     * Check Bitcoin Amount Free
     *
     * This checks that the amount is any positive amount.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_bitcoin_amount_free($str)
    {
        return $this->greater_than_equal_to($str, 0);
    }


    /**
     * Check Bitcoin Public Key
     *
     * Passes the input to the BitcoinLib library which tries to create
     * a valid point on the secp256k1 curve.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_bitcoin_public_key($str)
    {
        return \BitWasp\BitcoinLib\BitcoinLib::validate_public_key($str);
    }

    public function check_bitcoin_address($str)
    {
        return \BitWasp\BitcoinLib\BitcoinLib::validate_address($str, '05');
    }

    /**
     * Check Bool Enabled
     *
     * Checks that intput is either 0 or 1, indicating false/true, disabled/enabled, etc.
     * Alias for check_book(), this fxn triggers appropriate error.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_bool_enabled($str)
    {
        return $this->_check_bool($str);
    }

    /**
     * Check Bool AreYouSure?
     *
     * Checks that the input is either 0 or 1, indicating false/true, disabled/enabled, etc.
     * Alias for check_bool() which triggers appropriate error.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_bool_areyousure($str)
    {
        return $this->_check_bool($str);
    }

    /**
     * Check Bool
     *
     * Checks that the input is either 0 or 1, indicating false/true, disabled/enabled, etc.
     * Alias for check_bool which triggers appropriate error.
     *
     * @param        string $str
     * @return        boolean
     */
    public function _check_bool($str)
    {
        return $this->is_natural($str) AND in_array($str, array('0', '1'));
    }

    /**
     * Check Captcha
     *
     * Checks the given captcha against what's stored in the session.
     * Used In: Users, Authorize
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_captcha($str)
    {
        return $this->alpha_numeric($str) AND $this->CI->bw_captcha->check($str);
    }

    /**
     * Check Captcha Length
     *
     * Checks that the given captcha length is > 0, and <= 15.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_captcha_length($str)
    {
        return $this->is_natural_no_zero($str) && $this->less_than_equal_to($str, 15);
    }

    /**
     * Check Checkable
     *
     * This function checks that the parameter == 'set'. This is a simple check
     * for form submissions where only a CSRF check is required, rather than
     * needing any data.
     *
     * @param  $str
     * @return bool
     */
    public function check_checkable($str)
    {
        return $str == 'set';
    }

    /**
     * Check Custom Location Exists
     *
     * Checks tha the custom location exists.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_custom_location_exists($str)
    {
        if (!$this->is_natural($str))
            return false;

        // Use custom list if we have it, to spare the DB query.
        if ($this->CI->bw_config->location_list_source == 'Custom') {
            return isset($this->CI->bw_config->locations[$str]);
        } else {
            $this->CI->load->model('location_model');
            $custom = $this->CI->location_model->get_list('Custom', false);
            return isset($custom[$str]);
        }
    }

    /**
     * Check custom parent location exists
     *
     * Takes the stored custom locations from memory if available, otherwise
     * will load them from the database. Checks that the supplied location
     * is for a parent ID.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_custom_parent_location_exists($str)
    {
        if (!$this->is_natural($str))
            return false;

        if ($str == '0')
            return true;

        // Use custom list if we have it, to spare the DB query.
        if ($this->CI->bw_config->location_list_source == 'Custom') {
            $custom = $this->CI->bw_config->locations;
        } else {
            $this->CI->load->model('location_model');
            $custom = $this->CI->location_model->get_list('Custom', false);
        }

        $found = false;
        foreach ($custom as $location) {
            if ($location['parent_id'] == $str)
                $found = true;
        }
        return $found;
    }

    /**
     * Check Delete On Read
     *
     * This function checks whether the given input for the Message
     * 'burn on reading' functionality is acceptable. NULL means it wasn't
     * selected, 1 means it was selected.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_delete_on_read($str)
    {
        return in_array($str, array(NULL, '1'));
    }

    /**
     * Check Is Parent Category
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_is_parent_category($str)
    {
        if ($str == '0')
            return true;

        return isset($this->CI->bw_config->categories[$str])
        AND $this->CI->bw_config->categories[$str]['count_child_items'] == 0;
        /*
                $found = false;
                foreach ($this->CI->bw_config->categories as $cat)
                {
                    if($cat['parent_id'] == $str)
                        $found = true;
                }

                return $found;*/
    }

    /**
     * Check Parent Category Root
     *
     * @param    string $str
     * @return    boolean
     */
    public function check_valid_category_root($str)
    {
        return $str == '0' OR isset($this->CI->bw_config->categories[$str]);
    }

    /**
     * Check Master Public Key
     *
     * Checks that the given master public key was valid. Prefixes it with
     * 04 to make it look like an uncompressed key so $this->check_bitcoin_public_key
     * can handle it.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_master_public_key($str)
    {
        return $str == '' OR $this->check_bitcoin_public_key('04' . $str);
    }

    /**
     * Check Not Parent Category
     *
     * Checks that the chosen category ID isn't a parent category, by
     * looping through categories in config and checking against the parent.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_not_parent_category($str)
    {
        if (!$this->is_natural_no_zero($str))
            return false;

        foreach ($this->CI->bw_config->categories as $cat) {
            if ($cat['parent_id'] == $str)
                return true;
        }

        return false;
    }

    /**
     * Check PGP Encrypted
     *
     * This function extracts the base64 encoded message from a PGP message,
     * and checks the data against a regex. Returns true if the input was
     * a valid PGP encrypted message (ie, headers match, and encoding is
     * correct. Maybe using a php library rather than extension would
     * allow us to verify this further?)
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_pgp_encrypted($str)
    {
        $spos = stripos($str, '-----BEGIN PGP MESSAGE-----');
        $spos = ($spos !== false) ? $spos + 27 : false;

        $epos = stripos($str, '-----END PGP MESSAGE-----');
        $epos = ($epos !== false) ? $epos - 27 : false;

        if ($spos == false || $spos == false)
            return false;

        // Extract the ciphertext, and check against a regex.
        $cipher_text = preg_replace('/\s+/', '', substr($str, $spos, $epos));
        return (preg_match('^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$^', $cipher_text)) ? true : false;
    }

    /**
     * Check PGP Required for this User
     *
     * Checks the provided users PGP settings.
     * Returns true if the user has not blocked non-PGP messages,
     * or if they have blocked such messages, but the message is encrypted.
     * Returns false otherwise.
     *
     * @param        string $str
     * @param        string $post_name
     * @return        boolean
     */
    public function check_pgp_required_for_user($str, $post_name)
    {
        $this->CI->load->model('accounts_model');

        $block_non_pgp = $this->CI->accounts_model->user_requires_pgp_messages($this->CI->input->post($post_name));
        return $block_non_pgp == false OR $block_non_pgp == true AND $this->check_pgp_encrypted($str) == true;
    }

    /**
     * Check Vendor Prepared Comments
     *
     * Check the input is a valid prepared comment about a vendor.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_prepared_comments_vendor($str)
    {
        return in_array($str, array(
            'Excellent vendor, would do business again.',
            'Slow delivery.',
            'Poor communication.',
            'Poor communication & slow delivery.',
            'Fast delivery.'));
    }

    /**
     * Check Buyer Prepared Comments
     *
     * Checks the input is a valid prepared comment about a buyer.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_prepared_comments_buyer($str)
    {
        return in_array($str, array(
            'Fast payer.',
            'Would do business again.',
            'Will avoid in future.'));
    }

    /**
     * Check Item Prepared Comments
     *
     * Checks the choice is a valid prepared comment.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_prepared_comments_item($str)
    {
        return in_array($str, array(
            'Did not match description.',
            'Poor quality.',
            'Excellent quality.',
            'Would purchase again.'));
    }

    /**
     * Check Price Index
     *
     * Check the supplied parameter is a valid price index config value.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_price_index($str)
    {
        $config = $this->CI->bw_config->price_index_config;
        return $str == 'Disabled' OR isset($config[$str]);
    }

    /**
     * Check Proxy Type
     *
     * Checks that the proxy type is supported by the system.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_proxy_type($str)
    {
        return in_array($str, array('HTTP', 'SOCKS5'));
    }

    /**
     * Check Proxy URL
     *
     * Checks the supplied ip:port against a regex. $str may be an
     * empty string if the proxy is disabled, otherwise it uses
     * preg_match to verify the regex. Returns true if $str is valid,
     * and false on invalid input.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_proxy_url($str)
    {
        if ($str == '')
            return true;

        preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3}):([0-9]{1,5})$/', $str, $match);
        return count($match) > 0;
    }

    /**
     * Check Registration Token Charge
     *
     * Checks that the amount set by the admin for the amount to pay
     * on registration, for a user with this token, is either default,
     * for the default for that user-type, or else an arbitrary amount
     * (0 for free, or a valid bitcoin amount).
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_registration_token_charge($str)
    {
        return $str == 'default' || $this->check_bitcoin_amount_free($str);
    }

    /**
     * Check Review Comments Source
     *
     * Check the submitted source for comments for this subject -
     * It can either be 'input' or 'prepared'.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_review_comments_source($str)
    {
        return in_array($str, array('input', 'prepared'));
    }

    /**
     * Check Review Length
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_review_length($str)
    {
        return in_array($str, array('short', 'long'));
    }

    /**
     * Check RSA Keysize
     *
     * Checks that the keysize is either 1024, 2048, or 4096.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_rsa_keysize($str)
    {
        return in_array($str, array('1024', '2048', '4096'));
    }

    /**
     * Check Role Any
     *
     * Check that the given role is valid, ie, either 1 2 or 3.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_role_any($str)
    {
        return in_array($str, array('1', '2', '3'));
    }

    /**
     * Check User Exists
     *
     * Checks that the username exists in the users database be trying
     * to count results.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_exists($str)
    {
        return $this->CI->users_model->check_user_exists(array('user_name' => $str));
    }

    /**
     * Check User Rating Input
     *
     * Used on the admin panel when entering the minimum rating for
     * trusted users. Simply checks the chosen rating > 0 && < 5.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_rating_input($str)
    {
        return ($this->greater_than_equal_to($str, 0) && $this->less_than_equal_to($str, 5));
    }

    /**
     * Check User Search For
     *
     * This checks if the search_for parameter is an allowed class of
     * users. Returns true on success, false on failure.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_search_for($str)
    {
        return in_array($str, array('', 'all_users', 'buyers', 'vendors', 'admins'));
    }

    /**
     * Check User Search List
     *
     * This function checks if the order of the list (in order_by)
     * is allowed. It can be ascending, descending, or in random order.
     * Returns true on valid input, false on failure.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_search_list($str)
    {
        return in_array($str, array(NULL, 'random', 'ASC', 'DESC'));
    }

    /**
     * Check User Search Order By
     *
     * This checks if the order_by parameter is allowed. Returns true on
     * valid input, false on failure.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_search_order_by($str)
    {
        return in_array($str, array('', 'id', 'user_name', 'register_time', 'login_time', 'banned'));
    }

    /**
     * Check User Search With Property
     *
     * Checks if the with_property specifier for a search is an allowed
     * value. This narrows down the search to specific types of users.
     * Returns true on success, false on failure.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_user_search_with_property($str)
    {
        return in_array($str, array('', 'activated', 'not_activated', 'banned', 'not_banned'));
    }

    /**
     * Check Valid Category
     *
     * Checks first that the number is natural, and not zero.
     * Then looks through categories, checking if it finds it in the array.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_category($str)
    {
        if (!$this->is_natural_no_zero($str))
            return false;

        $found = false;
        foreach ($this->CI->bw_config->categories as $cat) {
            if ($cat['id'] == $str)
                $found = true;
        }
        return $found;
    }

    /**
     * Check Valid Currency
     *
     * Check that the user has submitted a valid currency, i.e., already
     * in the current list of currencies loaded in the config.
     *
     * Used in: Accounts, Listings, Users.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_currency($str)
    {
        return $this->is_natural($str) AND isset($this->CI->bw_config->currencies[$str]);
    }

    /**
     * Check Valid Location
     *
     * Check that the user has submitted a valid location, i.e., already
     * in the current list of locations loaded in the config.
     *
     * $str should be an natural number, this function does not allow a
     * the root location to be chosen ('0').
     *
     * Used in: Accounts, Users, Items.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_location($str)
    {
        return $this->is_natural_no_zero($str) AND isset($this->CI->bw_config->locations[$str]);
    }

    /**
     * Check Valid Location List Source
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_location_list_source($str)
    {
        return $str == 'Custom' OR $str == 'Default';
    }

    /**
     * Check Valid Location Ship-to
     *
     * Checks that the selected destination location is acceptable
     *  - ie, is either 'worldwide', or an ID of a valid location.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_location_shipto($str)
    {
        return $str == 'worldwide' OR ($this->is_natural_no_zero($str) AND isset($this->CI->bw_config->locations[$str]));
    }

    /**
     * Check Valid Location ShipFrom
     *
     * Same as above, just without worldwide.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_location_shipfrom($str)
    {
        // Domestic?
        return $this->is_natural_no_zero($str) AND isset($this->CI->bw_config->locations[$str]);
    }

    /**
     * Check Valid Rating Choice
     *
     * Checks that the user has supplied a valid rating, ie, a natural
     * number from 1 - 5.
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_rating_choice($str)
    {
        return in_array((string)$str, array('1', '2', '3', '4', '5'));
    }

    /**
     * Check Valid Role
     *
     * Checks that the selected role is for a valid user:
     *
     * Used in:
     *
     * @param        string $str
     * @return        boolean
     */
    public function check_valid_registration_role($str)
    {
        return in_array($str, (($this->CI->bw_config->vendor_registration_allowed == true) ? array('1', '2', '3') : array('1', '3')));
    }

    public function validate_bip32_key($str)
    {
        return is_array(\BitWasp\BitcoinLib\BIP32::import($str)) == true;
    }

    public function validate_is_public_bip32($str)
    {
        $decode = \BitWasp\BitcoinLib\BIP32::import($str);
        return $decode['type'] == 'public';
    }

    public function validate_depth_bip32($str)
    {
        $decode = \BitWasp\BitcoinLib\BIP32::import($str);
        return $decode['depth'] == '1';
    }
}

/* End of file MY_Form_validation.php */
/* Location: ./system/libraries/MY_Form_validation.php */
