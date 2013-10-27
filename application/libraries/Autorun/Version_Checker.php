<?php

/**
 * Version Checker
 *
 * This job is used to check for an updated version of BitWasp on GitHub
 * and is run once a week. Called by the autorun library, and reports 
 * new version information to the admin via the logging system.
 * 
 * @package		BitWasp
 * @subpackage	Autorun
 * @category	Version Checker
 * @author		BitWasp
 */
class Version_Checker {

	/**
	 * Config
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */
	public $config = array(	'name' => 'Version Check',
							'description' => 'An autorun job to check for updates to the BitWasp source code.',
							'index' => 'version_checker',
							'interval' => '7',
							'interval_type' => 'days');
	public $CI;
	
	/**
	 * Constructor
	 * 
	 * Load's the CodeIgniter framework.
	 */
	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->model('logs_model');
	}

	/**
	 * Job
	 * 
	 * This function is called by the Autorun script. 
	 */
	public function job() {
	
		$pluggable_timestamp = '0';

		$branch = json_decode($this->call_curl('https://api.github.com/repos/Bit-Wasp/BitWasp/branches'));
		if(is_array($branch)){
			
			$commit = json_decode($this->call_curl($branch[0]->commit->url));
			if(is_object($commit)){
				$timestamp = strtotime($commit->commit->author->date);
				if($timestamp > $pluggable_timestamp){
					echo 'Due an upgrade';
				}
			} else {
				return FALSE; 
			}
				/*
			if($branch !== FALSE && count($branch) > 0){
				
				$commit = $this->call_curl($branches[0]->commit->url);
				$commit = json_decode($commit);
				var_dump($commit);echo '<br />';
				if(isset($commit[0]) !== FALSE && count($commit[0]) > 0){
					$timestamp = strtotime($commit[0]->author->date);
					if($timestamp > $pluggable_timestamp)
						echo 'Need an upgrade<br />';
				}
			}*/
		} else {
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function call_curl($url, $proxy_arr = array()){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_REFERER, "");
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36");
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		if(isset($source['proxy']) && is_array($source['proxy'])){
			curl_setopt($curl, CURLOPT_PROXYTYPE, $proxy_arr['type']);
			curl_setopt($curl, CURLOPT_PROXY, $proxy_arr['url']);
		}
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	
	public function add($caller, $title, $message, $level){
		return ($this->db->insert('logs', array('caller' => $caller,
												'title' => $title, 
												'level' => $level,
												'hash' => $this->general->unique_hash('logs','hash'),
												'message' => $message)) == TRUE
				) ? TRUE : FALSE;
			
	}
	
};
