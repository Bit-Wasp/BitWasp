<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class General {
	
	protected $CI;

	public function __construct() { 	
		$this->CI = &get_instance();
		$this->CI->load->model('general_model');
	}

	// Ensures all the expect entries are present. Set to NULL if not specified.
	public function expect_keys($str, $array) {
		$keys = explode(",", $str);
		foreach($keys as $key) {
			if(!array_key_exists(trim($key), $array))
				$array[$key] = NULL;
		}
		return $array;
	}
	
	// Generate random bytes.
	public function random_data($length) {
		$data = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
		return $data;
	}
	
	// Generate random data, and take sha512 hash to generate a salt.
	public function generate_salt() {
		return $this->hash($this->random_data('512'));
	}
	
	// Generate a sha512 hash of a string, with an optional salt.
	public function hash($password, $salt = NULL){ 
		$sha_limit_loop = 10;
		
		$hash = '';
		for($i = 0; $i < $sha_limit_loop; $i++){
			$hash = hash('sha512', $hash.$salt.$password);
		}
		return $hash;	
	}
	
	public function unique_hash($table, $column, $length = 16){

		$hash = substr($this->hash($this->generate_salt()), 0, $length);
		// Test the DB, see if the hash is unique. 
		$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);

		while($test == FALSE){
			$hash = substr($this->hash($this->generate_salt()), 0, $length);

			// Perform the test again, and see if the loop goes on.
			$test = $this->CI->general_model->check_unique_entry($table, $column, $hash);	
		}

		// Finally return the generated unique hash.
		return $hash;			
	}
		
	// Determine if any values in the array matches field in $arr.
	public function matches_any($str, array $arr) {
		foreach($arr as $val) {
			if (($str == $val) == TRUE) return TRUE;
		}
		return FALSE;
	}
	
	// 1 - Buyer
	// 2 - Vendor
	// 3 - Admin
	// Used in the registration token code.
	public function role_from_id($id){
		switch($id){
			case '1':
				$result = 'Buyer';
				break;
			case '2':
				$result = 'Vendor';
				break;
			case '3':
				$result = 'Admin';
				break;
			default:
				$result = 'Buyer';
				break;
		}
		return $result;
	}
	
	
	// Format time into a readable format.
	public function format_time($timestamp){
		// Load the current time, and check the difference between the times in seconds.
		$currentTime = time();
		$difference = $currentTime-$timestamp;

		if ($difference < 60) {					// within a minute.
			return 'less than a minute ago';
		} else if($difference < 120) {			// 60-120 seconds.
			return 'about a minute ago';
		} else if($difference < (60*60)) {		// Within the hour. 
			return round($difference / 60) . ' minutes ago';
		} else if($difference < (120*60)) {		// Within a few hours.
			return 'about an hour ago';
		} else if($difference < (24*60*60)) {		// Within a day.
			return 'about ' . round($difference / 3600) . ' hours ago';
		} else if($difference < (48*60*60)) {		// Just over a day.
			return '1 day ago';
		} else if($timestamp == '0' || $timestamp == NULL){ //The timestamp wasn't set which means it has never happened.
			return 'Never';
		} else { // Otherwise just return the basic date.
			return date('j F Y',$timestamp);
		}
	}

};

 /* End of file General.php */
