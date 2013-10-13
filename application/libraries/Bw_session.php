<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class BW_Session {

	protected $CI;

	public $URI;
	public $session_id;
	public $user_role;
	public $auth_level;
	
	public function __construct(){
		$this->CI = &get_instance();
		$this->CI->load->model('auth_model');
		$this->CI->load->library('bw_auth');
		
		$this->URI = $this->CI->current_user->URI;	
		
		if($this->CI->current_user->logged_in()){
			
			// Kill a session due to inactivity.
			if((time()-$this->CI->current_user->last_activity) > $this->CI->bw_config->login_timeout
				&& $this->CI->session->userdata('new_session') !== 'true'){ 
				
				// Leave this commented for now to test. 
				if(!$this->CI->general->matches_any( $this->URI[0], array('login', 'register'))){
					$this->destroy(); 
					redirect('login');
					
				}		
			} else {
				if($this->CI->session->userdata('new_session'))
					$this->CI->session->unset_userdata('new_session');
				
				$this->CI->bw_auth->auth_timeout();
				$this->CI->bw_auth->message_password_timeout();
				
				$this->CI->session->set_userdata('last_activity', time());
				
				$this->user_role = $this->CI->current_user->user_role;
				
			}
		}
		
		$this->validate_req();
	}
	
	// Create a session. Params such as 2FA or a pubkey prompt can be set here.
	public function create($user, $params = NULL) {
		if($params == 'force_pgp') {
			$userdata = array(	'user_id' => $user['id'],
								'force_pgp' => 'true' );
		} else if($params == 'two_factor') {
			$userdata = array(	'user_id' => $user['id'],
								'two_factor' => 'true' );
		} else if($params == NULL) {
			
			$this->CI->session->unset_userdata('force_pgp');
			$this->CI->session->unset_userdata('two_factor');
			$userdata = array(	'logged_in' => 'true',
								'last_activity' => time(),
								'new_session' => 'true',
								'user_id'	=> $user['id'],
								'user_hash' => $user['user_hash'],
								'user_name' => $user['user_name'],
								'user_role' => $user['user_role'],
								'local_currency' => $user['local_currency'],
								'message_password' => NULL);
		}
		$this->CI->session->set_userdata($userdata);
	}
	
	// Destroy the current session.
	public function destroy() {
		$this->CI->session->unset_userdata('logged_in');
		$this->CI->session->unset_userdata('auth_reqs');
		$this->CI->session->unset_userdata('message_password');
		// Destroy the session.
		$this->CI->session->sess_destroy();
		redirect('login');									// made the change here!
	}
	
	// Validate a users request to view a page. Lots of rules.
	public function validate_req() {
		$this->auth_level = $this->CI->auth_model->check_auth($this->URI[0]);
		
		if($this->CI->general->matches_any($this->URI[0], array('','items','item','category','user')) == TRUE && $this->CI->bw_config->allow_guests == TRUE  && !$this->CI->current_user->logged_in() )
			return TRUE;
		
		if($this->auth_level == FALSE)
			return TRUE;
		
		if( $this->CI->current_user->two_factor == TRUE && !$this->CI->general->matches_any(uri_string(), array('login/two_factor', 'logout')) )
			redirect('login/two_factor');
		
		if( $this->CI->current_user->force_pgp == TRUE && !$this->CI->general->matches_any(uri_string(), array('register/pgp', 'logout')) )
			redirect('register/pgp');
			
		if($this->auth_level == 'guestonly') {
			if($this->CI->current_user->logged_in())
				redirect('');
			
			return TRUE;
		}
		
		if($this->auth_level == 'login' && $this->CI->current_user->logged_in())
			return TRUE;
		
		if($this->auth_level == 'vendor' && $this->CI->general->matches_any(strtolower($this->user_role), array('vendor')))
			return TRUE;
		
		if($this->auth_level == 'buyer' && $this->CI->general->matches_any(strtolower($this->user_role), array('buyer')))
			return TRUE;
		
		if($this->auth_level == 'admin' && $this->user_role == 'Admin')
			return TRUE;

		// Check if the page needs password authorization. 
		$multi_levels = explode('|', $this->auth_level);
		if($multi_levels[0] == 'auth'){
			
			if(		(	$multi_levels[1] == 'all' 
						&& $this->CI->current_user->logged_in()
						&& $this->CI->bw_auth->check_current() )
				||	(	$multi_levels[1] == 'admin'
						&& $this->CI->current_user->user_role == 'Admin'
						&& $this->CI->bw_auth->check_current() )	) {
				return TRUE;
				
			} else {
				// Set up new Auth request, and redirect to auth page.
				$this->CI->bw_auth->new_auth();
			}
		}
					
		// If user does not meet the criteria above, they are forbidden from accessing the page.			
		redirect('login');
	}
};

 /* End of file Bw_session.php */
