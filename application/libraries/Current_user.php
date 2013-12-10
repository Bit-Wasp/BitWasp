<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Current User Library
 * 
 * This library is used to allow easy access to information about the
 * user. User data is taken from the session, and the database, to allow
 * a central source of frequently used data.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Current_User
 * @author		BitWasp
 */
class Current_User {
	
	protected $CI;
	public $auth_reqs = array();
	public $current_auth_req;
	public $force_pgp;
	public $logged_in = FALSE;
	public $last_activity;
	public $user_id;
	public $user_hash;
	public $user_name;
	public $message_password;
	public $message_password_granted;
	public $two_factor;
	public $user_role;
	public $session_id;
	public $URI;
	public $entry_payment = FALSE;

	/**
	 * Constructor
	 * 
	 * This function generates all the information we want to provide
	 * using this library.
	 */
	public function __construct() {
		$this->CI = &get_instance();
			
		$this->URI = explode("/", uri_string());
			
		$this->CI->load->model('currencies_model');			
		
		if($this->CI->session->userdata('logged_in') == 'true') {
			$this->CI->load->model('messages_model');
			$this->logged_in = TRUE;
			$this->user_id = $this->CI->session->userdata('user_id');
			$this->user_hash = $this->CI->session->userdata('user_hash');
			$this->user_name = $this->CI->session->userdata('user_name');
			$this->last_activity = $this->CI->session->userdata('last_activity');
			$this->user_role = $this->CI->session->userdata('user_role');
			$this->auth_reqs = (array)json_decode($this->CI->session->userdata('auth_reqs'));
			$this->message_password = $this->CI->session->userdata('message_password');	
			$this->message_password_granted = $this->CI->session->userdata('message_password_granted');
			$this->current_auth_req = $this->CI->session->userdata('current_auth_req');

			$this->CI->load->model('accounts_model');
			$user = $this->CI->accounts_model->get(array('user_hash' => $this->user_hash), array('own' => TRUE));
			
			$tmp = $this->CI->currencies_model->get($user['local_currency']);
			// Determine which currency the user has set for themselves.
			$this->currency = ($tmp == FALSE || $this->CI->bw_config->price_index == 'Disabled') ? $this->CI->currencies_model->get('0') :  $tmp;

		} else {
			$id = $this->CI->session->userdata('user_id');

			$this->currency = $this->CI->currencies_model->get('0');
			
			// If an ID is set, user is in a half session.
			if(is_numeric($id) && $id !== NULL) {
				$this->user_id = $id;
			
				if($this->CI->session->userdata('two_factor') == 'true')
					$this->two_factor = TRUE;
			
				if($this->CI->session->userdata('force_pgp') == 'true')
					$this->force_pgp = TRUE;
					
				if($this->CI->session->userdata('entry_payment') == 'true')
					$this->entry_payment = TRUE;
			}
		}	
	}
	
	/**
	 * Status
	 * 
	 * Load all the information from this object.
	 *
	 * @return		array
	 */
	public function status() {
		$vars = get_object_vars($this);
		unset($vars['CI']);
		return $vars;
	}
		
	/** 
	 * Logged In
	 * 
	 * Function to check if this user is logged in.
	 *
	 * @return 		bool
	 */
	public function logged_in() {
		return $this->logged_in;
	}
	
	/**
	 * Set Message Password
	 * 
	 * Sets up the message password in the users session along with the
	 * time it was set (to allow it to expire)
	 * 
	 * @param		string
	 * @return		void
	 */
	public function set_message_password($password) {
		$this->CI->session->set_userdata('message_password',$password);
		$this->CI->session->set_userdata('message_password_granted', time());
	}
	
};


 /* End of file Current_user.php */
