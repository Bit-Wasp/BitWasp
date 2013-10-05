<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Authorize extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('bw_auth');
		$this->load->library('bw_captcha');
		$this->load->model('auth_model');
	}

	// Handle password requests for sensitive pages.
	public function index(){		
		if(is_string($this->session->userdata('current_auth_req'))){
			$this->load->library('form_validation');
			$this->load->model('users_model');
			
			$data['title'] = 'Authorize Request';
			$data['page'] = 'authorize/password';
			
			if ($this->form_validation->run('authorize') == FALSE) {
				
				// Use the usual login form!
				// Insert some return data to say it's an authorization request.
				$data['returnMessage'] = 'To access this page, you must enter your password.';
				
			} else {
					
				$password = $this->input->post('password');
				$user_info = $this->users_model->get(array('id' => $this->current_user->user_id));
					
				if($user_info !== FALSE){
					$check_login = $this->users_model->check_password($this->current_user->user_name, $user_info['salt'], $password);
					
					if( ($check_login !== FALSE) && ($check_login['id'] == $user_info['id']) ) {
						$uri = $this->bw_auth->successful_auth();
						redirect($uri);
					}
				} 
				
				$data['returnMessage'] = 'Your details were incorrect! To continue with this request, you must enter your login details.';
			}
			$data['captcha'] = $this->bw_captcha->generate();
			
			$this->load->library('Layout', $data);
		} else {
			redirect('');
		}
	}
	
	// Callback functions for form validation
	public function check_captcha($param) {
		return $this->bw_captcha->check($param);
	}
	
};

 /* End of file Authorize.php */
