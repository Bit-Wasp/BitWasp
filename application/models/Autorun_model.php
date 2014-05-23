<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Autorun Model
 *
 * This class handles handles database queries regarding currencies.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Autorun
 * @author        BitWasp
 *
 */
class Autorun_model extends CI_Model
{

    /**
     * Constructor
     *
     * @access    public
     * @return    void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add
     *
     * Used to create a new autorun job. Done automatically when a new
     * job is found in the table, or created by the admin.
     *
     * @access    public
     * @param    array $config
     * @return    bool
     */
    public function add($config)
    {
        return ($this->db->insert('autorun', $config) == TRUE) ? TRUE : FALSE;
    }

    /**
     * Load All
     *
     * Load all autorun information on record.
     *
     * @access    public
     * @param    int
     * @return    bool
     */
    public function load_all()
    {
        $query = $this->db->get('autorun');
        if ($query->num_rows() == 0)
            return FALSE;

        $results = array();
        foreach ($query->result_array() as $result) {
            $row = $result;

            switch ($result['interval_type']) {
                case 'seconds':
                    $row['interval_s'] = $result['interval'];
                    break;
                case 'minutes':
                    $row['interval_s'] = $result['interval'] * 60;
                    break;
                case 'hours':
                    $row['interval_s'] = $result['interval'] * 60 * 60;
                    break;
                case 'days':
                    $row['interval_s'] = $result['interval'] * 24 * 60 * 60;
                    break;
                default:
                    continue; // Skip obviously malformed job.
                    break;
            }

            $row['time_f'] = $this->general->format_time($result['last_update']);
            $results[$result['index']] = $row;
        }
        return $results;
    }

    /**
     * Set Updated
     *
     * Update the last_update entry for the task.
     *
     * @access    public
     * @param    string $index
     * @return    bool
     */
    public function set_updated($index)
    {
        $this->db->where('index', $index);
        return ($this->db->update('autorun', array('last_update' => time()))) ? TRUE : FALSE;
    }

    /**
     * Set Interval
     *
     * Set the interval for a particular job.
     *
     * @access    public
     * @param    string $index
     * @param    int $interval
     * @return    bool
     */
    public function set_interval($index, $interval)
    {
        $this->db->where('index', $index);
        return ($this->db->update('autorun', array('interval' => "$interval"))) ? TRUE : FALSE;
    }


}

;
