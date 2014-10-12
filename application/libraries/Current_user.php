<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Current User Library
 *
 * This library is used to allow easy access to information about the
 * user. User data is taken from the session, and the database, to allow
 * a central source of frequently used data.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Current_User
 * @author        BitWasp
 */
class Current_User
{

    /**
     * CI
     */
    protected $CI;

    /**
     * Auth Reqs
     *
     * Stores an array of the authentications for a restricted pages.
     */
    public $auth_reqs = array();

    /**
     * Current Auth Req
     *
     * Store the desired, but restricted page
     */
    public $current_auth_req;

    /**
     * Force PGP
     */
    public $force_pgp;

    /**
     * Logged In
     *
     * Determine whether the user is logged in.
     */
    public $logged_in = FALSE;

    /**
     * Last Activity
     */
    public $last_activity;

    /**
     * User ID
     */
    public $user_id;

    /**
     * User Hash
     */
    public $user_hash;

    /**
     * User Name
     */
    public $user_name;

    /**
     * Message Password
     */
    public $message_password;

    /**
     * Message Password Granted
     */
    public $message_password_granted;

    /**
     * TOTP factor
     */
    public $totp_factor;
    /**
     * PGP Factor
     */
    public $pgp_factor;
    /**
     * User Role
     */
    public $user_role;
    /**
     * Session ID
     */
    public $session_id;
    /**
     * URI
     */
    public $URI;
    /**
     * Entry Payment
     */
    public $entry_payment = FALSE;

    /**
     * Construct
     *
     * This function stores all the user user data in an accessible object.
     */
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->URI = explode("/", uri_string());

        if ($this->CI->session->userdata('logged_in') == 'true') {
            $this->logged_in = TRUE;
            $this->user_id = $this->CI->session->userdata('user_id');
            $this->user_hash = $this->CI->session->userdata('user_hash');
            $this->user_name = $this->CI->session->userdata('user_name');
            $this->last_activity = $this->CI->session->userdata('last_activity');
            $this->user_role = $this->CI->session->userdata('user_role');
            $this->auth_reqs = (array)json_decode($this->CI->session->userdata('auth_reqs'));
            $this->message_password = $this->CI->session->userdata('message_password');
            $this->message_password_granted = $this->CI->session->userdata('message_password_granted');
            $this->current_auth_req = $this->CI->session->userdata('current_auth_req');

            $this->CI->load->model('accounts_model');
            $this->user = $this->CI->accounts_model->get(array('user_hash' => $this->user_hash), array('own' => TRUE));
            if ($this->user !== FALSE) {
                $this->currency = ($this->CI->bw_config->price_index == 'Disabled') ? $this->CI->bw_config->currencies[0] : $this->CI->bw_config->currencies[$this->user['local_currency']];
                $this->currency['rate'] = $this->CI->bw_config->exchange_rates[(strtolower($this->currency['code']))];
            }
        } else {
            $id = $this->CI->session->userdata('user_id');

            // Default currency for non-logged in user is bitcoin.
            $this->currency = $this->CI->bw_config->currencies[0];
            $this->currency['rate'] = $this->CI->bw_config->exchange_rates[(strtolower($this->currency['code']))];

            // If an ID is set, user is in a half session.
            if (is_numeric($id) && $id !== NULL) {
                $this->user_id = $id;

                if ($this->CI->session->userdata('pgp_factor') == 'true') {
                    $this->pgp_factor = TRUE;
                    $this->user_role = 'half';
                }

                if ($this->CI->session->userdata('totp_factor') == 'true') {
                    $this->totp_factor = TRUE;
                    $this->user_role = 'half';
                }

                if ($this->CI->session->userdata('force_pgp') == 'true') {
                    $this->force_pgp = TRUE;
                    $this->user_role = 'half';
                }

                if ($this->CI->session->userdata('entry_payment') == 'true') {
                    $this->entry_payment = TRUE;
                    $this->user_role = 'half';
                }
            } else {
                $this->user_role = 'guest';
            }

        }
    }

    /**
     * Status
     *
     * Load all the information from this object.
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
     * Logged In
     *
     * Function to check if this user is logged in.
     *
     * @return        boolean
     */
    public function logged_in()
    {
        return $this->logged_in;
    }

    /**
     * Set Message Password
     *
     * Sets up the message password in the users session along with the
     * time it was set (to allow it to expire)
     *
     * @param        string $password
     * @return        void
     */
    public function set_message_password($password)
    {
        $this->CI->session->set_userdata('message_password', $password);
        $this->CI->session->set_userdata('message_password_granted', time());
    }

    public function set_return_message($message, $class = 'warning')
    {
        $this->CI->session->set_flashdata('returnMessage', json_encode(array('message' => $message, 'class' => $class)));
    }

    public function setup_vendor_bitcoin()
    {
        $this->CI->load->model('bitcoin_model');
        $this->CI->load->model('bip32_model');

        if (!is_array($this->CI->bitcoin_model->get_payout_address($this->user_id))
            OR !is_array($this->CI->bip32_model->get($this->user_id))
        ) {
            $this->set_return_message('You must configure a source of public keys, and a payout address, before you can add listings.', 'warning');
            redirect('account');
        }
    }
}

;


/* End of file Current_user.php */
