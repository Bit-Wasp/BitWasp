<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$ini = parse_ini_file(dirname(__FILE__) . '/../../../version.ini');
define('BITWASP_CREATED_TIME', $ini['bitwasp_created_time']);

/**
 * Version Checker
 *
 * This job is used to check for an updated version of BitWasp on GitHub
 * and is run once a week. Called by the autorun library, and reports
 * new version information to the admin via the logging system.
 *
 * @package        BitWasp
 * @subpackage    Autorun
 * @category    Version Checker
 * @author        BitWasp
 */
class Version_Checker
{

    /**
     * Config
     *
     * This stores predefined information about the job, such as the name,
     * description, and the frequency at which it should be run.
     */
    public $config = array('name' => 'Version Check',
        'description' => 'An autorun job to check for updates to the BitWasp source code.',
        'index' => 'version_checker',
        'interval' => '0',
        'interval_type' => 'days');
    public $CI;

    /**
     * Constructor
     *
     * Load's the CodeIgniter framework.
     */
    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('bw_curl');
    }

    /**
     * Job
     *
     * This function is called by the Autorun script.
     */
    public function job()
    {

        $latest_version = parse_ini_string($this->CI->bw_curl->get_request('https://raw.github.com/Bit-Wasp/BitWasp/master/version.ini'));

        // Check the recent commits for an alert message
        $alert = $this->check_alerts();
        if ($alert !== FALSE) {
            $this->CI->load->model('alerts_model');

            // If the site has never seen this alert before, proceed:
            if ($this->CI->alerts_model->check($alert['message']) == FALSE) {

                // Log a message for the admin
                $log_message = "A serious alert has been trigged by the Bitwasp developers on " . $alert['date'] . ":<br />" . $alert['message'] . "<br />";
                $this->CI->logs_model->add('BitWasp Developers', 'Please respond to serious alert', $log_message, 'Alert');
                unset($alert['date']);

                // Record the alert.
                $this->CI->alerts_model->add($alert);

                // If the site is not in maintenance mode, put it there now.
                if ($this->CI->bw_config->maintenance_mode == FALSE) {
                    $this->CI->load->model('admin_model');
                    $this->CI->admin_model->set_mode('maintenance');
                }
            }
        }

        if ($latest_version !== FALSE && BITWASP_CREATED_TIME !== FALSE) {
            if ($latest_version['bitwasp_created_time'] > BITWASP_CREATED_TIME) {
                $this->CI->load->model('logs_model');
                if ($this->CI->logs_model->add('Version Checker', 'New BitWasp code available', 'There is a new version of BitWasp available on GitHub. It is recommended that you download this new version (using ' . BITWASP_CREATED_TIME . ')', 'Info'))
                    return TRUE;
            }
        }
        return TRUE;
    }

    /**
     * Check Alerts
     *
     * This function will check the 10 most recent github entries for
     * any information about an alert. If an alert is detected, it will
     * leave an log for the admin, and put the site into maintenance mode.
     *
     * @return    boolean
     */
    public function check_alerts()
    {

        $repo = json_decode($this->CI->bw_curl->get_request("https://api.github.com/repos/Bit-Wasp/BitWasp/commits"));
        if ($repo == FALSE)
            return FALSE;

        $commit_limit = 10;
        $count = 0;
        foreach ($repo as $commit) {
            $commit = $commit->commit;

            // Stop checking after 10 commits.
            if ($count == $commit_limit)
                break;

            $timestamp = strtotime($commit->author->date);
            $message = htmlentities($commit->message);

            // If [ALERT] is in the message, return information about the alert.
            if (strpos($message, "[ALERT]") !== FALSE)
                return array('time' => $timestamp,
                    'date' => $commit->author->date,
                    'message' => $message,
                    'source' => 'GitHub');

            $count++;
        }
        return FALSE;
    }

}

;

/* End of File: Version_Checker.php */
