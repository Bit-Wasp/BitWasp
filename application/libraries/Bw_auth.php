<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/* Bw_auth class.
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
 */

class Bw_auth {
	
	protected $message_password_timeout = 1200;
	
	public $CI;
	public $URI;
	public $auth_reqs;
	
	public function __construct() {		
		$this->CI = &get_instance();
	
		$this->URI = $this->CI->current_user->URI;
		$this->auth_reqs = $this->CI->current_user->auth_reqs;
		
	}
	
	// Remove any expired authorization for pages.
	public function auth_timeout(){
		// Clear auth req data if the user isn't on the authorize page.
		if($this->URI[0] !== 'authorize')
			$this->CI->session->unset_userdata('current_auth_req');
		
		if(count($this->auth_reqs) > 0){
			$auth_reqs = $this->auth_reqs;
			$new = array();
			
			// Purge any expired ones. 
			foreach($auth_reqs as $key => $req){
				if( ($req->time+$req->timeout) > time() ){
					$new[$key] = array( 'timeout' => $req->timeout,
										'time' => $req->time);
				}
			}
			$this->set_data($new);
		}
	}
	
	// Generate a new auth request.
	public function new_auth(){
		$config = array('current_auth_req' => uri_string());
		$this->CI->session->set_userdata($config);
			
		redirect('authorize');
	}
	
	// Check the current URI has a currently verified request.
	public function check_current() {
		foreach($this->auth_reqs as $key => $req) {
			if($key == $this->URI[0])
				return TRUE;
		}
		
		return FALSE;
	}
	
	// Check if the user is attempting to authorize.
	public function has_request() {
		if(is_string($this->CI->session->userdata('current_auth_req')))
			return TRUE;
		
		return FALSE;
	}
	
	// General function to store the auth requests (JSON encoded)
	public function set_data(array $array) {
		$this->CI->session->set_userdata('auth_reqs', json_encode($array));
	}
	
	// After a successful authorization, add the authorization to the list.
	public function setup_auth($URI, $timeout) {
		if($timeout > 0) {
			$new_auth = $this->auth_reqs; 
			$new_auth[$URI[0]] = array('timeout' => $timeout,
										  'time' => time());
			$this->set_data($new_auth);
		}
	}	
	
	// Record the authorization, and how long until it expires, then redirect to desired page.
	public function successful_auth() {
		$this->CI->load->model('auth_model');
		$attempted_uri = $this->CI->current_user->current_auth_req;
		$URI = explode('/', $attempted_uri);
		
		// Lookup timeout.
		$timeout = $this->CI->auth_model->check_auth_timeout($URI[0]);
		$this->CI->session->unset_userdata('current_auth_req');
		
		$this->setup_auth($URI, $timeout);
		return $attempted_uri;
	}
	
	// Message password expiry function.
	public function message_password_timeout() {	
		$grant_time = $this->CI->session->userdata('message_password_granted');
		
		if($grant_time !== NULL && $grant_time < (time()-$this->message_password_timeout) ) {
			$this->CI->session->unset_userdata('message_password');
			$this->CI->session->unset_userdata('message_password_granted');
		}
	}
	
	// Generate and store a two factor token.
	public function generate_two_factor_token() {
		$this->CI->load->library('gpg');
		$this->CI->load->model('accounts_model');
		
		$key = $this->CI->accounts_model->get_pgp_key($this->CI->current_user->user_id);
		if($key == FALSE)
			return FALSE;
			
		$solution = $this->CI->general->generate_salt();
		$text = "Login Token: $solution\n";
		$challenge = $this->CI->gpg->encrypt($key['fingerprint'], $text);
		
		if($challenge == FALSE)
			return FALSE;
			
		if($this->CI->auth_model->add_two_factor_token($solution))
			return $challenge;
			
		return FALSE;
	}
};

 /* End of file Bw_auth.php */
