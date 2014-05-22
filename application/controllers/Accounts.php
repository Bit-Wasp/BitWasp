<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use BitWasp\BitcoinLib\BitcoinLib;

/**
 * Accounts Management Controller
 *
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Accounts
 * @author		BitWasp
 */
class Accounts extends CI_Controller
{

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('gpg');
		$this->load->model('accounts_model');
		$this->load->model('location_model');
	}

	/**
	 * View a users profile
	 * URI: /user/$hash
	 * 
	 * Users can load a public profile of other users. If the 
	 * Accounts_Model\get() returns FALSE, the requested account does not 
	 * exist, and the user is redirected to the homepage. Otherwise,
	 * the specified view is loaded into the Layout class.
	 * 
	 * @param	string	$hash
	 */
	public function view($hash)
	{
		// Load the specified user, redirect if they don't exist.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $hash));
		if ($data['user'] == FALSE)
			redirect('');

		$this->load->model('items_model');
		$this->load->model('review_model');
	
		// Load information for the view.
		$data['page'] = 'accounts/view';
		$data['title'] = $data['user']['user_name'];
		$data['logged_in'] = $this->current_user->logged_in();
		$data['user_role'] = $this->current_user->user_role;

		$data['reviews'] = $this->review_model->random_latest_reviews(8, 'user', $data['user']['user_hash']);
		$data['review_count']['all'] = $this->review_model->count_reviews('user', $data['user']['user_hash']);
		$data['review_count']['positive'] = $this->review_model->count_reviews('user', $data['user']['user_hash'], 0);
		$data['review_count']['disputed'] = $this->review_model->count_reviews('user', $data['user']['user_hash'], 1);
		$data['average_rating'] = $this->review_model->current_rating('user', $hash);	

		if ($data['user']['user_role'] == 'Vendor')
			$data['items'] = $this->items_model->get_list(array('vendor_hash' => $data['user']['user_hash']));
		
		$this->load->library('Layout', $data);
	}

	/**
	 * Public Keys
	 * URI: /account/public_keys
	 * 
	 * This page allows vendors to add a list of public keys for use in 
	 * orders.
	 */
	public function public_keys()
	{
		if ( $this->current_user->user_role !== 'Vendor')
			redirect('');
		
		if ( $this->input->post('submit_public_keys') == 'Upload Public Keys')
		{
			$keys = explode("\n",$this->input->post('public_key_list'));
			
			foreach ($keys as $key)
			{
				$key = trim($key);
				if (BitcoinLib::validate_public_key($key) == TRUE)
					$this->accounts_model->add_bitcoin_public_key($key);
			}
			redirect('accounts/public_keys');
		}
		
		$data['page'] = 'accounts/vendor_public_keys';
		$data['title'] = 'Bitcoin Public Keys';		
		$data['available_public_keys'] = $this->accounts_model->bitcoin_public_keys($this->current_user->user_id);
		$this->load->library('Layout', $data);
	}

	/**
	 * View own user profile
	 * URI: /account
	 * 
	 * A user can view their own account settings. Accounts_Model\get is called
	 * but this time, an additional option is set to confirm it's the 
	 * users own account, and additional info besides the norm should be
	 * loaded from the database. The data is then sent to the Layout class 
	 * to be displayed.
	 *
	 * @access	public
	 * @return	void
	 */
	public function me()
	{
		$data['page'] = 'accounts/me';
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		$data['title'] = $data['user']['user_name'];
		
		// Load profile from the current_user object. 
		if ($this->current_user->user_role == 'Vendor')
		{
			$keys = $this->accounts_model->bitcoin_public_keys($this->current_user->user_id);
			$data['public_key_count'] = ($keys == FALSE) ? '0' : count($keys);
		}

		// 2FA settings
		$data['two_factor']['totp'] = ($data['user']['totp_two_factor'] == '1') ? TRUE : FALSE;
		if (isset($data['user']['pgp'])) 
			$data['two_factor']['pgp'] = ($data['user']['pgp_two_factor'] == '1') ? TRUE : FALSE;
			
		$data['two_factor_setting'] = FALSE;
		foreach ($data['two_factor'] as $factor)
		{
			$data['two_factor_setting'] = $data['two_factor_setting'] || $factor;
		}
		
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Edit own account settings
	 * URI: /account/edit
	 * 
	 * Users can alter their account settings. They may choose to alter
	 * their local currency, their current location, whether their login
	 * activity is displayed, (and their PGP fingerprint if they have it,
	 * along with options for Two-Factor Auth and Forced PGP Messages).
	 * Users may be forced to replace their PGP key, instead of delete it.
	 * This is done in the view by using $data['option_replace_pgp'] = TRUE.
	 * 
	 * Different Form_Validation rules are chosen based on whether the
	 * user has a PGP key currently set up on their account. Once past
	 * form validation, we compare POSTed values to what we have in the
	 * database, filter unchanged entries, and update if there is anything
	 * to update.
	 * 
	 * Redirect on success, or display an error.
	 * 
	 * @access	public
	 * @see 	Models/Accounts_model
	 * @see 	Models/Currencies_model
	 * @see 	Models/General_model
	 * @return	void
	 */	
	public function edit()
	{
		$this->load->model('location_model');
		
		$data['page'] = 'accounts/edit';
		$data['title'] = 'Account Settings';
		
		// Load own user profile.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		$data['currencies'] = $this->bw_config->currencies;	
		$data['location_select'] = $this->location_model->generate_select_list($this->bw_config->location_list_source, 'location', 'span5', $data['user']['location']);		

		// Check if the user is forced to user PGP. If so, display the 'Replace' link instead of 'Delete'
		$data['option_replace_pgp'] = FALSE;
		if ($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
		||  $this->current_user->user_role == 'Admin')
			$data['option_replace_pgp'] = TRUE;
			
		// Different form validation rules depending on if the user has a PGP key uploaded.
		$form_rules = (isset($data['user']['pgp']) == TRUE) ? 'account_edit' : 'account_edit_no_pgp';
		
		// If form validation is successful, update the changes.
		if ($this->form_validation->run($form_rules) == TRUE)
		{
			$changes = array();
			
			// Compare POSTed values to the original, remove any NULL entries.
			$changes['location'] = ($data['user']['location'] == $this->input->post('location')) ? NULL : $this->input->post('location');
			$changes['display_login_time'] = ($data['user']['display_login_time'] == $this->input->post('display_login_time')) ? NULL : $this->input->post('display_login_time');
			$changes['local_currency'] = ($data['user']['currency'] == $this->input->post('local_currency')) ? NULL : $this->input->post('local_currency');				
			
			// Only consider these if the user has a PGP key uploaded.
			if (isset($data['user']['pgp']))
			{
				$changes['force_pgp_messages'] = ($data['user']['force_pgp_messages'] == $this->input->post('force_pgp_messages')) ? NULL : $this->input->post('force_pgp_messages');
				$changes['block_non_pgp'] = ($data['user']['block_non_pgp'] == $this->input->post('block_non_pgp')) ? NULL : $this->input->post('block_non_pgp');
			}
			$changes = array_filter($changes, 'strlen');

			if (count($changes) > 0)
			{
				// If there are changes, set an error message for if the update fails (and user is not redirected).
				$data['returnMessage'] = 'Unable to save your changes, please try again later.';
				if($this->accounts_model->update($changes) == TRUE) 
					redirect('account');
			}
		}
		
		$this->load->library('Layout', $data);
	}

	/**
	 * Disable 2FA
	 * URI: /account/disable_2fa
	 * 
	 * This page allows users to disable the current two factor setting
	 * on their account after passing the correct challenge.
	 */
	public function disable_2fa()
	{
		$data = array();
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		$data['two_factor']['totp'] = ($data['user']['totp_two_factor'] == '1') ? TRUE : FALSE;
		if (isset($data['user']['pgp'])) 
			$data['two_factor']['pgp'] = ($data['user']['pgp_two_factor'] == '1') ? TRUE : FALSE;
		
		// Work out 2FA is enabled at all.
		$data['two_factor_setting'] = FALSE;
		foreach ($data['two_factor'] as $factor)
		{
			$data['two_factor_setting'] = $data['two_factor_setting'] || $factor;
		}

		// If two factor is not enabled..
		if ($data['two_factor_setting'] == FALSE) 
			redirect('account/two_factor');
		
		if ($data['two_factor']['totp'] == TRUE)
		{
			// If TOTP is enabled, allow the user to disable it.
			$this->load->library('totp');
			if ($this->input->post('disable_totp') == 'Continue')
			{
				if ($this->form_validation->run('submit_totp_token') == TRUE)
				{
					$check = $this->totp->verifyCode($data['user']['totp_secret'], $this->input->post('totp_token'), 2);
					if ($check)
					{
						$this->accounts_model->disable_2fa_totp();
						$this->session->userdata('message', json_encode(array('message' => 'App-based two factor authentication has been <b>disabled</b>.')));
						redirect('account/two_factor');
					}
					else
					{
						$data['returnMessage'] = 'You entered an invalid token!';
						$new_qr = FALSE;
					}
				}
			}
			
			$data['page'] = 'accounts/disable_totp_factor';
		}
		else
		{
			// If PGP two factor is enabled, allow it to be disabled.
			$this->load->library('bw_auth');
			$this->load->model('auth_model');
			
			if ($this->input->post('disable_pgp') == 'Continue')
			{
				if ($this->form_validation->run('submit_pgp_token') == TRUE)
				{
					// Check the answer to what we have on record as the solution.
					if ($this->auth_model->check_two_factor_token($this->input->post('answer')) == TRUE)
					{
						$this->accounts_model->disable_2fa_pgp();
						$this->session->set_userdata('returnMessage', json_encode(array('message' => 'PGP two factor authentication has been <b>disabled</b>.')));
						redirect('accounts/two_factor');
					}
					else
					{
						// Leave an error if the user has not been redirected.
						$data['returnMessage'] = "Your token did not match. Please remove any whitespaces and enter only the token. A new challenge has been generated.";
					}			
				} 
			}
			
			$data['challenge'] = $this->bw_auth->generate_two_factor_token();
			if($data['challenge'] == FALSE)
				$this->logs_model->add('Two Factor Auth','Unable to generate two factor challenge','Unable to generate two factor authentication token.','Error');
			
			$data['page'] = 'accounts/disable_pgp_factor';
		}
		
		$data['title'] = 'Disable Two Factor Authentication';
		$this->load->library('Layout', $data);
		
	}

	/**
	 * Enable PGP Factor
	 * 
	 * This page generates an encrypted challenge for the user to decrypt
	 * and paste to the site before enabling PGP 2fa. 
	 */
	public function enable_pgp_factor()
	{
		$this->load->library('bw_auth');
		$this->load->model('auth_model');

		$data['title'] = 'Enable PGP Two Factor Authentication';
		$data['page'] = 'accounts/enable_pgp_factor';
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));

		if ($this->input->post('submit_pgp_token') == 'Continue')
		{ 
			if ($this->form_validation->run('submit_pgp_token') == TRUE)
			{
				// Check the answer to what we have on record as the solution.
				if ($this->auth_model->check_two_factor_token($this->input->post('answer')) == TRUE)
				{
					$this->accounts_model->enable_2fa_pgp();
					$this->session->set_userdata('returnMessage', json_encode(array('message' => 'PGP two factor authentication has been enabled.')));
					redirect('accounts/two_factor');
				}
				else
				{
					// Leave an error if the user has not been redirected.
					$data['returnMessage'] = "Your token did not match. Please remove any whitespaces and enter only the token. A new challenge has been generated.";
				}			
			}
		}

		$data['challenge'] = $this->bw_auth->generate_two_factor_token();
		if($data['challenge'] == FALSE)
			$this->logs_model->add('Two Factor Auth','Unable to generate two factor challenge','Unable to generate two factor authentication token.','Error');

		$this->load->library('Layout', $data);
	}
	

	/**
	 * Two Factor Panel
	 * 
	 * Allow users to set up TOTP or PGP two factor authentication.
	 * 
	 */
	public function two_factor()
	{
		$this->load->library('totp');
		$this->load->model('users_model');
		
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		$data['two_factor']['totp'] = ($data['user']['totp_two_factor'] == '1') ? TRUE : FALSE;
		$data['two_factor']['pgp'] = ($data['user']['pgp_two_factor'] == '1') ? TRUE : FALSE;

		$new_qr = TRUE;

		// Process TOTP - PGP is done in enabe_pgp_factor.
		if ($this->input->post('submit_totp_token') == 'Setup')
		{
			// If PGP is enabled, they can't enable TOTP. 
			if($data['two_factor']['pgp'] == TRUE)
			{
				$data['returnMessage'] = 'You must disable PGP authentication before enabling app-based two factor authentication.';
			}
			else
			{
				$user_info = $this->users_model->get(array('id' => $this->current_user->user_id));	
				$this->form_validation->set_rules("password", "your password", "required");
				$this->form_validation->set_rules("totp_token", "a valid token", "required");
				
				if ($this->form_validation->run() == TRUE)
				{
					// Work out if submitted password has been hashed by javascript already
					$password = ($this->input->post('js_disabled') == '1') ? $this->general->hash($this->input->post('password')) : $this->input->post('password');
					$password = $this->general->password($password, $user_info['salt']);
					
					$check_login = $this->users_model->check_password($this->current_user->user_name, $password);

					if( $check_login !== FALSE && $check_login['id'] == $data['user']['id'] )
					{
						if ($this->form_validation->run('submit_totp_token') == TRUE)
						{
							if ($this->totp->verifyCode($this->session->userdata('otp_secret'), $this->input->post('totp_token'), 2))
							{
								$this->accounts_model->enable_2fa_totp($this->session->userdata('otp_secret'));
								$this->session->unset_userdata('otp_secret');
								$this->session->userdata('message', json_encode(array('message' => 'App-based two factor authentication has been enabled.')));
								redirect('account/two_factor');
							}
							else
							{
								$data['returnMessage'] = 'You entered an invalid token. Try again, or '.anchor('accounts/two_factor','click here to load a new secret.');
								$new_qr = FALSE;
							}
						}
					}
					else
					{
						$data['returnMessage'] = 'Your password was incorrect.';
						$new_qr = FALSE;
					}
				}
			}
		}

		// If TOTP is disabled, show a QR to set up.
		if ($data['two_factor']['totp'] == FALSE)
		{
			$this->load->library('ciqrcode', array('cacheable' => FALSE));
			if ($new_qr == FALSE)
			{
				$data['secret'] = $this->session->userdata('otp_secret');
			}
			else
			{
				$data['secret'] = $this->totp->createSecret();
				$this->session->set_userdata('otp_secret', $data['secret']);
			}
			
			$url = $this->totp->getOTPLink("{$this->current_user->user_name} @ Bitwasp", $data['secret']);
			$data['qr'] = $this->ciqrcode->generate_base64(array('data' => $url));
		}
		
		// Work out if it's on at all.
		$data['two_factor_setting'] = FALSE;
		foreach ($data['two_factor'] as $factor)
		{
			$data['two_factor_setting'] = $data['two_factor_setting'] || $factor;
		}

		$data['page'] = 'accounts/two_factor';
		$data['title'] = 'Two Factor Authentication';
		$data['header_meta'] = $this->load->view('accounts/password_hash_header', NULL, true);
		$this->load->library('Layout', $data);
	}

	/**
	 * Delete PGP key from account.
	 * URI: /pgp/delete
	 * 
	 * User will be redirected if they are forced to replace instead of delete.
	 * If the user has no PGP key, they will get redirected also. If the
	 * form validation is successful, delete the PGP key. Even if we didn't
	 * delete the key, redirect the accounts page.
	 * 
	 * @access	public 
	 * @see 	Libraries/Form_Validation
	 * @see 	Libraries/GPG
	 * @see 	Models/Accounts_model
	 * @return	void
	 */
	public function delete_pgp()
	{
		// If a user is forced to have a PGP key, they must replace it instead.
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
		|| $this->current_user->user_role == 'Admin')
			redirect('pgp/replace');
		
		// Load own account information.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
		// If there is no PGP key, redirect to the accounts page.
		if ( ! isset($data['user']['pgp']))
			redirect('account');
		
		if ($this->form_validation->run('delete_pgp') === TRUE)
		{
			if ($this->input->post('delete') == '1' && $this->accounts_model->delete_pgp_key($data['user']['id']) == TRUE)
			{
				// Put a message here, and failure message after.
			} 
			redirect('account');
		}
		
		$data['page'] = 'accounts/delete_pgp';
		$data['title'] = 'Delete PGP Key';
		
		$this->load->library('Layout', $data);
	}

	/**
	 * Replace current PGP key. 
	 * URI: /pgp/replace
	 * 
	 * Sometimes called instead of pgp/delete (as the user might be forced
	 * to have a PGP key at all times). The new key is imported using the GPG\import
	 * function. This function strips out the PGP key from the input, and performs
	 * HTMlentities on the remaining key if it passes validation. 
	 * If replacing the key is successful, redirect, otherwise display
	 * an error.
	 * 
	 * @access	public
	 * @see 	Libraries/Gpg
	 * @see 	Libraries/GPG::import()
	 * @see 	Libraries/Form_Validation
	 * @return	void
	 */
	public function replace_pgp()
	{
		// Load account, redirect if it doesn't exist.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		if ( ! isset($data['user']['pgp']))
			redirect('account');
				
		if ($this->form_validation->run('add_pgp') === TRUE)
		{
			$import = $this->gpg->import($this->input->post('public_key'));
			if (is_array($import))
			{
				$this->accounts_model->replace_pgp_key($this->current_user->user_id, array(
										'user_id' => $this->current_user->user_id,
										'fingerprint' => $import['fingerprint'],
										'public_key' => $import['clean_key']
										));
				redirect('account');
			}
			else
			{
				$data['returnMessage'] = 'Failed to import that public key.';
			}
		}
		
		$data['page'] = 'accounts/replace_pgp';
		$data['title'] = 'Replace PGP Key';
		
		$this->load->library('Layout', $data);
	}
	
	/**
	 * Add a PGP key to account.
	 * URI: /pgp/add
	 * 
	 * If a user is forced to keep their PGP key, they can only replace it.
	 * They will be redirected to the Replace PGP Key form. If they already 
	 * have a PGP key, then we redirect them to the account information page.
	 * 
	 * If form validation goes through, the GPG\import function checks
	 * the key is valid. If so, the key is inserted, and the user redirected.
	 * On failure, an error message is displayed.
	 * 
	 * @see 	Libraries/GPG
	 * @see		Libraries/Form_Validation
	 * @param	string
	 * @return	void
	 */
	public function add_pgp()
	{
		// If the user is forced to have a PGP key, they must replace it.
		if ($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
		//|| $this->current_user->user_role == 'Admin'
			)
			redirect('pgp/replace');
		
		// If they have a PGP key, redirect them.
		$pgp = $this->accounts_model->get_pgp_key($this->current_user->user_id);
		if ($pgp !== FALSE)
			redirect('account');
		
		$data['page'] = 'accounts/add_pgp';
		$data['title'] = 'PGP Public Key';	
		
		if ($this->form_validation->run('add_pgp') === TRUE)
		{
			$import = $this->gpg->import($this->input->post('public_key'));
			
			if (is_array($import))
			{
				$this->accounts_model->add_pgp_key(array(
					'user_id' => $this->current_user->user_id,
					'fingerprint' => $import['fingerprint'],
					'public_key' => $import['clean_key']
				));
				
				redirect('account');
			}
			else
			{
				$data['returnMessage'] = 'Failed to import that public key.';
			}
		}
		$this->load->library('Layout', $data);
	}
	
};

/* End of file: Accounts.php */
/* Location: ./application/controllers/Accounts.php */
