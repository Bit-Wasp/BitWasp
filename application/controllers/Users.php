<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users Controller
 *
 * This class handles the buyer and vendor side of the order process.
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Users
 * @author        BitWasp
 *
 */
class Users extends MY_Controller
{

    /**
     * Constructor
     *
     * Load libs/models.
     *
     * @access    public
     * @see        Libraries/Bw_Captcha
     * @see        Models/Users_Model
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('bw_captcha');
        $this->load->model('users_model');

        $this->coin = $this->bw_config->currencies[0];

        if ($this->session->userdata('failed_login') == NULL)
            $this->session->set_userdata('failed_login', '0');

        $this->display_captcha = ($this->session->userdata('failed_login') > 1) ? TRUE : FALSE;
    }

    /**
     * Log user out.
     * URI: /logout
     *
     * @access    public
     * @see        Libraries/Bw_Session
     *
     * @return    void
     */
    public function logout()
    {
        $this->bw_session->destroy();
        redirect('login');
    }

    /**
     * Process user logins.
     * URI: /login/two_factor
     *
     * @access    public
     * @see        Models/Accounts_Model
     * @see        Models/Auth_Model
     * @see        Libraries/Form_Validation
     * @see        Libraries/GPG
     * @see        Libraries/Bw_Auth
     *
     * @return    void
     */
    public function login()
    {

        $this->load->model('accounts_model');

        if ($this->display_captcha)
            $this->form_validation->set_rules("captcha", "required|check_captcha");

        if ($this->form_validation->run('login_form') == TRUE) {
            $user_info = $this->users_model->get(array('user_name' => $this->input->post('user_name')));

            if ($user_info !== FALSE) {
                $password = $this->general->password($this->input->post('password'), $user_info['salt']);
                $check_login = $this->users_model->check_password($this->input->post('user_name'), $password);

                // Check the login went through OK.
                if ($check_login !== FALSE AND $check_login['id'] == $user_info['id']) {
                    $this->users_model->set_login($user_info['id']);

                    if(strlen($user_info['email_address']) > 0 AND $user_info['email_activated'] == '0') {
                        $this->current_user->set_return_message('You have not yet activated your email address. Please check your inbox, or '.anchor('activate/email', 'do it manually here'), 'warning');
                        redirect('login');
                    } else if ($user_info['banned'] == '1') {
                        // User is banned. Disallow.
                        $this->current_user->set_return_message("You have been banned from this site.",'warning');
                        redirect('login');
                    } else if ($user_info['user_role'] !== 'Admin' AND $this->bw_config->maintenance_mode == TRUE) {
                        // Maintainance mode active, but user isn't admin. Disallow.
                        $this->current_user->set_return_message("This site is down for maintenance, please check again later.");
                        redirect('login');
                    } else if ($user_info['entry_paid'] == '0') {
                        // Registration payment required. Direct to details.
                        $this->bw_session->create($user_info, 'entry_payment');
                        redirect('register/payment');
                    } else if ($user_info['totp_two_factor'] == '1') {
                        // Redirect to TOTP two factor page.
                        $this->bw_session->create($user_info, 'totp_factor');
                        redirect('login/totp_factor');
                    } else if ($user_info['pgp_two_factor'] == '1') {
                        // Redirect for two-factor authentication.
                        $this->bw_session->create($user_info, 'pgp_factor'); // TRUE, enables a half-session for two factor auth
                        redirect('login/pgp_factor');
                    } else if ($user_info['user_role'] == 'Vendor'
                        AND $this->bw_config->force_vendor_pgp == TRUE
                        AND $this->accounts_model->get_pgp_key($user_info['id']) == FALSE
                    ) {
                        // Redirect to register a PGP key.
                        $this->bw_session->create($user_info, 'force_pgp'); // enable a half-session where the user registers a PGP key.
                        redirect('register/pgp');
                    } else {
                        // Success! Log the user in.
                        $this->bw_session->create($user_info);
                        // Changed from redirect('/');
                        redirect('');
                    }
                }
            }

            $this->session->set_userdata('failed_login', ((int)$this->session->userdata('failed_login') + 1));
            $this->current_user->set_return_message("Your details were incorrect, try again.", 'warning');
            redirect('login');
        }

        $data['title'] = 'Login';
        $data['page'] = 'users/login';
        $data['action_page'] = 'login';
        $data['captcha'] = ($this->display_captcha) ? $this->bw_captcha->generate() : '';
        $data['display_captcha'] = $this->display_captcha;
        $this->_render('users/login', $data);

    }

    /**
     * Register new users on the system.
     *
     * If requested by admins users may need to:
     * - verify their email address
     * - enter a PGP key
     * before they can access their account.
     *
     * URI: /register
     *
     * @param    string /NULL    $token
     */
    public function register($token = NULL)
    {

        // If registration is disabled, and no token is set, direct to the login page.
        if (($this->bw_config->maintenance_mode == TRUE) OR ($this->bw_config->registration_allowed == FALSE && $token == NULL))
            redirect('login');

        // If a token is invalid, redirect to the register page.
        $data['token_info'] = $this->users_model->check_registration_token($token);
        if ($token !== NULL AND $data['token_info'] == FALSE)
            redirect('register');

        $this->load->library('openssl');
        $this->form_validation->set_error_delimiters('<span class="form-error">', '</span>');

        $data['terms_of_service'] = ($this->bw_config->terms_of_service_toggle == TRUE) ? $this->bw_config->terms_of_service : FALSE;
        $data['force_vendor_pgp'] = $this->bw_config->force_vendor_pgp;
        $data['encrypt_private_messages'] = $this->bw_config->encrypt_private_messages;
        $data['request_emails'] = $this->bw_config->request_emails;
        $data['vendor_registration_allowed'] = $this->bw_config->vendor_registration_allowed;
        $data['locations_select'] = $this->location_model->generate_select_list($this->bw_config->location_list_source, 'location', 'form-control');
        $data['currencies'] = $this->bw_config->currencies;

        // Different rules depending on whether a PIN must be entered.
        $register_page = ($data['encrypt_private_messages'] == TRUE) ? 'users/register' : 'users/register_no_pin';
        $register_validation = ($data['encrypt_private_messages'] == TRUE) ? 'register_form' : 'register_no_pin_form';

        if($this->bw_config->request_emails == TRUE)
            $this->form_validation->set_rules("email_address","email address","required|valid_email|is_unique[users.email_address]");

        if ($this->form_validation->run($register_validation) == TRUE) {
            $data['role'] = ($token == NULL) ? $this->general->role_from_id($this->input->post('user_type')) : $data['token_info']['user_type']['txt'];

            // Display an error if the user has not agreed to the terms of service.
            if ($data['terms_of_service'] !== FALSE && $this->input->post('tos_agree') !== '1') {
                $this->current_user->set_return_message('You must agree to the terms of service to register an account.','warning');
                redirect('register');
            }

            // If there's no token, the admin cannot register.
            if ($token == NULL && !in_array($data['role'], array('Buyer', 'Vendor'))) {
                $this->current_user->set_return_message('Please select a valid role.','warning');
                redirect('register');
            }

            $user_name = $this->input->post('user_name');
            $password = $this->general->new_password($this->input->post('password0'));

            // Generate OpenSSL keys for the users private messages.
            if ($data['encrypt_private_messages'] == TRUE) {
                $msg_password = $this->general->new_password($this->input->post('message_pin0'));
                $message_keys = $this->openssl->keypair($msg_password['hash']);
                $message_keys['private_key_salt'] = $msg_password['salt'];

            } else {
                $salt_only = $this->general->new_password($this->input->post('message_pin0'));
                // Set default values for the message keys.
                $message_keys = array('public_key' => '0',
                    'private_key' => '0',
                    'private_key_salt' => $salt_only['salt']);
            }

            // Generate a user hash.
            $user_hash = $this->general->unique_hash('users', 'user_hash');

            // Build the array for the model.
            $register_info = array('password' => $password['hash'],
                'location' => $this->input->post('location'),
                'register_time' => time(),
                'salt' => $password['salt'],
                'activation_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
                'activation_id' => bin2hex(openssl_random_pseudo_bytes(7)),
                'email_address' => ($this->bw_config->request_emails) ? $this->input->post('email_address') : '',
                'email_activated' => ($this->bw_config->request_emails) ? '0' : '1',
                'user_hash' => $user_hash,
                'user_name' => $user_name,
                'user_role' => $data['role'],
                'public_key' => $message_keys['public_key'],
                'private_key' => $message_keys['private_key'],
                'private_key_salt' => $message_keys['private_key_salt'],
                'wallet_salt' => $this->general->unique_hash('users', 'wallet_salt', '32'),
                'local_currency' => $this->input->post('local_currency'));

            $add_user = $this->users_model->add($register_info, $data['token_info']);

            // Check the submission
            if ($add_user) {
                if($this->bw_config->request_emails == TRUE){
                    $this->load->library('email');
                    $service_name = preg_replace("/^[\w]{2,6}:\/\/([\w\d\.\-]+).*$/", "$1", $this->config->slash_item('base_url'));

                    $this->email->from('do-not-reply@'.$service_name, $this->bw_config->site_title);
                    $this->email->to($this->input->post('email_address'));
                    $this->email->subject('Email Activation: '.$service_name);
$msg = "Thanks for joining {$this->bw_config->site_title}!\n\n
In order to activate your account, please visit the following link:\n"
.anchor('activate/email/'.$register_info['activation_id'].'/'.$register_info['activation_hash'], 'Activate your account')."\n".
"Alternatively, you can visit ".base_url('activate/email/'.$register_info['activation_id'].'/'.$register_info['activation_hash']).
"and manually verify by entering your email address, and the verification token below:\n".
"Token: {$register_info['activation_hash']}\n\nIf you didn't make this request, feel free to ignore it.";
                    $this->email->message($msg);
                    $this->email->send();
                }

                $this->load->model('bitcoin_model');
                $entry_fee = 'entry_payment_' . strtolower($data['role']);

                // User is registered. Work out if we charge a fee or let them login immediately.
                if ((isset($data['token_info']) AND $data['token_info'] !== FALSE AND $data['token_info']['entry_payment'] > 0)
                    OR (isset($this->bw_config->$entry_fee) AND $this->bw_config->$entry_fee > 0)
                ) {

                    // Create a fees address, and record the entry payment
                    $address = $this->bitcoin_model->get_fees_address($user_hash, $this->bw_config->currencies[0]['crypto_magic_byte']);
                    $amount = (is_array($data['token_info']) ? $data['token_info']['entry_payment'] : $this->bw_config->$entry_fee);
                    $info = array('user_hash' => $user_hash,
                        'amount' => $amount,
                        'bitcoin_address' => $address);
                    $this->users_model->set_entry_payment($info);

                    $message = "Your account has been created, but this site requires you pay an entry fee. Please send {$this->bw_config->currencies[0]['symbol']} {$amount} to {$address}. You can log in to view these details again.";
                    $message.= ($this->bw_config->request_emails == TRUE) ? " An email has been sent to your address with an activation link - you'll need to click this before you can access your account." : '';
                } else {
                    // Allow the user immediate access.
                    $this->users_model->set_entry_paid($user_hash);
                    $message = ($this->bw_config->request_emails)
                        ? "Your account has been created! You'll need to verify your email address by clicking the link we just sent"
                        : "Your account has been created, please login below: ";
                }
                $this->current_user->set_return_message($message,'success');
                redirect('login');
            } else {
                // Unsuccessful submission, show form again.
                $data['returnMessage'] = 'Your registration was unsuccessful, please try again.';
            }

        }

        $data['title'] = 'Register';
        $data['page'] = $register_page . ((isset($token) && $token !== NULL) ? '/' . $token : '');
        $data['token'] = $token;
        $data['captcha'] = $this->bw_captcha->generate();
        $data['display_captcha'] = $this->display_captcha;
        $this->_render($register_page, $data);

    }

    /**
     * Register PGP
     *
     * Force a user to import a PGP key before logging in fully. The admin
     * may decide all vendors need to adhere to this, so the key is
     * set on registration, and can only be replaced later on. These
     * users can never not have a PGP key.
     * URI: /register/pgp
     *
     * @access    public
     * @see        Models/Accounts_Model
     * @see        Libraries/Form_Validation
     * @see        Libraries/GPG
     *
     * @return    void
     */
    public function register_pgp()
    {
        if ($this->current_user->force_pgp !== TRUE)
            redirect('');

        $this->load->library('form_validation');
        $this->load->library('gpg');
        $this->load->model('accounts_model');

        $data['title'] = 'Add PGP Key';
        $data['page'] = 'users/register_pgp';

        if ($this->form_validation->run('add_pgp') == TRUE) {
            // Import the key, this will perform HTML entities and
            // extract the content between the two PGP headers.
            $key = $this->gpg->import($this->input->post('public_key'));

            if ($key !== FALSE) {
                $key = array('user_id' => $this->current_user->user_id,
                    'fingerprint' => $key['fingerprint'],
                    'public_key' => $key['clean_key']);

                if ($this->accounts_model->add_pgp_key($key) == TRUE) {
                    // Create full session
                    $user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
                    $this->bw_session->create($user_info);
                    redirect('');
                }
            }

            $data['returnMessage'] = 'Unable to import the supplied public key. Please ensure you are submitting an ASCII armored PGP public key.';
        }

        $this->_render($data['page'], $data);
    }

    /**
     * Payment
     *
     * If users are required to make a payment before they can register
     * on the site, they are redirected to this page to view the details
     * of what they have to pay.
     * URI: /register/payment
     *
     * @access    public
     */
    public function payment()
    {
        if ($this->current_user->entry_payment !== TRUE)
            redirect('');

        $this->load->model('accounts_model');
        $this->load->model('transaction_cache_model');

        $data['page'] = 'users/payment';
        $data['title'] = 'Entry Payment';
        $data['user'] = $this->users_model->get(array('id' => $this->current_user->user_id));
        $data['entry_payment'] = $this->users_model->get_entry_payment($data['user']['user_hash']);
        $data['payments'] = $this->transaction_cache_model->payments_to_address($data['entry_payment']['bitcoin_address']);
        $data['paid'] = 0.00000000;

        foreach ($data['payments'] as $tmp) {
            $data['paid'] += $tmp['value'];
        }

        // If no entry payment exists in the table for the registration,
        // or the user has been flagged as paid, redirect to the next page.
        if ($data['entry_payment'] == FALSE || $data['user']['entry_paid'] == '1') {
            if ($data['user']['user_role'] == 'Vendor'
                && $this->bw_config->force_vendor_pgp == TRUE
                && $this->accounts_model->get_pgp_key($data['user']['id']) == FALSE
            ) {
                // Redirect to register a PGP key.
                $this->session->unset_userdata('entry_payment');
                $this->bw_session->create($data['user'], 'force_pgp'); // enable a half-session where the user registers a PGP key.
                redirect('register/pgp');
            } else {
                // Log the user in fully.
                $this->session->unset_userdata('entry_payment');
                $this->bw_session->create($data['user']);
                redirect('');
            }
        }

        $this->_render($data['page'], $data);
    }

    /**
     * PGP Factor
     *
     * Process a two factor PGP authentication. A user is prompted to decrypt
     * a PGP encrypted challenge string. They must enter it correctly on
     * the first try, otherwise a new challenge is generated.
     * URI: /login/two_factor
     *
     * @return    void
     */
    public function pgp_factor()
    {
        // Abort if there is no two factor request.
        if ($this->current_user->pgp_factor !== TRUE)
            redirect('');

        $this->load->library('gpg');
        $this->load->library('bw_auth');
        $this->load->model('accounts_model');
        $this->load->model('auth_model');

        $data['title'] = 'Two Factor Authentication';
        $data['page'] = 'users/pgp_factor';

        if ($this->input->post('submit_pgp_token') == 'Continue') {
            if ($this->form_validation->run('submit_pgp_token') == TRUE) {
                // Check the answer to what we have on record as the solution.
                $answer = $this->input->post('answer');

                if ($this->auth_model->check_two_factor_token($answer) == TRUE) {
                    // If successful, create a full session and redirect to the homepage.
                    $user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
                    $this->bw_session->create($user_info);
                    redirect('');
                } else {
                    // Leave an error if the user has not been redirected.
                    $data['returnMessage'] = "Your token did not match. Please remove any whitespaces and enter only the token. A new challenge has been generated.";
                }
            }
        }

        // Generate a new challenge for new requests, or if a user has failed one.
        $data['challenge'] = $this->bw_auth->generate_two_factor_token();
        if ($data['challenge'] == FALSE)
            $this->logs_model->add('Two Factor Auth', 'Unable to generate two factor challenge', 'Unable to generate two factor authentication token.', 'Error');

        $this->_render($data['page'], $data);
    }

    /**
     * TOTP Factor
     * URI: /login/totp_factor
     *
     * Display a page for users to enter a TOTP token from their app.
     */
    public function totp_factor()
    {
        // Abort if there is no two factor request.
        if ($this->current_user->totp_factor !== TRUE)
            redirect('');

        $this->load->library('totp');
        $this->load->model('accounts_model');

        $data['user'] = $this->accounts_model->get(array('id' => $this->current_user->user_id), array('own' => TRUE));

        if ($this->input->post('submit_totp_token')) {
            if ($this->form_validation->run('submit_totp_token') == TRUE) {
                if ($this->totp->verifyCode($data['user']['totp_secret'], $this->input->post('totp_token'), 2)) {
                    $user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
                    $this->bw_session->create($user_info);
                    redirect('');
                } else {
                    $data['returnMessage'] = 'You entered an invalid token.';
                }
            }
        }

        $data['title'] = 'Two Factor Authentication';
        $data['page'] = 'users/totp_factor';
        $this->_render($data['page'], $data);
    }

}

;

/* End of file: Users.php */
/* Location: ./application/controllers/Users.php */
