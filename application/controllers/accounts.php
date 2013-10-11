<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accounts Management Controller
 *
 * @package		BitWasp
 * @subpackage	Controllers
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
	
		// Load information for the view.
		$data['logged_in'] = $this->current_user->logged_in();
		$data['user_role'] = $this->current_user->user_role;
		$data['page'] = 'accounts/view';
		$data['title'] = $data['user']['user_name'];
		$this->load->library('Layout', $data);
	}
	
	/**
	 * View own user profile
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
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
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
	 *
	 * @access	public 
	 * @see 	Libraries/Form_Validation
	 * @see 	Libraries/GPG
	 * @see 	Models/Accounts_model
	 * @return	void
	 */
	public function delete_pgp() {
		// If a user is forced to have a PGP key, they must replace it instead.
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
			redirect('pgp/replace');
		
		// Load own account information.
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
		// If there is no PGP key, redirect to the accounts page.
		if(!isset($data['user']['pgp']))
			redirect('account');
		
		$this->load->library('form_validation');
		
		if($this->form_validation->run('delete_pgp') === TRUE) {
			if($this->input->post('delete') == '1') {
				if($this->accounts_model->delete_pgp_key($data['user']['id']) == TRUE) 
					redirect('account');
			} else {
				redirect('account');
			}
		}
		
		$data['page'] = 'accounts/delete_pgp';
		$data['title'] = 'Delete PGP Key';
		
		$this->load->library('Layout', $data);
	}

	/**
	 * Replace current PGP key. 
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
				// If the import is successful, delete the current key and add the new one.
				if($this->accounts_model->delete_pgp_key($this->current_user->user_id) == TRUE) {
					$config = array('user_id' => $this->current_user->user_id,
									'fingerprint' => $import['fingerprint'],
									'public_key' => $import['clean_key']);
									
					if($this->accounts_model->add_pgp_key($config) == TRUE) {
						redirect('account');
					}
				}
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
	 *
	 * @see 	Libraries/GPG
	 * @see		Libraries/Form_Validation
	 * @param	string
	 * @return	void
	 */
	public function add_pgp() {
		// If the user is forced to have a PGP key, they must replace it.
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
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
	 * @see 	Libraries/Form_Validation
	 * @see 	Models/Currencies_Model
	 * @param	int
	 * @return	bool
	 */
	public function check_valid_currency($param){
		$this->load->model('currencies_model');
		return ($this->currencies_model->get($param) !== FALSE) TRUE : FALSE;
	}
};

 /* End of file Account.php */
