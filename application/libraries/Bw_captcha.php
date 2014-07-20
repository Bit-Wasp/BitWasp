<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Captcha Library
 *
 * Library for the generation, and checking, of captcha requests.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Captcha
 * @author        BitWasp
 */
class Bw_captcha
{

    /**
     * CI
     */
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
    public function __construct()
    {
        $this->CI = & get_instance();

        $this->CI->load->helper('captcha');
        $this->CI->load->library('image');

    }

    /**
     * Check
     *
     * Checks the entered $answer to see if it correspondes to the
     * captchakey stored in the session
     *
     * @param        string $answer
     * @return        bool
     */
    public function check($answer)
    {
        $success = ($answer == $this->CI->session->userdata('captha_sol')) ? TRUE : FALSE;
        $this->CI->session->unset_userdata('captha_sol');
        return $success;
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
     * @return        string
     */
    public function generate()
    {
        // Check if there is a challenge set for this user. Delete old and create a new one.
        // Either way, the timed removal of old captchas will fix this sort of thing.

        // list all possible characters, similar looking characters and vowels have been removed
        $possible = '23456789bcdfghjkmnpqrstvwxyz';
        $characters = '';
        $i = 0;

        // create a captcha based on supplied length.
        while ($i < $this->CI->bw_config->captcha_length) {
            $characters .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            $i++;
        }

        // Array to pass to CI captcha helper.
        $config = array('word' => $characters,
            'img_path' => '/tmp/',
            'img_url' => base_url() . '',
            'font_path' => 'assets/font.ttf',
            'img_width' => '218',
            'base64' => TRUE
        );

        // Create captcha from the config data.
        $captcha = create_captcha($config);

        // Load the base64 image into memory and then erase the file.
        $image = $captcha['image'];

        $this->CI->session->set_userdata('captha_sol', $characters);

        return $image;
    }

}

;

/* End of File: Bw_captcha.php */
