<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Accounts Model
 *
 * This class handles the database queries relating to changing a users email address,.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Accounts
 * @author        BitWasp
 */
class Email_update_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * New Update Request
     *
     * Store a new update in the request, pending verification by the user following the link.
     *
     * @param $array
     * @return bool
     */
    public function new_update_request($array)
    {
        return $this->db->insert('email_update_requests', $array) == TRUE;
    }

    /**
     * Attempt Email Activation
     *
     * This function attemmpts to verify the details given by the user (via form or link)
     * and will update the users email address if they are correct.
     *
     * @param $identifier
     * @param $subject
     * @param $activation_hash
     * @return bool|string
     */
    public function attempt_email_activation($identifier, $subject, $activation_hash)
    {
        // Take the most recent email, force query to expect that id.
        $by_user = $this->db
            ->select('e.user_id, max(r.id) as req_id')
            ->from('email_update_requests e')
            ->where('e.'.$identifier, $subject)
            ->join('email_update_requests r', 'r.user_id = e.user_id')
            ->get();

        // Refer to all emails for that subject, work out what the last addition was (by ID)
        if($by_user->num_rows() > 0){
            $val = $by_user->row_array();
            $this->db->where('id', $val['req_id']);
        }

        $q = $this->db
            ->select('id, user_id, email_address, activated')
            ->from('email_update_requests')
            ->where(array(
                'expire_time >' => time(),
                'activated' => '0',
                $identifier => $subject,
                'activation_hash' => $activation_hash
            ))
            ->get();

        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            // Return 'activated' if already activated or a boolean indicating whether activation was successful.
            if ($row['activated'] == '1') {
                return 'activated';
            } else {
                // Update user record
                $update = $this->db
                        ->where('id', $row['user_id'])
                        ->update('users', array(
                            'email_address' => $row['email_address'],
                            'email_activated' => '1'
                        )) == TRUE;

                if ($update) {
                    // If update successful, set this record as activated.
                    $this->db
                        ->where('id', $row['id'])
                        ->update('email_update_requests', array(
                            'activated' => '1'
                        ));
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Delete Request
     *
     * Deletes a $request_id which was created by that $user_id.
     *
     * @param $user_id
     * @param $request_id
     * @return bool
     */
    public function delete_request($user_id, $request_id)
    {
        return $this->db
            ->where(array(
                'user_id' => $user_id,
                'id' => $request_id
            ))
            ->delete('email_update_requests') == TRUE;
    }


    /**
     * Pending Verification
     *
     * This function takes a $user_id, and returns all emails which are pending verification.
     * Returns an array of entries of they exist, otherwise an empty array.
     *
     * @param $user_id
     * @return array
     */
    public function pending_verification($user_id)
    {
        return $this->db
            ->limit(1)
            ->order_by('time DESC')
            ->get_where('email_update_requests', array(
                'user_id' => $user_id,
                'expire_time >' => time(),
                'activated' => '0'))

            ->result_array();
    }
}

;

/* End of File: Email_update_model.php */
/* Location: application/models/Email_update_model.php */