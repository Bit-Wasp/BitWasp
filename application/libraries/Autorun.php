<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Autorun Library
 *
 * This library automatically loads jobs, checks if there newly created
 * ones (found in application/libraries/Autorun/) or runs jobs at a
 * specified interval.
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Autorun
 * @author        BitWasp
 */
class Autorun
{

    /**
     * CI
     */
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

    /**
     * Jobs
     *
     * This will store the jobs so they can be re-called if there are any
     * changes we can rerun the job.
     */
    public $jobs = array();

    /**
     * Constructor
     *
     * This initiates and runs each autorun job. Each time the class is
     * loaded, we scan the Autorun path for newly added jobs. If there are
     * some unrecognized ones, we add their default information to the
     * autorun table and run the job for the first time.
     * If the job is recognized, we check the autorun table to see when
     * it was last run, and the frequency the job is to be run. If the
     * interval has passed, we run the job again & record the details on
     * the table
     * All jobs are stored to an array, where they can be accessed through
     * the rest of the application (if any changes are being updated, we
     * should re-run the job).
     *
     * @param        boolean $run_jobs
     */
    public function __construct($run_jobs = TRUE)
    {
        $this->CI = & get_instance();
        $this->CI->load->model('autorun_model');

        $this->path = APPPATH . 'libraries/Autorun/';

        $jobs = $this->CI->autorun_model->load_all();

        foreach (glob($this->path . "*.php") as $filename) {
            $class_name = pathinfo($filename, PATHINFO_FILENAME);

            require_once($filename);
            $class = new $class_name;

            // If the job isn't in the job's list, add it.
            if ($run_jobs == TRUE) {
                if (!isset($jobs[$class->config['index']])) {
                    $def_config = $class->config;
                    $config = array_map('htmlentities', $def_config);
                    $this->CI->autorun_model->add($config);

                    // Run the job & record that it's been updated.
                    if ($def_config['interval'] !== '0')
                        if ($class->job() == TRUE)
                            $this->CI->autorun_model->set_updated($class->config['index']);

                } else {
                    $job = $jobs[$class->config['index']];

                    // If the interval has passed. Run again!
                    if ($job['interval'] !== '0' && $job['last_update'] < (time() - $job['interval_s'])) {
                        if ($class->job() == TRUE)
                            $this->CI->autorun_model->set_updated($class->config['index']);
                    }
                }
            }

            array_push($this->defaults, $class->config);
            $this->jobs[$class->config['index']] = $class;
            unset($class);
        }
    }
}

;

/* End of File: Autorun.php */
