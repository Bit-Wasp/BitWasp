<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * GPG
 * 
 * This library handles server side GPG encryption of messages. It is also
 * used to generate challenges for users to perform GPG two step authentication.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Admin
 * @author		BitWasp
 */

class GPG {

	protected $gpg;
	
	public $have_GPG = TRUE;
	public $style;
	public $version;
	
	// Set up the class, by initializing the GnuPG extension. 
	// Compatible with object-oriented or procedural version.
	/**
	 * Construct
	 * 
	 * In constructing this class we check if the gnupg OOP class is
	 * available, or if the procedural functions are there instead.
	 * Loads the current version of the GPG extension if it's available.
	 */
	public function __construct() {
		if(class_exists('gnupg')) {
			
			$this->gpg = new gnupg();
			$this->style = 'oop';
			$this->version = phpversion('gnupg');
		} if(function_exists('gnupg_init')) {
			
			$this->gpg = gnupg_init();
			// Detect whether functions are procedural or object-oriented. 
			$this->style = 'proc';
			$this->version = phpversion('gnupg');
			
		} else {
			// User does not have a GPG extension.
			$this->have_GPG = FALSE;
		}
	}
	
	/**
	 * Have GPG
	 * 
	 * Returns whether the GPG extension has been detected.
	 */
	public function have_GPG(){
		return $this->have_GPG;
	}
	
	// Import a public key.
	// A successful import will result in $info['fingerprint'] being set, and != 0.
	// Unsuccessful will just return FALSE;
	/**
	 * Import
	 * 
	 * Imports an ASCII armored PGP key. Searches the $ascii input to
	 * see if it contains valid GPG headers, and tries to import the key.
	 * If the import is successful, then $info['fingerprint'] will be set, 
	 * and we can return an array with a santizied (htmlentities) key 
	 * and fingerprint.
	 * Returns FALSE on failure.
	 */
	public function import($ascii) {
		$start = strpos($ascii, '-----BEGIN PGP PUBLIC KEY BLOCK-----');
		$end = strpos($ascii, '-----END PGP PUBLIC KEY BLOCK-----')+34;
		
		$key = substr($ascii, $start, ($end-$start));
		
		if($this->style == 'oop') {
			$info = $this->gpg->import($key);
		} else if($this->style == 'proc') {
			$info = gnupg_import($this->gpg, $key);
		} 
		if(isset($info['fingerprint'])){
			$info['clean_key'] = htmlentities($key);
			return $info;
		}
				
		return FALSE;
	}
	
	// Encrypt a message using a public key fingerprint (key already in keychain)
	// Only call when the fingerprint is known.  
	/**
	 * Encrypt
	 * 
	 * Takes the supplied $fingerprint so GnuPG can load the key from the 
	 * keyring. 
	 * 
	 * @param		string
	 * @param		string
	 * @return		string
	 */
	public function encrypt($fingerprint, $plaintext) {
		if($this->style == 'oop') {
			
			if($gpg->addencryptkey($fingerprint) == FALSE)
				return FALSE;
			$ciphertext = $gpg->encrypt($gpg, "$plaintext\n");
		} else if($this->style == 'proc') {
			
			if(gnupg_addencryptkey($this->gpg, $fingerprint) == FALSE)
				return FALSE;
				
			$ciphertext = gnupg_encrypt($this->gpg, "$plaintext\n");
		}
		
		$full_crypt = "-----BEGIN PGP MESSAGE-----\n".substr($ciphertext,28);
		
		return $full_crypt;
	}
};

 /* End of file Gpg.php */
