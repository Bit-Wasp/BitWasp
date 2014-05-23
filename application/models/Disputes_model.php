<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Disputes Model
 *
 * This class handles database queries for disputes.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Disputes
 * @author        BitWasp
 */
class Disputes_model extends CI_Model
{

    /**
     * Constructor
     *
     * Load libs/models.
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get
     *
     * This function gets a dispute array, or returns FALSE. Loads by
     * the $dispute_id.
     *
     * @param    int $dispute_id
     * @return    array/FALSE
     */
    public function get($dispute_id)
    {
        $query = $this->db->where('id', $dispute_id)->get('disputes');

        if ($query->num_rows() > 0) {
            return $this->create_dispute_info_array($query->row_array());
        } else {
            return FALSE;
        }
    }

    /**
     * Create Dispute Info Array
     *
     * Takes an array containing {time, dispute_message, last_update, id,
     * disputing_user_id}, and processes the time stamps, treats the message,
     * loads update messages, and the disputing_user_hash/name.
     *
     * @param    array $row
     * @return    array
     */
    public function create_dispute_info_array($row)
    {
        $row['time_f'] = $this->general->format_time($row['time']);
        $row['dispute_message'] = nl2br($row['dispute_message']);
        $row['last_update_f'] = $this->general->format_time($row['last_update']);
        $row['updates'] = $this->get_dispute_updates($row['id']);

        $query = $this->db->select('user_hash, user_name')->where('id', $row['disputing_user_id'])->get('users');
        $tmp = $query->row_array();
        $row['disputing_user_hash'] = $tmp['user_hash'];
        $row['disputing_user_name'] = $tmp['user_name'];
        return $row;
    }

    /**
     * Get Dispute Updates
     *
     * This function loads updates regarding a dispute.
     *
     * @param    array $dispute_id
     * @return    boolean
     */
    public function get_dispute_updates($dispute_id)
    {
        $this->db->where('dispute_id', $dispute_id);
        $query = $this->db->get('disputes_updates');
        if ($query->num_rows() == 0)
            return array();

        $result = $query->result_array();
        foreach ($result as &$tmp) {
            $tmp['time_f'] = $this->general->format_time($tmp['time']);
            if ($tmp['posting_user_id'] !== '0') {
                $this->db->select('user_hash, user_name');
                $this->db->where('id', $tmp['posting_user_id']);
                $query = $this->db->get('users');
                $row = $query->row_array();
                $tmp['posting_user_hash'] = $row['user_hash'];
                $tmp['posting_user_name'] = $row['user_name'];
            }

        }
        return $result;
    }

    /**
     * Get By Order ID
     *
     * Same as get(), but works by querying by the order id.
     *
     * @param    int $order_id
     * @return    array/FALSE
     */
    public function get_by_order_id($order_id)
    {
        $query = $this->db->where('order_id', $order_id)->get('disputes');
        if ($query->num_rows() > 0) {
            return $this->create_dispute_info_array($query->row_array());
        } else {
            return FALSE;
        }
    }

    /**
     * Disputes List
     *
     * Loads a list of disputes for an administrator
     *
     * @return array
     */
    public function disputes_list()
    {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('disputes');
        if ($query->num_rows() == 0)
            return array();

        $results = $query->result_array();
        foreach ($results as &$row) {
            $row['dispute_message'] = substr(strip_tags(nl2br($row['dispute_message'])), 0, 50) . "...";
            $row['disputing_user'] = $this->accounts_model->get(array('id' => $row['disputing_user_id']));
            $row['other_user'] = $this->accounts_model->get(array('id' => $row['other_user_id']));
            $row['last_update_f'] = $this->general->format_time($row['last_update']);
        }
        return $results;
    }

    /**
     * Create
     *
     * @param    array $array
     * @return    boolean
     */
    public function create($array)
    {
        return $this->db->insert('disputes', $array) == TRUE;
    }

    /**
     * Set Final Response
     *
     * This function marks that the dispute (for $order_id) has been marked
     * as resolved, prevents users from posting further messages on a
     * dispute.
     * This will be called in order_finalized_callback for escrow disputed
     * orders, or will be triggered by the admin
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function set_final_response($order_id)
    {
        $this->db->where('order_id', $order_id);
        return $this->db->update('disputes', array('final_response' => '1')) == TRUE;
    }

    /**
     * Post Dispute Update
     *
     * @param    array $array
     * @return    boolean
     */
    public function post_dispute_update($array)
    {
        $array['time'] = time();
        return $this->db->insert('disputes_updates', $array) == TRUE;
    }
}

;

