<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accounts extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('accounts_model');
		$this->load->library('gpg');
	}

	// View a users public profile.
	public function view($hash) {
		$data['user'] = $this->accounts_model->get(array('user_hash' => $hash));
		if($data['user'] == FALSE)
			redirect('');
	
		$data['logged_in'] = $this->current_user->logged_in();
		$data['page'] = 'accounts/view';
		$data['title'] = $data['user']['user_name'];
		
		$this->load->library('Layout', $data);
	}
	
	// Load own account settings.
	public function me() {
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));

		$data['page'] = 'accounts/me';
		$data['title'] = $data['user']['user_name'];
		
		$this->load->library('Layout', $data);
	}
	
	// Edit account settings
	public function edit() {
		$this->load->library('form_validation');
		$this->load->model('currencies_model');

		$data['currencies'] = $this->currencies_model->get();	
		$data['page'] = 'accounts/edit';
		$data['title'] = 'Account Settings';
		$data['locations'] = $this->general_model->locations_list();
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
		$data['option_replace_pgp'] = FALSE;
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
			$data['option_replace_pgp'] = TRUE;
			
		$form_rules = (isset($data['user']['pgp']) == TRUE) ? 'account_edit' : 'account_edit_no_pgp';

		if($this->form_validation->run($form_rules) == TRUE) {
			$changes = array();
			// Compare post values to the original, remove any NULL entries.
			$changes['location'] = ($data['user']['location'] == $this->input->post('location')) ? NULL : $this->input->post('location');
			$changes['display_login_time'] = ($data['user']['display_login_time'] == $this->input->post('display_login_time')) ? NULL : $this->input->post('display_login_time');
			$changes['local_currency'] = ($data['user']['currency'] == $this->input->post('local_currency')) ? NULL : $this->input->post('local_currency');	
			
			if(isset($data['user']['pgp'])) {
				$changes['two_factor_auth'] = ($data['user']['two_factor_auth'] == $this->input->post('two_factor_auth')) ? NULL : $this->input->post('two_factor_auth');
				$changes['force_pgp_messages'] = ($data['user']['force_pgp_messages'] == $this->input->post('force_pgp_messages')) ? NULL : $this->input->post('force_pgp_messages');
			}
			$changes = array_filter($changes, 'strlen');

			if(count($changes) > 0){	
				$data['returnMessage'] = 'Unable to save your changes, please try again later.';
				if($this->accounts_model->update($changes) == TRUE) 
					redirect('account');
				
			}
		}
		
		$this->load->library('Layout', $data);
	}
	
	// Delete a users PGP key.
	public function delete_pgp() {
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
			redirect('pgp/replace');
		
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		
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

	// Replace a users PGP key. Accept old, import (error on fail due to invalid key), delete old and add new one.
	public function replace_pgp() {
		$data['user'] = $this->accounts_model->get(array('user_hash' => $this->current_user->user_hash), array('own' => TRUE));
		if(!isset($data['user']['pgp']))
			redirect('account');
		
		$this->load->library('form_validation');
		
		if($this->form_validation->run('add_pgp') === TRUE) {
			$public_key = $this->input->post('public_key');
			$import = $this->gpg->import($public_key);
			
			if($import !== FALSE) {
				if($this->accounts_model->delete_pgp_key($this->current_user->user_id) == TRUE) {
					$config = array('user_id' => $this->current_user->user_id,
									'fingerprint' => $import['fingerprint'],
									'public_key' => $import['clean_key']);
									
					if($this->accounts_model->add_pgp_key($config) == TRUE) {
						redirect('account');
					}
				}
				$data['returnMessage'] = 'An error occured, please try again.';
			} else {
				$data['returnMessage'] = 'Unable to import this PGP key, please ensure you enter an ASCII armored PGP public key.';
			}
		}
		
		$data['page'] = 'accounts/replace_pgp';
		$data['title'] = 'Replace PGP Key';
		
		$this->load->library('Layout', $data);
	}
	
	// Add a PGP key.
	public function add_pgp() {
		if($this->bw_config->force_vendor_pgp == TRUE && $this->current_user->user_role == 'Vendor')
			redirect('pgp/replace');
			
		$pgp = $this->accounts_model->get_pgp_key($this->current_user->user_id);
		if($pgp !== FALSE)
			redirect('account');
		
		$this->load->library('gpg');
		$this->load->library('form_validation');
		
		$data['page'] = 'accounts/add_pgp';
		$data['title'] = 'PGP Public Key';	
		
		if($this->form_validation->run('add_pgp') === TRUE) {
			$import = $this->gpg->import($this->input->post('public_key'));
			if($import !== FALSE) {
				$add = $this->accounts_model->add_pgp_key( array('user_id' => $this->current_user->user_id,
																 'fingerprint' => $import['fingerprint'], 
																 'public_key' => $import['clean_key'] ) );
				if($add == TRUE)
					redirect('account');
			} 
			$data['returnMessage'] = 'Unable to import this PGP key, please ensure you enter an ASCII armored PGP public key.';
		}
		$this->load->library('Layout', $data);
	}
	
	
	// Callback functions for form validation.
	public function check_bool($param) {
		if($this->general->matches_any($param, array('0','1')))
			return TRUE;
		
		return FALSE;
	}
	
	public function check_location($param) {
		if($this->general_model->location_by_id($param) == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	public function check_valid_currency($param){
		$this->load->model('currencies_model');
		if($this->currencies_model->get($param) !== FALSE)
			return TRUE;
		
		return FALSE;
	}
};

 /* End of file Account.php */
