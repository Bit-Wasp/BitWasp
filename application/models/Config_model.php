<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Config Model
 *
 * This class handles the retrieval/updating of the configuration settings
 * from the database.
 *
 * @package        BitWasp
 * @subpackage    Model
 * @category    Config
 * @author        BitWasp
 *
 */
class Config_model extends CI_Model
{

    /**
     * Config
     *
     * Store the sites config here for easy access
     */
    public $config;

    /**
     * Constructor
     *
     * Loads the current config into $this->config. Can specify different
     * config rows, but yet to be implemented.
     */
    public function __construct()
    {
        parent::__construct();
        $query = $this->db->get('config');
        if ($query->num_rows() > 0) {
            $this->config = $query->result_array();
            foreach ($query->result_array() as $config) {
                $this->config[$config['parameter']] = $config['value'];
            }
        } else {
            $this->config = FALSE;
        }
    }

    /**
     * Get
     *
     * Return the current config as loaded from the database.
     *
     * @access    public
     * @return    array
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * Update
     *
     * Update column in the config row. Indexes are column name.
     *
     * @access    public
     * @param    array $records
     * @return    boolean
     */
    public function update($records)
    {
        $success = TRUE;
        foreach ($records as $key => $update) {
            $this->db->where('parameter', "$key");
            if ($this->db->update('config', array('value' => $update)) !== TRUE)
                $success = FALSE;
        }
        return $success;
    }

    /**
     * Create
     *
     * Create an entry in the config table. Setting name is set by $parameter,
     * and it's value is set by $value.
     *
     * @param    string $parameter
     * @param    string $value
     * @return    boolean
     */
    public function create($parameter, $value)
    {
        $insert = array('parameter' => $parameter,
            'value' => $value);
        return ($this->db->insert('config', $insert) == TRUE) ? TRUE : FALSE;
    }

}

;

/* End of File: Config_Model.php */
/* Location: application/models/Config_model.php */