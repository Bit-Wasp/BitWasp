<?php
/**
 * Autorun library
 *
 * This library automatically loads jobs, checks if there newly created 
 * ones (found in application/libraries/Autorun/) or runs jobs at a 
 * specified interval.
 * 
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Autorun
 * @author		BitWasp
 */

class Autorun {

	public $CI; 
	
	/**
	 * Defaults
	 * 
	 * Load default information from the classes.
	 */
	public $defaults = array();
	
	/**
	 * Path
	 * 
	 * This variable is used to generate the location of Autorun scripts.
	 */
	public $path;
	
	// Need to store the fully generated array for later on, if
	// the admin wants to change things.
	
	public function __construct() { 
		$this->CI = &get_instance();
		$this->CI->load->model('autorun_model');
	
		$this->path = APPPATH.'libraries/Autorun/';
		
		$jobs = $this->CI->autorun_model->load_all();
		
		foreach(glob($this->path."*.php") as $filename){
			$class_name = pathinfo($filename, PATHINFO_FILENAME);
			
			require_once($filename);
			$class = new $class_name;
			
			// If the job isn't in the job's list, add it.
			if(!isset($jobs[$class->config['index']])){
				$config = $class->config;
				$config = array_map('htmlentities', $config);
				$this->CI->autorun_model->add($class->config);
				
				// Run the job & record that it's been updated.
				if($class->job() == TRUE)
					$this->CI->autorun_model->set_updated($class->config['index']);
					
			} else {
				$job = $jobs[$class->config['index']];
				
				// If the interval has passed. Run again!
				if( $job['interval'] !== '0' && $job['last_update'] < (time()-$job['interval_s'])){
					if($class->job() == TRUE)
						$this->CI->autorun_model->set_updated($class->config['index']);					
				}
			}
			
			array_push($this->defaults, $class->config);
			unset($class);
		}
	}
};


