<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Logs Model
 *
 * This model is used to record and display logs and debugging information
 * to Admins about the activity taking place on the server.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Logs
 * @author        BitWasp
 *
 */
class Logs_Model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fetch
     *
     * Fetches the list of log entries, or a specific log item (specified
     * by $hash). Returns an array describing an item if $hash is set,
     * a multi-dimensional array if returning a list, or FALSE on failure.
     *
     * @param    string $hash (optional)
     * @return    array/FALSE
     */
    public function fetch($hash = NULL)
    {
        // Load the whole list, or just one.
        if ($hash == NULL) {
            $query = $this->db->order_by('time desc')->get('logs');
        } else {
            $query = $this->db->get_where('logs', array('hash' => $hash));
        }

        if ($query->num_rows() > 0) {
            // If any records exist, process them and return.
            $results = $query->result_array();
            foreach ($results as &$result) {
                $result['time_f'] = $this->general->format_time($result['time']);
            }
            // Depending on whether $hash was unset, results will be an array
            // or jst a single entry.
            return ($hash == NULL) ? $results : $results[0];
        }
        return FALSE;
    }

    /**
     * Add
     *
     * Add a record to the log table. Specify the script/uri which is
     * recording the log ($caller), the title of the log ($title),
     * the log message ($message) and the warning level $level.
     * Info / Warning / Error / Alert
     *
     * @param $caller
     * @param $title
     * @param $message
     * @param $level
     * @return bool
     */
    public function add($caller, $title, $message, $level)
    {
        return $this->db->insert('logs', array('caller' => $caller,
            'title' => $title,
            'info_level' => $level,
            'hash' => $this->general->unique_hash('logs', 'hash'),
            'time' => time(),
            'message' => $message)) == TRUE;
    }
}

;

/* End of file logs_model.php */
/* Location: application/models/Logs_model.php */