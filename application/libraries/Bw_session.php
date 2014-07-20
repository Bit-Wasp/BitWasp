<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Session Library
 *
 * This session is used to manage the sessions in the application.
 * Performs checking if the session has expired
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Session
 * @author        BitWasp
 */
class Bw_session
{

    /**
     * CI
     *
     * Store CodeIgniter
     */
    public $CI;

    /**
     * URI
     *
     * Present URI, as an array
     */
    public $URI;

    /**
     * User Role
     */
    public $user_role;

    /**
     * Auth Level
     */
    public $auth_level;

    /**
     * Construct
     *
     * Initialize the user sesssion
     */
    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('auth_model');
        $this->CI->load->model('users_model');
        $this->CI->load->library('bw_auth');

        $this->URI = $this->CI->current_user->URI;

        if ($this->CI->current_user->logged_in()) {

            // Kill a session due to inactivity, or if the user is deleted/banned while logged in.
            // Also kill a session if maintenance mode is on and the user role is not an admin.
            if ((time() - $this->CI->current_user->last_activity) > $this->CI->bw_config->login_timeout && $this->CI->session->userdata('new_session') !== 'true'
                OR $this->CI->current_user->user == FALSE
                OR $this->CI->current_user->user['banned'] == '1'
                OR $this->CI->bw_config->maintenance_mode == TRUE AND $this->CI->current_user->user_role !== 'Admin'
            ) {

                $this->destroy();
                redirect('login');

            } else {
                // Remove new_session from memory if set.
                if ($this->CI->session->userdata('new_session'))
                    $this->CI->session->unset_userdata('new_session');

                // Check if auth reqs or the message password has expired & remove them.
                $this->CI->bw_auth->auth_timeout();
                $this->CI->bw_auth->message_password_timeout();

                // Set the last activity to track for expiry.
                $this->CI->session->set_userdata('last_activity', time());

                $this->user_role = $this->CI->current_user->user_role;
            }
        }

        $this->validate_req();
    }

    /**
     * Create
     *
     * Create a session based on the supplied userdata $user, and an
     * optional parameter specifying the type of the session (such
     * as a full session (param=null), for two factor (param=two_factor)
     * or to register a pgp key (param=force_pgp)
     *
     * @param        array $user
     * @param        array $instance_type
     * @return        void
     */
    public function create($user, $instance_type = NULL)
    {
        if ($instance_type == 'force_pgp') {
            $userdata = array('user_id' => $user['id'],
                'force_pgp' => 'true');
        } else if ($instance_type == 'totp_factor') {
            $userdata = array('user_id' => $user['id'],
                'totp_factor' => 'true');
        } else if ($instance_type == 'pgp_factor') {
            $this->CI->session->unset_userdata('entry_payment');
            $userdata = array('user_id' => $user['id'],
                'pgp_factor' => 'true');
        } else if ($instance_type == 'entry_payment') {
            $userdata = array('user_id' => $user['id'],
                'entry_payment' => 'true');
        } else {
            $this->CI->session->unset_userdata('force_pgp');
            $this->CI->session->unset_userdata('two_factor');
            $this->CI->session->unset_userdata('entry_payment');
            $userdata = array('logged_in' => 'true',
                'last_activity' => time(),
                'new_session' => 'true',
                'user_id' => $user['id'],
                'user_hash' => $user['user_hash'],
                'user_name' => $user['user_name'],
                'user_role' => $user['user_role'],
                'local_currency' => $user['local_currency'],
                'message_password' => NULL);
        }
        $this->CI->session->set_userdata($userdata);
    }

    /**
     * Destroy
     *
     * Destroy the current session, remove userdata, and redirect
     * to the login page.
     *
     * @param        boolean $redirect
     * @return        void
     */
    public function destroy($redirect = TRUE)
    {
        $this->CI->session->unset_userdata('logged_in');
        $this->CI->session->unset_userdata('auth_reqs');
        $this->CI->session->unset_userdata('message_password');
        // Destroy the session.
        $this->CI->session->sess_destroy();
        if ($redirect == TRUE)
            redirect('login');
    }

    /**
     * Validate Request
     *
     * Validate a users request to view a page.
     * Authorization for a pages first uri (Current_user->URI[0]) is
     * stored in the page_authorization's table. Some of the rules are
     *  - If the admin allows it, allow users to view the homepage, items/item, category, user pages.
     *  - If the authorization level is unset, allow the request.
     *  - If a two factor session is set, and the user isn't on that page or logout, redirect them to the two factor form.
     *  - If a user has the force_pgp flag set, and they're not on that page or the logout page, redirect to the PGP form.
     *  - If the page is restricted to guests only (login/register), the user must not be logged in.
     *  - If the authorization level == 'login' and the user is logged in, allow their request.
     *  - If the authorization level == 'vendor' and the user has that role, allow the request.
     *  - If the authorization level == 'buyer' and the user has that role, allow the request.
     *  - If the authorization level == 'admin' and the user has that role, allow the request.
     *  - If the authorization level == 'auth|all', all users must enter their password before viewing the page (record request for a few minutes)
     *  - If the authorization level == 'auth|admin', user MUST be an admin, and must enter their password.
     *  - If all these rules fail, direct the user to the login page.
     *
     * @return        mixed
     */
    public function validate_req()
    {
        $this->auth_level = $this->CI->auth_model->check_auth($this->URI[0]);

        if (in_array($this->URI[0], array('', 'items', 'item', 'category', 'user')) && $this->CI->bw_config->allow_guests == TRUE && !$this->CI->current_user->logged_in())
            return TRUE;

        if ($this->auth_level == FALSE)
            return TRUE;

        if ($this->CI->current_user->pgp_factor == TRUE && !in_array(uri_string(), array('login/pgp_factor', 'logout')))
            redirect('login/pgp_factor');

        if ($this->CI->current_user->totp_factor == TRUE && !in_array(uri_string(), array('login/totp_factor', 'logout')))
            redirect('login/totp_factor');

        if ($this->CI->current_user->force_pgp == TRUE && !in_array(uri_string(), array('register/pgp', 'logout')))
            redirect('register/pgp');

        if ($this->CI->current_user->entry_payment == TRUE && !in_array(uri_string(), array('register/payment', 'logout')))
            redirect('register/payment');

        if ($this->auth_level == 'guestonly') {
            // Added this if block.
            if ($this->URI[0] == 'login' || $this->URI[0] == 'register') {
                if ($this->CI->current_user->logged_in())
                    redirect('');
            }

            return TRUE;
        }

        if ($this->auth_level == 'login' && $this->CI->current_user->logged_in())
            return TRUE;

        if ($this->auth_level == 'vendor' && in_array($this->CI->current_user->user_role, array('Vendor')))
            return TRUE;

        if ($this->auth_level == 'buyer' && in_array($this->CI->current_user->user_role, array('Buyer')))
            return TRUE;

        if ($this->auth_level == 'admin' && $this->CI->current_user->user_role == 'Admin')
            return TRUE;

        // Check if the page needs password authorization.
        $multi_levels = explode('|', $this->auth_level);
        if ($multi_levels[0] == 'auth') {
            if (($multi_levels[1] == 'all'
                    && $this->CI->current_user->logged_in()
                    && $this->CI->bw_auth->check_current())
                || ($multi_levels[1] == 'admin'
                    && $this->CI->current_user->user_role == 'Admin'
                    && $this->CI->bw_auth->check_current())
            ) {
                return TRUE;

            } else {
                // Set up new Auth request, and redirect to auth page.
                $this->CI->bw_auth->new_auth();
            }
        }

        // If user does not meet the criteria above, they are forbidden from accessing the page.
        redirect('login');
        return FALSE;
    }
}

;

/* End of file Bw_session.php */
