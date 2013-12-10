<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accounts Management Controller
 *
 * @package		BitWasp
 * @subpackage		Controllers
 * @category	Accounts
 * @author		BitWasp
 */
class Accounts extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('accounts_model');
		$this->load->library('gpg');
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
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function view($hash) {
		// Load the specified user, redirect if they don't exist.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $hash));
		if($data['user'] == FALSE)
			redirect('');
	
		$this->load->model('items_model');
		if($data['user']['user_role'] == 'Vendor')
			$data['items'] = $this->items_model->get_list(array('vendor_hash' => $data['user']['user_hash']));
	
		// Load information for the view.
		$data['logged_in'] = $this->current_user->logged_in();
		$data['user_role'] = $this->current_user->user_role;
		
		$data['page'] = 'accounts/view';
		$data['title'] = $data['user']['user_name'];
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
	public function me() {
		// Load profile from the current_user object. 
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));

		$data['page'] = 'accounts/me';
		$data['title'] = $data['user']['user_name'];
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
	public function edit() {
		$this->load->library('form_validation');
		$this->load->model('currencies_model');

		$data['page'] = 'accounts/edit';
		$data['title'] = 'Account Settings';

		// Load a list of currencies, and locations.
		$data['currencies'] = $this->currencies_model->get();	
		$data['locations'] = $this->general_model->locations_list();

		// Load own user profile.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));

		// Check if the user is forced to user PGP. If so, display the 'Replace' link instead of 'Delete'
		$data['option_replace_pgp'] = FALSE;
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
			|| $this->current_user->user_role == 'Admin')
			$data['option_replace_pgp'] = TRUE;
			
		// Different form validation rules depending on if the user has a PGP key uploaded.
		$form_rules = (isset($data['user']['pgp']) == TRUE) ? 'account_edit' : 'account_edit_no_pgp';

		// If form validation is successful, update the changes.
		if($this->form_validation->run($form_rules) == TRUE) {
			
			$changes = array();
			
			// Compare POSTed values to the original, remove any NULL entries.
			$changes['location'] = ($data['user']['location'] == $this->input->post('location')) ? NULL : $this->input->post('location');
			$changes['display_login_time'] = ($data['user']['display_login_time'] == $this->input->post('display_login_time')) ? NULL : $this->input->post('display_login_time');
			$changes['local_currency'] = ($data['user']['currency'] == $this->input->post('local_currency')) ? NULL : $this->input->post('local_currency');				
			
			// Only consider these if the user has a PGP key uploaded.
			if(isset($data['user']['pgp'])) {
				$changes['two_factor_auth'] = ($data['user']['two_factor_auth'] == $this->input->post('two_factor_auth')) ? NULL : $this->input->post('two_factor_auth');
				$changes['force_pgp_messages'] = ($data['user']['force_pgp_messages'] == $this->input->post('force_pgp_messages')) ? NULL : $this->input->post('force_pgp_messages');
				$changes['block_non_pgp'] = ($data['user']['block_non_pgp'] == $this->input->post('block_non_pgp')) ? NULL : $this->input->post('block_non_pgp');
			}
			$changes = array_filter($changes, 'strlen');

			if(count($changes) > 0) {	
				// If there are changes, set an error message for if the update fails (and user is not redirected).
				$data['returnMessage'] = 'Unable to save your changes, please try again later.';
				if($this->accounts_model->update($changes) == TRUE) 
					redirect('account');
				
			}
		}
		
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
	public function delete_pgp() {
		// If a user is forced to have a PGP key, they must replace it instead.
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
		|| $this->current_user->user_role == 'Admin')
			redirect('pgp/replace');
		
		// Load own account information.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
		// If there is no PGP key, redirect to the accounts page.
		if(!isset($data['user']['pgp']))
			redirect('account');
		
		$this->load->library('form_validation');
		
		if($this->form_validation->run('delete_pgp') === TRUE) {
			if($this->input->post('delete') == '1') {
				// If the user has selected to delete their key, delete it and redirect.
				if($this->accounts_model->delete_pgp_key($data['user']['id']) == TRUE) 
					redirect('account');
			} else {
				// Otherwise, they've chosen not to delete. Redirect.
				redirect('account');
			}
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
	public function replace_pgp() {
		// Load account, redirect if it doesn't exist.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		if(!isset($data['user']['pgp']))
			redirect('account');
		
		$this->load->library('form_validation');
		
		if($this->form_validation->run('add_pgp') === TRUE) {
			// Import the PGP key. 
			$public_key = $this->input->post('public_key');
			$import = $this->gpg->import($public_key);
			
			if($import !== FALSE) {
			
				$config = array('fingerprint' => $import['fingerprint'],
								'public_key' => $import['clean_key']);				
				
				// If the import is successful, delete the current key and add the new one.
				if($this->accounts_model->replace_pgp_key($data['user']['id'], $config) == TRUE) 
					redirect('account');
					
				// If the user has not been redirected, display an error message.
				$data['returnMessage'] = 'An error occured, please try again.';
			} else {
				$data['returnMessage'] = 'Unable to import this PGP key, please ensure you enter an ASCII armored PGP public key.';
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
	public function add_pgp() {
		// If the user is forced to have a PGP key, they must replace it.
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor'
		//|| $this->current_user->user_role == 'Admin'
		)
			redirect('pgp/replace');
		
		// If they have a PGP key, redirect them.
		$pgp = $this->accounts_model->get_pgp_key($this->current_user->user_id);
		if($pgp !== FALSE)
			redirect('account');
		
		$this->load->library('gpg');
		$this->load->library('form_validation');
		
		$data['page'] = 'accounts/add_pgp';
		$data['title'] = 'PGP Public Key';	
		
		if($this->form_validation->run('add_pgp') === TRUE) {
			// Check if the key can be imported.
			$import = $this->gpg->import($this->input->post('public_key'));
			if($import !== FALSE) {
				$add = $this->accounts_model->add_pgp_key( array('user_id' => $this->current_user->user_id,
																 'fingerprint' => $import['fingerprint'], 
																 'public_key' => $import['clean_key'] ) );
				if($add == TRUE)
					redirect('account');
			} 
			// Display an error if the user has not been redirected.
			$data['returnMessage'] = 'Unable to import this PGP key, please ensure you enter an ASCII armored PGP public key.';
		}
		$this->load->library('Layout', $data);
	}
	
	// Callback functions for form validation.
	
	/**
	 * Check if the parameter is for a boolean.
	 *
	 * In HTML forms when setting something as enabled or disabled, the radio
	 * button will be either '0' or '1'. Check if the value contains either
	 * of these. Return TRUE if the parameter matches one, return FALSE
	 * on failure.
	 * 
	 * @see 	Libraries/Form_Validation
	 * @param	int
	 * @return	bool
	 */
	public function check_bool($param) {
		return ($this->general->matches_any($param, array('0','1')) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Check that the specified location ID exists.
	 *
	 * Locations are selected on HTML forms and submitted as the ID. 
	 * If the submitted parameter does not contain a valid location ID, 
	 * return FALSE. Return TRUE if it contains a valid location ID.
	 * 
	 * @see 	Libraries/Form_Validation
	 * @param	int
	 * @return	bool
	 */
	public function check_location($param) {
		return ($this->general_model->location_by_id($param) !== FALSE) ? TRUE : FALSE;
	}
		
	/**
	 * Check that the specified currency ID exists.
	 * 
	 * Currencies are chosen on HTML forms by specifying their currency ID.
	 * This function attempts to load the specified currency. If successful
	 * it returns TRUE, return FALSE on failure.
	 *
	 * @see 	Libraries/Form_Validation
	 * @see 	Models/Currencies_Model
	 * @param	int
	 * @return	bool
	 */
	public function check_valid_currency($param) {
		$this->load->model('currencies_model');
		return ($this->currencies_model->get($param) !== FALSE) ? TRUE : FALSE;
	}
};

 /* End of file Account.php */
