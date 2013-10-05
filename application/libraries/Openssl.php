<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Openssl {


	protected $digest_alg = "sha512";
	protected $private_key_bits = 2048;
	protected $private_key_type = OPENSSL_KEYTYPE_RSA;

	public function __construct() {
		$CI = &get_instance();
		$CI->load->library('bw_config');
		$this->private_key_bits = $CI->bw_config->openssl_keysize;
	}

	// Generate a keypair using the basic configuration.
	public function keypair($message_password) {
		
		/* Create the private and public key */
		$openssl_config = array(	"digest_alg" => $this->digest_alg,
									"private_key_bits" => (int)$this->private_key_bits,
									"private_key_type" => $this->private_key_type);

		$keypair = openssl_pkey_new($openssl_config);

		/* Extract the private key from $res to $privKey */
		openssl_pkey_export($keypair, $private_key, $message_password, $openssl_config);
		unset($message_password);
		
		// Extract the public key from $res to $pubKey 
		$public_key = openssl_pkey_get_details($keypair);
		$public_key = $public_key['key'];
		unset($keypair);
		return array('public_key' => base64_encode($public_key),
					 'private_key' => base64_encode($private_key) );
	}
	
	// Encrypt text using a public key.
	public function encrypt($text, $public_key) { 
		openssl_public_encrypt($text, $encrypted, $public_key);
		return $encrypted;
	}
	
	// Decrypt text using an encrypted private key and password.
	public function decrypt($text, $private_key, $password) { 
		// Decrypt the private key prior to use.
		$res = openssl_pkey_get_private($private_key, $password);
		
		// Decrypt the text.
		@openssl_private_decrypt($text, $decrypted, $res);
		
		unset($password);
		unset($res);
		return $decrypted;
	}
	
};
