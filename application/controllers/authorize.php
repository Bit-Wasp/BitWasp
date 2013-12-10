<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authorization Request's Controller
 *
 * This class handles requesting a password from a user to access a
 * restricted page. 
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Authorize
 * @author		BitWasp
 * 
 * @see			Libraries/Bw_Session
 */

class Authorize extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Auth_Model
	 * @see		Libraries/Bw_Auth
	 * @see		Libraries/Bw_Captcha
	 */
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_captcha');
		$this->load->model('auth_model');
	}

	/**
	 * Handle password requests for sensitive pages.
	 *
	 * @see 	Libraries/Form_Validation
	 * @see		Models/Users_Model
	 * @return	void
	 */
	public function index() {
		// Abort if there is no stored session data about an
		// authorization request.
		if(!is_string($this->session->userdata('current_auth_req')))
			redirect('');
		
		$this->load->library('form_validation');
		$this->load->model('users_model');
		
		$data['header_meta'] = $this->load->view('authorize/authorize_hash_header', NULL, true);
		$data['title'] = 'Authorize Request';
		$data['page'] = 'authorize/password';
		
		$data['returnMessage'] = 'To access this page, you must enter your password.';
	
		if ($this->form_validation->run('authorize') == TRUE) {	
			$user_info = $this->users_model->get(array('id' => $this->current_user->user_id));	
			
			// Check the user info exists.
			if($user_info !== FALSE) {
				
				// Work out if submitted password has been hashed by javascript already
				$password = ($this->input->post('js_disabled') == '1') ? $this->general->hash($this->input->post('password')) : $this->input->post('password');
				$password = $this->general->password($password, $user_info['salt']);
				
				// Check the password is valid.
				$check_login = $this->users_model->check_password($this->current_user->user_name, $password);
				
				if( ($check_login !== FALSE) && ($check_login['id'] == $user_info['id']) ) {
					// Load the requested URI and redirect (after clearing up session data)
					$uri = $this->bw_auth->successful_auth();
					redirect($uri);
				}
			} 
			// Leave an error message if the user was not redirected.
			$data['returnMessage'] = 'Your details were incorrect! To continue with this request, you must enter your login details.';
		}
		
		// Generate a new captcha.
		$data['captcha'] = $this->bw_captcha->generate();
		$this->load->library('Layout', $data);

	}
	
	// Callback functions for form validation.
	
	/**
	 * Check the supplied answer to the Captcha is correct..
	 *
	 * @param	string
	 * @return	bool
	 */
	public function check_captcha($param) {
		return $this->bw_captcha->check($param);
	}
	
};

 /* End of file Authorize.php */
