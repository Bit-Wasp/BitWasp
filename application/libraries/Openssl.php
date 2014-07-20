<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * OpenSSL Library
 *
 * This library contains the functions to manage RSA encryption within
 * the marketplace. Can generate RSA keypair's at a specified
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    OpenSSL
 * @author        BitWasp
 */
class Openssl
{

    /**
     * Digest Algorithm
     *
     * Set the default hash function to use.
     */
    protected $digest_alg = "sha512";

    /**
     * Private Key Bits
     *
     * Sets the default bits the private key should use.
     */
    protected $private_key_bits = 2048;

    /**
     * Private Key Type
     *
     * Sets which default type the keypair should be.
     */
    protected $private_key_type = OPENSSL_KEYTYPE_RSA;

    /**
     * Constructor
     *
     * This function is used to load the CodeIgniter framework, and to
     * work out the keysize to be used.
     *
     */
    public function __construct()
    {
        $CI = & get_instance();
        $this->private_key_bits = ($CI->bw_config->openssl_keysize == '') ? $this->private_key_bits : $CI->bw_config->openssl_keysize;
    }

    /**
     * Keypair
     *
     * Generate an RSA keypair, the private key of which is protected
     * using the specified $message_password. Returns the public/private
     * keypair as an array containing the base64 decoded strings (for
     * insertion into the database).
     *
     * @param        string $message_password
     * @return      array
     */
    public function keypair($message_password)
    {

        /* Create the private and public key */
        $openssl_config = array("digest_alg" => $this->digest_alg,
            "private_key_bits" => (int)$this->private_key_bits,
            "private_key_type" => $this->private_key_type);

        $keypair = openssl_pkey_new($openssl_config);

        /* Extract the private key from $res to $private_key */
        openssl_pkey_export($keypair, $private_key, $message_password, $openssl_config);
        unset($message_password);

        // Extract the public key from $res to $public_key
        $public_key = openssl_pkey_get_details($keypair);
        $public_key = $public_key['key'];
        unset($keypair);
        return array('public_key' => base64_encode($public_key),
            'private_key' => base64_encode($private_key));
    }

    /**
     * Encrypt
     *
     * This function encrypts the specified $text using the specified
     * $public_key. Returns the encrypted text.
     *
     * @param $text
     * @param $public_key
     * @return mixed
     */
    public function encrypt($text, $public_key)
    {
        openssl_public_encrypt($text, $encrypted, $public_key);
        return $encrypted;
    }

    /**
     * Decrypt
     *
     * Decrypt text using the password protected $private_key and the
     * users $message_password. Suppress errors for openssl_private_decrypt
     * because when entering the message pin on /messaages/pin, the supplied
     * pin may be incorrect. We handle errors by checking the output.
     *
     * @param        string $text
     * @param        string $private_key
     * @param        string $password
     * @return        string
     */
    public function decrypt($text, $private_key, $password)
    {
        // Decrypt the private key prior to use.
        $res = openssl_pkey_get_private($private_key, $password);
        // Decrypt the text.
        @openssl_private_decrypt($text, $decrypted, $res);
        unset($password);
        unset($res);
        return $decrypted;
    }

};

/* End of File: Openssl.php */
