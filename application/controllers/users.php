<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users Controller
 *
 * This class handles the buyer and vendor side of the order process.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Users
 * @author		BitWasp
 * 
 */
class Users extends CI_Controller {

	/**
	 * Constructor
	 * 
	 * Load libs/models.
	 *
	 * @access	public
	 * @see		Libraries/Bw_Captcha
	 * @see		Models/Users_Model
	 */ 
	public function __construct() {
		parent::__construct();
		$this->load->model('users_model');
		$this->load->library('bw_captcha');	
		$this->block_category_display = ($this->bw_config->allow_guests == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Log user out.
	 * URI: /logout
	 * 
	 * @access	public
	 * @see		Libraries/Bw_Session
	 * 
	 * @return	void
	 */
	public function logout() {
		$this->bw_session->destroy();
		redirect('login');
	}
	
	/**
	 * Process user logins.
	 * URI: /login/two_factor
	 * 
	 * @access	public
	 * @see		Models/Accounts_Model
	 * @see		Models/Auth_Model
	 * @see		Libraries/Form_Validation
	 * @see		Libraries/GPG
	 * @see		Libraries/Bw_Auth
	 * 
	 * @return	void
	 */
	public function login() {

		$data['header_meta'] = $this->load->view('users/login_hash_header', NULL, true);
		
		$this->load->helper(array('form'));
		$this->load->library('form_validation');
	
		if ($this->form_validation->run('login_form') == FALSE) {
			$data['title'] = 'Login';
			$data['page'] = 'users/login';
			$data['action_page'] = 'login';
			$data['captcha'] = $this->bw_captcha->generate();
		} else {
			$this->load->model('accounts_model');
			
			$user_name = $this->input->post('user_name');
			
			$user_info = $this->users_model->get(array('user_name' => $user_name));
			
			$data['returnMessage'] = "Your details were incorrect, try again.";
			
			if($user_info !== FALSE) {
				
				$password = ($this->input->post('js_disabled') == '1') ? $this->general->hash($this->input->post('password')) : $this->input->post('password');
				$password = $this->general->password($password, $user_info['salt']);
				
				$check_login = $this->users_model->check_password($user_name, $password);

				// Check the login went through OK.
				if( ($check_login !== FALSE) && ($check_login['id'] == $user_info['id']) ) {
					$this->users_model->set_login($user_info['id']);

					// Check if the user is banned.
					if($user_info['banned'] == '1') {
						$data['returnMessage'] = "You have been banned from this site.";
						
					} else if($user_info['user_role'] !== 'Admin' && $this->bw_config->maintenance_mode == TRUE) {
						$data['returnMessage'] = "The site is in maintenance mode, please try again later.";
						
					} else if($user_info['entry_paid'] == '0') {
						// Redirect to payment details.
						$this->bw_session->create($user_info, 'entry_payment');
						redirect('register/payment');
						
					} else if($user_info['two_factor_auth'] == '1') {
						// Redirect for two-factor authentication.
						$this->bw_session->create($user_info, 'two_factor');	// TRUE, enables a half-session for two factor auth
						redirect('login/two_factor');
						
					} elseif ($user_info['user_role'] == 'Vendor' 
						&& $this->bw_config->force_vendor_pgp == TRUE
						&& $this->accounts_model->get_pgp_key($user_info['id']) == FALSE) {
							
						// Redirect to register a PGP key.
						$this->bw_session->create($user_info, 'force_pgp');	// enable a half-session where the user registers a PGP key.
						redirect('register/pgp');
					
					} else {
						// Success! Log the user in.
						$this->bw_session->create($user_info);
						redirect('/');
					}
				} 
			}
			// If not already redirected... details were incorrect.
			$data['title'] = 'Login';
			$data['page'] = 'users/login';
			$data['captcha'] = $this->bw_captcha->generate();
		}
		$this->load->library('Layout',$data);
 
	}
	
	/**
	 * Register new users on the system.
	 * URI: /register
	 * 
	 * @access	public
	 * @see		Model/User_Model
	 * @see		Models/Currencies_Model
	 * @see		Libraries/Form_Validation
	 * @see		Libraries/OpenSSL
	 * @see		Libraries/Bw_Bitcoin
	 * @see		Libraries/Bw_Auth
	 * 
	 * @param 	string/NULL
	 * @return	void
	 */
	public function register($token = NULL) {

		$data['header_meta'] = $this->load->view('users/register_hash_header', NULL, true);

		// If registration is disabled, and no token is set, direct to the login page.
		if($this->bw_config->maintenance_mode == TRUE || $this->bw_config->registration_allowed == FALSE && $token == NULL)
			redirect('login');
			
		// If a token is invalid, redirect to the register page.
		$data['token_info'] = $this->users_model->check_registration_token($token);
		if($token !== NULL && $data['token_info'] == FALSE)
			redirect('register');
			
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('bw_bitcoin');
		$this->load->library('openssl');
		$this->load->model('currencies_model');
		
		$this->form_validation->set_error_delimiters('<span class="form-error">', '</span>');
		
		$data['terms_of_service'] = ($this->bw_config->terms_of_service_toggle == TRUE) ? $this->bw_config->terms_of_service : FALSE;
		$data['force_vendor_pgp'] = $this->bw_config->force_vendor_pgp;
		$data['encrypt_private_messages'] = $this->bw_config->encrypt_private_messages;
		$data['vendor_registration_allowed'] = $this->bw_config->vendor_registration_allowed;
		$data['locations'] = $this->general_model->locations_list();
		$data['currencies'] = $this->currencies_model->get();
		
		// Different rules depending on whether a PIN must be entered.		
		$register_page = ($data['encrypt_private_messages'] == TRUE) ? 'users/register' : 'users/register_no_pin';
		$register_validation = ($data['encrypt_private_messages'] == TRUE) ? 'register_form' : 'register_no_pin_form';
		
		$data['role'] = ($token == NULL) ? $this->general->role_from_id($this->input->post('user_type')) : $data['token_info']['user_type']['txt'];
		
		
		// If there is any information about a recent transaction, display it.
		$info = (array)json_decode($this->session->flashdata('info'));
		if(count($info) !== 0){
			// If the information is to do with topping up a WIF key:
			if($info['action'] == 'tos_fail')
				$data['returnMessage'] = "You must agree to the terms of service to register an account.";
		}

		
		// Check if we need the message_pin form, or the other one!
		if ($this->form_validation->run($register_validation) == FALSE) {
			// Show the register form.
			$data['title'] = 'Register';
            $data['page'] = $register_page; 
            $data['token'] = $token;
			$data['captcha'] = $this->bw_captcha->generate();
			
		} else {
			// Display an error if the user has not agreed to the terms of service.
			if($data['terms_of_service'] !== FALSE && $this->input->post('tos_agree') !== '1') {
				$info = json_encode(array('action' => 'tos_fail'));
				$this->session->set_flashdata('info', $info);
				redirect('register');
			}
			
			// If there's no token, the admin cannot register.
			if($token == NULL && !$this->general->matches_any($data['role'], array('Buyer','Vendor'))) {
				$data['title'] = 'Register';
				$data['page'] = $register_page;
				$data['token'] = $token;
				$data['captcha'] = $this->bw_captcha->generate();
				$data['returnMessage'] = 'Please select a valid role.';
			} else {
			
				$password = ($this->input->post('js_disabled') == '1') ? $this->general->hash($this->input->post('password0')) : $this->input->post('password0');
				// Generate the users salt and hashed password.
				$salt = $this->general->generate_salt();
				$password = $this->general->password($password, $salt);
				
				$user_name = $this->input->post('user_name');
				
				// Generate OpenSSL keys for the users private messages.	
				if($data['encrypt_private_messages'] == TRUE) {
					$pin = $this->input->post('message_pin0');
					$message_password = $this->general->password($this->input->post('message_pin0'), $salt);
					$message_keys = $this->openssl->keypair($message_password);
					unset($message_password);
				
				} else {
					// Set default values for the message keys.
					$message_keys = array(	'public_key' => '0',
											'private_key' => '0');
				}	

				// Generate a user hash.
				$user_hash = $this->general->unique_hash('users', 'user_hash');

				// Build the array for the model.
				$register_info = array(	'password' => $password,
										'location' => $this->input->post('location'),
										'register_time' => time(),
										'salt' => $salt,
										'user_hash' => $user_hash,
										'user_name' => $user_name,
										'user_role' => $data['role'],
										'public_key' => $message_keys['public_key'],
										'private_key' => $message_keys['private_key'],
										'local_currency' => $this->input->post('local_currency') );		
				
				$add_user = $this->users_model->add($register_info, $data['token_info']);
				
				// Check the submission
				if($add_user) {
		
					// Registration successful, show login page.
					$data['title'] = 'Registration Successful';
					$data['page'] = 'users/login';
					$data['action_page'] = 'login';
					$data['header_meta'] = $this->load->view('users/login_hash_header', NULL, true);
					$data['captcha'] = $this->bw_captcha->generate();

					$entry_fee = 'entry_payment_'.strtolower($data['role']);
					
					if(isset($data['token_info']) && $data['token_info'] !== FALSE) {
						if($data['token_info']['entry_payment'] > 0) {
							$address = $this->bw_bitcoin->new_fees_address();
							$entry_fee_amount = $data['token_info']['entry_payment'];
							$info = array(	'user_hash' => $user_hash,
											'amount' => $data['token_info']['entry_payment'],
											'bitcoin_address' => $address);
							if($this->users_model->set_entry_payment($info) == TRUE) {
									
								if($address == NULL) {
									$data['returnMessage'] = "Your account has been created, but this site requires you pay an entry fee of BTC $entry_fee_amount. Currently the bitcoin daemon is offline, and no receiving addresses can be generated. Please try log in later for details.";
								} else {
									$data['returnMessage'] = "Your account has been created, but this site requires you pay an entry fee. Please send BTC $entry_fee_amount to $address. <br /><br />You can log in to view these details again, but will not gain full access until the fee is paid.";
								}
							}
						} else {
							if($this->users_model->set_entry_paid($user_hash))
								$data['returnMessage'] = 'Your account has been created, please login below.';
						}
					} else if(isset($this->bw_config->$entry_fee) && $this->bw_config->$entry_fee > 0) {
						$address = $this->bw_bitcoin->new_fees_address();
						$entry_fee = $this->bw_config->$entry_fee;
						$info = array(	'user_hash' => $user_hash,
										'amount' => $entry_fee,
										'bitcoin_address' => $address );
						if($this->users_model->set_entry_payment($info) == TRUE) {
							// Work out which return message to display.
							// $address == NULL: bitcoind offline.
							// Otherwise display the normal message.
							$data['returnMessage'] = ($address == NULL) ? "Your account has been created, but this site requires you pay an entry fee of BTC $entry_fee. Currently the bitcoin daemon is offline, and no receiving addresses can be generated. Please try log in later for details." : "Your account has been created, but this site requires you pay an entry fee. Please send BTC $entry_fee to $address. <br /><br />You can log in to view these details again, but will not gain full access until the fee is paid.";
						}
					} else {
						if($this->users_model->set_entry_paid($user_hash))
							$data['returnMessage'] = 'Your account has been created, please login below.';
					}
					
					if($this->bw_bitcoin->new_address($user_hash) == FALSE)
						$this->logs_model->add('User Registration', 'Unable to create bitcoin topup address', 'It was not possible to create a bitcoin address when creating an account.', 'Warning');

					// REMOVE BEFORE PRODUCTION
					$this->load->model('bitcoin_model');
					if($data['role'] == 'Buyer') {
						$credit = array('user_hash' => $user_hash,
										'value' => (float)0.03333333);
						$this->bitcoin_model->update_credits(array($credit));
					}
					
				} else {
					// Unsuccessful submission, show form again.
					$data['title'] = 'Register';
					$data['returnMessage'] = 'Your registration was unsuccessful, please try again.';
					$data['page'] = $register_page; 
					$data['token'] = $token;
					$data['captcha'] = $this->bw_captcha->generate();
					
				}
			}
		}
		
		$this->load->library('Layout',$data); 
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
	 * @access	public
	 * @see		Models/Accounts_Model
	 * @see		Libraries/Form_Validation
	 * @see		Libraries/GPG
	 * 
	 * @return	void
	 */
	public function register_pgp() {
		if($this->current_user->force_pgp !== TRUE) 
			redirect('');
				
		$this->load->library('form_validation');
		$this->load->library('gpg');
		$this->load->model('accounts_model');
		
		$data['title'] = 'Add PGP Key';
		$data['page'] = 'users/register_pgp';
		
		if($this->form_validation->run('add_pgp') == TRUE) {
			// Import the key, this will perform HTML entities and 
			// extract the content between the two PGP headers.
			$public_key = $this->input->post('public_key');
			$key = $this->gpg->import($public_key);
			
			if($key !== FALSE) {
				
				$key = array('user_id' => $this->current_user->user_id,
							 'fingerprint' => $key['fingerprint'],
							 'public_key' => $key['clean_key']);
							 
				if($this->accounts_model->add_pgp_key($key) == TRUE) {
					// Create full session
					$user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
					$this->bw_session->create($user_info);
					redirect('');
				}
			}
			
			$data['returnMessage'] = 'Unable to import the supplied public key. Please ensure you are submitting an ASCII armored PGP public key.';
		}
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Payment
	 * 
	 * If users are required to make a payment before they can register
	 * on the site, they are redirected to this page to view the details
	 * of what they have to pay.
	 * URI: /register/payment
	 * 
	 * @access	public
	 */
	 public function payment() {
		if($this->current_user->entry_payment !== TRUE)
			redirect('');

		$this->load->library('bw_bitcoin');
		$this->load->model('accounts_model');
		
		$data['user'] = $this->users_model->get(array('id' => $this->current_user->user_id));
		$data['entry_payment'] = $this->users_model->get_entry_payment($data['user']['user_hash']);

		// If no entry payment exists in the table for the registration,
		// or the user has been flagged as paid, redirect to the next page.
		if($data['entry_payment'] == FALSE || $data['user']['entry_paid'] == '1') {
			if ($data['user']['user_role'] == 'Vendor' 
				&& $this->bw_config->force_vendor_pgp == TRUE
				&& $this->accounts_model->get_pgp_key($data['user']['id']) == FALSE) {
						
				// Redirect to register a PGP key.
				$this->session->unset_userdata('entry_payment');
				$this->bw_session->create($data['user'], 'force_pgp');	// enable a half-session where the user registers a PGP key.
				redirect('register/pgp');
				
			} else {
				// Log the user in fully.
				$this->session->unset_userdata('entry_payment');
				$this->bw_session->create($data['user']);
				redirect('');
			}	
		}

		// Payment is still not completed, but the bitcoin address is not set.
		if($data['entry_payment'] !== FALSE && $data['entry_payment']['bitcoin_address'] == '0') {
			// Try to generate another.
			$payment_address = $this->bw_bitcoin->new_fees_address();
			if($payment_address !== NULL) {
				
				if($this->users_model->set_payment_address($data['user']['user_hash'], $payment_address) == TRUE) {
					// If we add the bitcoin address, show the user the details to make payment.
					$data['entry_payment']['bitcoin_address'] = $payment_address;
					$data['entry_payment']['received'] = $this->bw_bitcoin->getreceivedbyaddress($payment_address);
					$data['returnMessage'] = "We thank you for registering on our site. In order to complete setting up your account, you must make a payment of BTC {$data['entry_payment']['amount']}. This can be sent to {$data['entry_payment']['bitcoin_address']}. Click refresh one you have made the payment to check for receipt."; 
				} else {
					// Otherwise tell them bitcoind is still down.
					$this->logs_model->add('User Registration', 'Error adding fee payment adress', 'It was not possible to record a fee payment address at registration.','Error');
					$data['returnMessage'] = "We thank you for registering on our site. In order to complete setting up your account, you must make a payment of BTC {$data['entry_payment']['amount']}. Unfortunately, it was not possible to create a fee's address at this time. Please try again later to check for the payment address.";
				}
			} else {
				$this->logs_model->add('User Registration', 'Bitcoind down at registration', 'User signed in to view payment details, bitcoind was unable to create a payment address for the fees', 'Warning');
				$data['returnMessage'] = "We thank you for registering on our site. In order to complete setting up your account, you must make a payment of BTC {$data['entry_payment']['amount']}. Unfortunately, the bitcoin processing system is unavailable at this time. Please try again later to check for the payment address.";
			}
		} else if($data['entry_payment'] !== FALSE) {
			$data['entry_payment']['received'] = $this->bw_bitcoin->getreceivedbyaddress($data['entry_payment']['bitcoin_address']);
			$data['returnMessage'] = "We thank you for registering on our site. In order to complete setting up your account, you must make a payment of BTC {$data['entry_payment']['amount']}. This can be sent to {$data['entry_payment']['bitcoin_address']}. Click refresh once you have made the payment to check for receipt."; 	
		}
				
		$data['title'] = 'Entry Payment';
		$data['page'] = 'users/payment';
		
		$this->load->library('Layout', $data);	

	 }
	
	/**
	 * Two Factor
	 * 
	 * Process a two factor PGP authentication. A user is prompted to decrypt
	 * a PGP encrypted challenge string. They must enter it correctly on
	 * the first try, otherwise a new challenge is generated.
	 * URI: /login/two_factor
	 * 
	 * @access	public
	 * @see		Models/Accounts_Model
	 * @see		Models/Auth_Model
	 * @see		Libraries/Form_Validation
	 * @see		Libraries/GPG
	 * @see		Libraries/Bw_Auth
	 * 
	 * @return	void
	 */
	public function two_factor() {
		// Abort if there is no two factor request.
		if($this->current_user->two_factor !== TRUE) 
			redirect('');
				
		$this->load->library('form_validation');
		$this->load->library('gpg');
		$this->load->library('bw_auth');
		$this->load->model('accounts_model');
		$this->load->model('auth_model');
		
		$data['title'] = 'Two Factor Authentication';
		$data['page'] = 'users/two_factor';
		
		if($this->form_validation->run('two_factor') == TRUE) {
			// Check the answer to what we have on record as the solution.
			$answer = $this->input->post('answer');
			
			if($this->auth_model->check_two_factor_token($answer) == TRUE) {
				// If successful, create a full session and redirect to the homepage.
				$user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
				$this->bw_session->create($user_info);
				redirect('');
			} else {
				// Leave an error if the user has not been redirected.
				$data['returnMessage'] = "Your token did not match. Please remove any whitespaces and enter only the token. A new challenge has been generated.";
			}			
		} 
		
		// Generate a new challenge for new requests, or if a user 
		// has failed one.
		$data['challenge'] = $this->bw_auth->generate_two_factor_token();
		if($data['challenge'] == FALSE)
			$this->logs_model->add('Two Factor Auth','Unable to generate two factor challenge','Unable to generate two factor authentication token.','Error');
		
		$this->load->library('Layout', $data);
	}
	
	// Callback functions for for validation.
	
	/**
	 * Check Captcha
	 * 
	 * Check the supplied captcha solution ($param) is correct.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */	
	public function check_captcha($param) {
		return $this->bw_captcha->check($param);
	}
	
	/**
	 * Check Role
	 * 
	 * Check the supplied role ID ($param) is allowed.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */	
	public function check_role($param) {
		$allowed_values = ($this->bw_config->vendor_registration_allowed) ? array('1','2','3') : array('1','3');	
		return ($this->general->matches_any($param, $allowed_values)) ? TRUE : FALSE;
	}
	
	/**
	 * Check Valid Currency
	 * 
	 * Check if the supplied currency ID ($param) exists.
	 *
	 * @param	int	$param
	 * @return	boolean
	 */
	public function check_valid_currency($param) {
		$this->load->model('currencies_model');
		return ($this->currencies_model->get($param) !== FALSE) ? TRUE : FALSE;
	}
	
	/**
	 * Check Location
	 * 
	 * Check the supplied location ID ($param) exists.
	 *
	 * @param	id	$param
	 * @return	boolean
	 */
	public function check_location($param) {
		return ($this->general_model->location_by_id($param) == TRUE) ? TRUE : FALSE;
	}
};
