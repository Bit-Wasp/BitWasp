<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Captcha Library
 * 
 * Library for the generation, and checking, of captcha requests.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Captcha
 * @author		BitWasp
 */
class Bw_captcha {

	protected $CI; 
	/**
	 * Captcha Length
	 * 
	 * Set from the value in bw_config->captcha_length. This sets the number
	 * of characters the captcha should contain.
	 */
	protected $captcha_length; 
	
	/**
	 * Captcha Timeout
	 * 
	 * This sets how long before we forget about a captcha request and remove it from the database.
	 * Default is 20 minutes.
	 */
	protected $captcha_timeout = 1200; 
	
	/**
	 * Construct
	 * 
	 * Load the CodeIgniter framework, along with it's captcha helper script,
	 * the captchas model, and the images library.
	 * 
	 * Purge all of the expired captchas.
	 */
	public function __construct() {
		$this->CI = &get_instance();
		
		$this->CI->load->helper('captcha');
		$this->CI->load->model('captchas_model');
		$this->CI->load->library('image');
		
		$expired_time = time()-$this->captcha_timeout;
		$this->CI->captchas_model->purge_expired($expired_time);
	}
		
	/**
	 * Check
	 * 
	 * Checks the entered $answer to see if it correspondes to the
	 * captchakey stored in the session
	 * 
	 * @param		string $answer.
	 * @return		bool
	 */
	public function check($answer) { 
		// Load captcha identifier from session.
		
		$key = $this->CI->session->userdata('captcha_key');
		
		if(isset($answer) && isset($key)){
			$test = $this->CI->captchas_model->get($key);
			
			if($test == NULL)
				return FALSE;

			// Unset the current challenge from the users session.
			$this->CI->session->unset_userdata('captcha_key');
	
			return ($test['solution'] == $answer) ? TRUE : FALSE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Generate
	 * 
	 * Remove previous challenges for this user. 
	 * 
	 * Generate a captcha solution based on the admin-configurable length.
	 * Generate an image using the CI captcha library, generate a unique
	 * hash for the captchakey. Set the captchakey in memory to check
	 * later on.
	 * 
	 * @return		string
	 */
	public function generate() {
		// Check if there is a challenge set for this user. Delete old and create a new one.
		// Either way, the timed removal of old captchas will fix this sort of thing.
		$old_challenge = $this->CI->session->userdata('captcha_key');
		if(!empty($old_challenge) && is_string($old_challenge)){
			$old_captcha = $this->CI->captchas_model->get($old_challenge);
			$this->CI->captchas_model->delete($old_captcha['id']);
		}
				
		// list all possible characters, similar looking characters and vowels have been removed 
		$possible = '23456789bcdfghjkmnpqrstvwxyz';
		$characters = ''; 
		$i = 0;
		
		// create a captcha based on supplied length.
		while ($i < $this->CI->bw_config->captcha_length){
			$characters .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		
		// Array to pass to CI captcha helper.
		$config = array(	'word' => $characters,
							'img_path' => 'assets/images/captcha/',
							'img_url' => base_url().'assets/images/captcha/',
							'font_path' => 'assets/font.ttf',
							'img_width' => '218'
					);
					
		// Create captcha from the config data.
		$captcha = create_captcha($config);
		
		// Load the base64 image into memory and then erase the file.
		$image = $this->CI->image->temp("captcha/{$captcha['time']}.jpg");
		unlink("assets/images/captcha/{$captcha['time']}.jpg");
		
		// Create a unique key for the captcha and set it in the session.
		$key = $this->CI->general->unique_hash('captchas','key');
			
		// Add the solution to the captcha to the database.
		$this->CI->captchas_model->set($key, $characters);
		
		$this->CI->session->set_userdata('captcha_key',$key);
		
		return $image;
	}
	
};

/* End of File: Bw_captcha.php */
