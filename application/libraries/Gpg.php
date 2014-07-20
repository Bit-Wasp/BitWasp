<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GPG Library
 *
 * This library handles server side GPG encryption of messages. It is also
 * used to generate challenges for users to perform GPG two step authentication.
 *
 * @package        BitWasp
 * @subpackage    Library
 * @category    GPG
 * @author        BitWasp
 */
class GPG
{

    /**
     * GPG
     *
     * This variable contains the GPG object once initialized.
     */
    protected $gpg;

    /**
     * Have GPG
     *
     * This is used to determine whether the application has access to
     * the GPG functions
     */
    public $have_GPG = true;

    /**
     * Stype
     *
     * Whether the script should use procedural or object oriented functions.
     */
    public $style;

    /**
     * Version
     *
     * Record the GnuPG extensions version here.
     */
    public $version;

    /**
     * Construct
     *
     * In constructing this class we check if the gnupg OOP class is
     * available, or if the procedural functions are there instead.
     * Loads the current version of the GPG extension if it's available.
     */
    public function __construct()
    {
        $home = dirname(__FILE__) . '/../storage/.gnupg';
        if (!file_exists($home))
            mkdir($home, 0700);

        putenv("GNUPGHOME={$home}");

        if (class_exists('gnupg')) {

            $this->gpg = new gnupg();
            $this->style = 'oop';
            $this->version = phpversion('gnupg');
        }
        if (function_exists('gnupg_init')) {

            $this->gpg = gnupg_init();
            // Detect whether functions are procedural or object-oriented.
            $this->style = 'proc';
            $this->version = phpversion('gnupg');

        }
    }

    /**
     * Have GPG
     *
     * Returns whether the GPG extension has been detected.
     */
    public function have_GPG()
    {
        return $this->have_GPG;
    }

    // Import a public key.
    /**
     * Import
     *
     * Imports an ASCII armored PGP key. Searches the $ascii input to
     * see if it contains valid GPG headers, and tries to import the key.
     * If the import is successful, then $info['fingerprint'] will be set,
     * and we can return an array with a santizied (htmlentities) key
     * and fingerprint.
     * Returns FALSE on failure.
     *
     * @param    string $ascii
     * @return    string/FALSE
     */
    public function import($ascii)
    {
        $start = strpos($ascii, '-----BEGIN PGP PUBLIC KEY BLOCK-----');
        $end = strpos($ascii, '-----END PGP PUBLIC KEY BLOCK-----') + 34;

        $key = substr($ascii, $start, ($end - $start));

        if ($this->style == 'oop') {
            $info = $this->gpg->import($key);
        } else if ($this->style == 'proc') {
            $info = gnupg_import($this->gpg, $key);
        }
        if (isset($info['fingerprint'])) {
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
     * @param        string $fingerprint
     * @param        string $plaintext
     * @return        string
     */
    public function encrypt($fingerprint, $plaintext)
    {
        if ($this->style == 'oop') {

            if ($this->gpg->addencryptkey($fingerprint) == FALSE)
                return FALSE;
            $ciphertext = $this->gpg->encrypt($this->gpg, "$plaintext\n");
        } else if ($this->style == 'proc') {

            if (gnupg_addencryptkey($this->gpg, $fingerprint) == FALSE)
                return FALSE;

            $ciphertext = gnupg_encrypt($this->gpg, "$plaintext\n");
        }

        $full_crypt = "-----BEGIN PGP MESSAGE-----\n" . substr($ciphertext, 28);

        return $full_crypt;
    }
}

;

/* End of file Gpg.php */
