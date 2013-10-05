<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class GPG {

	protected $gpg;
	
	public $have_GPG = TRUE;
	public $style;
	public $version;
	
	// Set up the class, by initializing the GnuPG extension. 
	// Compatible with object-oriented or procedural version.
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
	
	// Check if GPG is present.
	public function have_GPG(){
		return $this->have_GPG;
	}
	
	// Import a public key.
	// A successful import will result in $info['fingerprint'] being set, and != 0.
	// Unsuccessful will just return FALSE;
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
	public function encrypt($fingerprint, $plaintext) {
		if($this->style == 'oop') {
			
			$gpg->addencryptkey($fingerprint);
			$ciphertext = $gpg->encrypt($gpg, "$plaintext\n");
		} else if($this->style == 'proc') {
			
			gnupg_addencryptkey($this->gpg, $fingerprint);
			$ciphertext = gnupg_encrypt($this->gpg, "$plaintext\n");
		}
		
		$full_crypt = "-----BEGIN PGP MESSAGE-----\n\n".substr($ciphertext,28);
		
		return $full_crypt;
	}
};

 /* End of file Gpg.php */
