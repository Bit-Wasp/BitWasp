<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Admin Model
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Admin
 * @author        BitWasp
 *
 */
class Admin_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Format Config Changes
     *
     * This function formats the array that is supplied to update the
     * sites configuration into a log message for the admin.
     *
     * @param    array $changes
     * @return    string
     */
    public function format_config_changes($changes)
    {
        ob_start();
        echo "<pre>\n";
        foreach ($changes as $parameter => $value) {
            echo "$parameter - $value<br />\n";
        }
        echo "</pre>";
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Set Mode
     *
     * This function is used to toggle maintenance mode for the site.
     * If turning maintenance mode on, it will make a copy of the sites
     * current configuration, and back this up. Then it will make the
     * following changes to the config table: allow_registration is set
     * to '0'; allow_guests is set to '0'; refund_after_inactivity='0'; delete_messages_after='0';
     * delete_transactions_after='0';
     * It will also affect the autorun table, probably by disabling
     * everything.
     *
     * $setting can be 'maintenance' or 'normal'
     *
     * @param    string $setting
     * @return    boolean
     */
    public function set_mode($setting)
    {
        $this->load->model('autorun_model');

        if ($setting == 'maintenance') {
            // Disable autorun & Back up autorun settings.
            $autorun = $this->autorun_model->load_all();
            $autorun_array = array();
            foreach ($autorun as $index => $job) {
                $autorun_array[$index] = $job['interval'];
            }
            if ($this->disable_autorun($autorun_array) == TRUE)
                $changes['autorun_preserve'] = json_encode($autorun_array);

            // Back up the settings we are about to change.
            $backup_settings = array('registration_allowed' => (int)$this->bw_config->registration_allowed,
                'allow_guests' => (int)$this->bw_config->allow_guests);
            $changes['settings_preserve'] = json_encode($backup_settings);

            // Change some settings to lock down the site.
            $changes['registration_allowed'] = '0';
            $changes['allow_guests'] = '0';
            $changes['maintenance_mode'] = '1';

            // Commit backups and changes.
            if ($this->config_model->update($changes) == FALSE)
                return FALSE;

        } else if ($setting == "online") {

            // Load the autorun backup and restore it.
            $autorun_backup = (array)json_decode($this->bw_config->autorun_preserve);
            if ($this->restore_autorun($autorun_backup) == TRUE)
                $changes['autorun_preserve'] = '';

            // Load the settings backup and restore it.
            $settings_backup = (array)json_decode($this->bw_config->settings_preserve);
            $this->config_model->update($settings_backup);

            $changes['setting_preserve'] = '';
            $changes['maintenance_mode'] = '0';

            // Commit changes
            if ($this->config_model->update($changes) == FALSE)
                return FALSE;
        }
        return TRUE;
    }

    /**
     * Disable Autorun
     *
     * Pass an array indexed by the job's ID into the autorun table, and
     * will set the interval for each of those ID's to 0. This will disable
     * the job. Returns TRUE on success, and FALSE on failure.
     *
     * @param    array $array
     * @return    boolean
     */
    public function disable_autorun($array)
    {
        $success = TRUE;
        $keys = array_keys($array);
        foreach ($keys as $index) {
            if ($this->autorun_model->set_interval($index, '0') == FALSE)
                $success = FALSE;
        }
        return $success;
    }

    /**
     * Restore Autorun
     *
     * Pass an array indexed by the job's ID in the autorun table, and
     * the corresponding interval from the backup. This will restore
     * the settings which were present before the site went into
     * maintenance mode.
     *
     * @param $array
     * @return bool
     */
    public function restore_autorun($array)
    {
        $success = TRUE;
        foreach ($array as $index => $interval) {
            if ($this->autorun_model->set_interval($index, $interval) == FALSE)
                $success = FALSE;
        }
        return $success;
    }

}

/* End of file Admin_model.php */
/* Location: application/models/Admin_model.php */
