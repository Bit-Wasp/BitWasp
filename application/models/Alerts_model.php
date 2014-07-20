<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Alerts Model
 *
 * Alert messages are stored by Bitwasp to prevent the marketplace from
 * reacting to something that the admin has already responded to. Ie,
 * if a github/bitcoin alert occurs, the message is stored, so that if the
 * admin fixes the issue, the script won't trigger a maintenance_mode/log
 * if it hears about it again.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Alerts
 * @author        BitWasp
 *
 */
class Alerts_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add
     *
     * Store an alert in the table. Array requires parameters 'source',
     * and 'message'. If time is not supplied, it will be stored as the
     * time the site heard about the alert.
     * @param    array $alert
     * @return    boolean
     */
    public function add($alert)
    {
        if (!isset($alert['time']))
            $alert['time'] = time();

        return $this->db->insert('alerts', $alert) == TRUE;
    }

    /**
     * Check
     *
     * Check if an alert message has been seen already.
     *
     * @param    string $string
     * @return    boolean
     */
    public function check($string)
    {
        $query = $this->db->get_where('alerts', array('message' => "$string"));
        return $query->num_rows() > 0;
    }
}

;

/* End of File: alerts_model.php */
