<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Price Index Job.
 *
 * This job is used to update exchange rates. Called by the autorun library.
 * It is run periodically (default is 10 minutes) to update the exchange reates on record.
 *
 * @package        BitWasp
 * @subpackage    Autorun
 * @category    Price Index
 * @author        BitWasp
 */
class Price_Index
{

    /**
     * Config
     *
     * This stores predefined information about the job, such as the name,
     * description, and the frequency at which it should be run.
     */
    public $config = array('name' => 'Bitcoin Price Index',
        'description' => 'An autorun job to update the Bitcoin exchange rates.',
        'index' => 'price_index',
        'interval' => '0',
        'interval_type' => 'minutes');
    public $CI;

    /**
     * Constructor
     *
     * Load's the CodeIgniter framework and the Bitcoin library.
     */
    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('bw_bitcoin');
    }

    /**
     * Job
     *
     * This function is called by the Autorun script.
     * If the Price Indexing is not disabled, run the update script and
     * record the new update.
     */
    public function job()
    {
        $stat = FALSE;
        if ($this->CI->bw_config->price_index !== 'Disabled')
            if ($this->CI->bw_bitcoin->ratenotify() == TRUE) {
                $stat = TRUE;
                $this->CI->autorun_model->set_updated('price_index');
            }
        return $stat;
    }

}

;
