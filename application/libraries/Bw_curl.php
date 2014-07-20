<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * cURL Library
 *
 * Tiny library to make curl requests through.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Config
 * @author        BitWasp
 */
class Bw_curl
{

    protected $CI;

    /**
     * Proxy URL
     *
     * This variable contains the proxy url. It is set by default by
     * the config item $global_proxy_url, however can be replaced by
     * supplying an array parameter to the constructor function.
     */
    public $proxy_url = '';

    /**
     * Proxy Type
     *
     * This variable contains the proxy type, as supplied by a form or
     * config entry. May be either Disabled, HTTP or SOCKS5.
     */
    public $proxy_type = '';

    /**
     * Curl Proxy Type
     *
     * Once a $proxy_type is set, by $override or config, the constructor
     * will determine the curl proxy type. This is the value passed to
     * CURL to say what type of proxy to use.
     */
    public $curl_proxy_type = '';

    /**
     * Type Ref
     *
     * This variable contains the definitions for the curl proxy type.
     * If $proxy_type is set by $override or a config entry, the script
     * will use this reference for the proxy type.
     */
    public $type_ref = array('HTTP' => CURLPROXY_HTTP,
        'SOCKS5' => CURLPROXY_SOCKS5);

    /**
     * Construct
     *
     * This function loads the CodeIgniter class, and loads any proxy
     * settings from the config class. An $override array can be passed,
     * which will replace any of the proxy settings loaded in memory.
     * This is used to test a new proxy.
     *
     * @param    array $override
     * @return    void
     */
    public function __construct($override = NULL)
    {
        $this->CI = & get_instance();

        // Load the proxy settings from the config library.
        $this->proxy_type = $this->CI->bw_config->global_proxy_type;
        if ($this->proxy_type !== 'Disabled') {
            $this->curl_proxy_type = $this->type_ref[$this->proxy_type];
            $this->proxy_url = $this->CI->bw_config->global_proxy_url;
        }

        // If the $override parameter is supplied, configure these.
        if (is_array($override)) {
            $this->proxy_type = $override['proxy_type'];
            $this->curl_proxy_type = $this->type_ref[$this->proxy_type];
            $this->proxy_url = $override['proxy_url'];
        }
    }

    /**
     * GET Request
     *
     * This function will send a GET request for the supplied URL.
     * The function will check for any proxy settings, and use those
     * if found. Returns a string with the results if successful, and
     * FALSE on failure.
     *
     * @param    string $url
     * @return    string/FALSE
     */
    public function get_request($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_REFERER, "");
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36");
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        if ($this->curl_proxy_type !== '') {
            curl_setopt($curl, CURLOPT_PROXYTYPE, $this->curl_proxy_type);
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy_url);
        }

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

}

;

/* End of file: Bw_curl.php */
