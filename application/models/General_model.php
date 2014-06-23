<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * General Model
 *
 * General model with some small functions.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    General
 * @author        BitWasp
 */
class General_model extends CI_Model
{

    /**
     * Construct
     *
     * @access    public
     * @return    void
     */
    public function __construct()
    {
    }

    /**
     * Check Unique Entry
     *
     * This function will check that the supplied $entry is unique in
     * the $table $column. Returns a boolean indicating Success/Failure.
     *
     * @param    string $table
     * @param    string $column
     * @param    string $entry
     * @return    bool
     */
    public function check_unique_entry($table, $column, $entry)
    {
        return $this->db->where($column, $entry)->count_all_results($table) == 0;
    }

    /**
     * Rows Before Time
     *
     * Return rows in $table with a timestamp before $time.
     *
     * @param    string $table
     * @param    int $time
     * @return    bool
     */
    public function rows_before_time($table, $time)
    {
        $query = $this->db->where("time <", "$time")->get($table);
        return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
    }

    /**
     * Count Entries
     *
     * Count the total number of entries in a table, specified by $table.
     *
     * @param    string $table
     * @return    int/FALSE
     */
    public function count_entries($table)
    {
        return $this->db->count_all($table);
    }

    /**
     * Count Unread Messages
     *
     * Count the number of unread messages for the current user.
     *
     * @return    int
     */
    public function count_unread_messages()
    {
        return $this->db->select('id')->where('to', $this->current_user->user_id)->where('viewed', '0')->from('messages')->count_all_results();
    }

    /**
     * Count New Orders
     *
     * Count orders at progress=1 for the currently logged in user.
     *
     * @return    int
     */
    public function count_new_orders()
    {
        $this->db->select('id');
        $this->db->where('vendor_hash', $this->current_user->user_hash);
        $this->db->where('progress', '1');
        $this->db->from('orders');
        return $this->db->count_all_results();
    }

}

;

/* End of File: General_model.php */
/* Location: application/models/General_model.php */
