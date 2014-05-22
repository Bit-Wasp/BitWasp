<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Review Auth Model
 *
 * This class handles the database functions for creating/deleting review
 * auth tokens, and loading the type of review the user is allowed to leave.
 *
 * @package    BitWasp
 * @subpackage    Models
 * @category    Review_Auth_Model
 * @author    BitWasp
 */
class Review_auth_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Load Review State
     *
     * This function accepts an $auth_token, and an $order_id. These
     * are used along with Current_User::$user_hash to work out if there
     * is an auth_token to be used. If so, it returns the type of form
     * review to be left. Otherwise, the user was not granted permission,
     * or used their permission to leave a review already.
     *
     * @param    string $auth_token
     * @param    int $order_id
     * @return    string/FALSE
     */
    public function load_review_state($auth_token, $order_id)
    {
        $query = $this->db->select('review_type, order_id')
            ->where('auth_token', $auth_token)
            ->where('order_id', $order_id)
            ->where('user_hash', $this->current_user->user_hash)
            ->get('review_auth_tokens');

        if ($query->num_rows() == 0)
            return FALSE;

        return $query->row_array();
    }

    /**
     * User Tokens By Order
     *
     * This function accepts an $order_id and uses this, with the
     * Current_User::$user_hash to find any auth_tokens the user has to
     * use. If none are found, FALSE is returned. If the user has the
     * authorization to leave feedback, we populate an array indexed by
     * the order_id, to allow easy checking later. If the user has a token
     * for order X, then show the link in that order.
     *
     * @param    int $order_id
     * @return    array/FALSE
     */
    public function user_tokens_by_order($order_id)
    {
        $this->db->select('auth_token, order_id');

        if (is_array($order_id)) {
            $c = 0;
            foreach ($order_id as $id) {
                $query = "order_id='{$id}' AND user_hash='{$this->current_user->user_hash}'";
                ($c++ > 0) ? $this->db->or_where($query, NULL, FALSE) : $this->db->where($query, NULL, FALSE);
            }
        } else {
            $this->db->where("order_id='{$order_id}' AND user_hash='{$this->current_user->user_hash}'");
        }

        $query = $this->db->get('review_auth_tokens');
        if ($query->num_rows() == 0)
            return FALSE;

        $results = array();
        foreach ($query->result_array() as $record) {
            $results[$record['order_id']] = $record['auth_token'];
        }
        return $results;
    }

    /**
     * Clear Review Permissions
     *
     * Accepts an $order_id, and attempts to delete the record from the
     * auth table. Returns a boolean indicating success.
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function clear_user_auth($order_id)
    {
        $this->db->where('order_id', $order_id);
        $this->db->where('user_hash', $this->current_user->user_hash);
        return $this->db->delete('review_auth_tokens') == TRUE;
    }

    /**
     * Issue Tokens For Order
     *
     * This function accepts an $order_id, and creates review authorization
     * tokens for the buyer and the vendor. The buyer's token is created
     * with the 'vendor' review type. This allows them to rate the vendor
     * and the items on the review form. The vendors token is created with
     * the 'buyer' review type, which only allows them to rate the buyer.
     *
     * @param    int $order_id
     * @return    boolean
     */
    public function issue_tokens_for_order($order_id)
    {
        $this->load->model('order_model');
        $order = $this->order_model->get($order_id);

        return ($this->create($order['buyer']['user_hash'], $order_id, 'buyer')
            && $this->create($order['vendor']['user_hash'], $order_id, 'vendor'));
    }

    /**
     * Create Token
     *
     * This function creates an auth token, which allows a user ($user_hash)
     * to create a review of $order_type, for order number $order_id.
     * These auth_tokens are expected along with the order_id when a user
     * want's to leave feedback. $review_type can have options 'buyer',
     * 'vendor' - 'item' is not exposed here, but comes from the 'buyer'
     * reviews.
     *
     * @param    string $user_hash
     * @param    int $order_id
     * @param    string $review_type
     * @return    bool
     */
    public function create($user_hash, $order_id, $review_type)
    {
        $auth_token = $this->general->unique_hash('review_auth_tokens', 'auth_token', '64');

        $insert = array('auth_token' => $auth_token,
            'user_hash' => $user_hash,
            'review_type' => $review_type,
            'order_id' => $order_id);
        return $this->db->insert('review_auth_tokens', $insert) == TRUE;
    }
}

;


/* End of File: Review_auth_model.php */
/* Location: application/models/Review_auth_model.php */