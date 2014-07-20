<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authorization Library
 *
 * Used to check if a user must enter a password before viewing a page.
 * Has automatic expiration of requests (called in Bw_session)
 * Handles redirection to /authorize and to desired page.
 *
 * Successful auth requests are stored in the users session.
 * This may be a mitigating factor in the choice of session data in a cookie
 *
 * Will eventually look after post data if there happens to be any.
 * Same functions would treat visiting /admin for the first time, and the
 * reaction to the eventual expiration, if it happens as a user is typing out a form.
 * The result should be the post data is kept also. Functions like Admin/Account/PGP
 * would need to check an as yet undesigned session identifier - if the user has just come from
 * an auth request. Then, it would check for session data. (this bit of data is a counter.
 * it will be deleted once it is incremented twice, and forgotten.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Authorization
 * @author        BitWasp
 */
class Bw_auth
{

    /**
     * Message Password Timeout
     *
     * This sets the default amount of time to hold the message password
     * in the session before expiring.
     */
    protected $message_password_timeout = 1200;

    /**
     * CI
     */
    protected $CI;

    /**
     * URI
     *
     * Store the Current_user/URI here.
     */
    public $URI;

    /**
     * Auth Req's
     *
     * Store the current users auth requests here.
     */
    public $auth_reqs;

    /**
     * Construct
     *
     * Load the codeigniter framework, the current URI, and auth_requests.
     */
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->URI = explode("/", uri_string());
        $this->auth_reqs = (array)json_decode($this->CI->session->userdata('auth_reqs'));
    }

    /**
     * Auth Timeout
     *
     * Remove any expired authorization for a page.
     */
    public function auth_timeout()
    {
        // Clear auth req data if the user isn't on the authorize page.
        if ($this->URI[0] !== 'authorize')
            $this->CI->session->unset_userdata('current_auth_req');

        if (count($this->auth_reqs) > 0) {
            $auth_reqs = $this->auth_reqs;
            $new = array();

            // Purge any expired ones.
            foreach ($auth_reqs as $key => $req) {
                if (($req->time + $req->timeout) > time()) {
                    $new[$key] = array('timeout' => $req->timeout,
                        'time' => $req->time);
                }
            }
            $this->set_data($new);
        }
    }

    /**
     * New Auth
     *
     * Generate a new authorization request. Record the current URI
     * so we can redirect the user later on, and direct them to the
     * authorize page.
     *
     * @return        void
     */
    public function new_auth()
    {
        $config = array('current_auth_req' => uri_string());
        $this->CI->session->set_userdata($config);

        redirect('authorize');
    }

    /**
     * Check Current
     *
     * Check if the current URI has already been authorized by the user.
     *
     * @return        bool
     */
    public function check_current()
    {
        foreach ($this->auth_reqs as $key => $req) {
            if ($key == $this->URI[0])
                return TRUE;
        }

        return FALSE;
    }

    /**
     * Has Request
     *
     * Check if the user has made an attempt to authorize a request.
     * @return        bool
     */
    public function has_request()
    {
        return (is_string($this->CI->session->userdata('current_auth_req'))) ? TRUE : FALSE;
    }

    /**
     * Set Data
     *
     * A general function to store the authorization requests in the session.
     *
     * @param        array $array
     * @return        void
     */
    public function set_data(array $auth_reqs = array())
    {
        $this->CI->session->set_userdata('auth_reqs', json_encode($auth_reqs));
    }

    /**
     * Setup Authorization
     *
     * This function records the new authorized request for the URI, along
     * with the current time, and the timeout for this request.
     *
     * @param        string $URI
     * @param        int $timeout
     * @return        void
     */
    public function setup_auth($URI, $timeout)
    {
        if ($timeout > 0) {
            $new_auth = $this->auth_reqs;
            $new_auth[$URI[0]] = array('timeout' => $timeout,
                'time' => time());
            $this->set_data($new_auth);
        }
    }

    /**
     * Successful Auth
     *
     * Record the successful authorization request, how long until it expires,
     * and then provide the requested URI so the user can be redirected.
     *
     * @return        string
     */
    public function successful_auth()
    {
        //	$this->CI->load->model('auth_model');
        $attempted_uri = $this->CI->current_user->current_auth_req;
        $URI = explode('/', $attempted_uri);

        // Lookup timeout.
        $timeout = $this->CI->auth_model->check_auth_timeout($URI[0]);
        $this->CI->session->unset_userdata('current_auth_req');

        $this->setup_auth($URI, $timeout);
        return $attempted_uri;
    }

    /**
     * Message Password Timeout
     *
     * This function will check if the message password is expired, and
     * should be forgotten.
     *
     * @return        void
     */
    public function message_password_timeout()
    {
        $grant_time = $this->CI->session->userdata('message_password_granted');

        if ($grant_time !== NULL && $grant_time < (time() - $this->message_password_timeout)) {
            $this->CI->session->unset_userdata('message_password');
            $this->CI->session->unset_userdata('message_password_granted');
        }
    }

    /**
     * Generate Two Factor Token
     *
     * This function will generate and store a new two-factor token.
     * If the challenge is successfully generated, then the challenge is
     * returned. On failure, the function returns FALSE.
     *
     * @return        string / FALSE
     */
    public function generate_two_factor_token()
    {
        $this->CI->load->model('accounts_model');

        // Load the users PGP key.
        $key = $this->CI->accounts_model->get_pgp_key($this->CI->current_user->user_id);
        if ($key == FALSE) // Abort if it's not set.
            return FALSE;

        // Generate a solution.
        $solution = $this->CI->general->generate_salt();
        // Encrypt the challenge.
        $challenge = $this->CI->gpg->encrypt($key['fingerprint'], "Login Token: $solution\n");

        if ($challenge == FALSE) // Abort if unsuccessful.
            return FALSE;

        // If we successfully stored the solution, return the challenge.
        return ($this->CI->auth_model->add_two_factor_token($solution) == TRUE) ? $challenge : FALSE;
    }
}

;

/* End of file Bw_auth.php */
