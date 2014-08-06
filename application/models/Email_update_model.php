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
     * This function attempts to verify the details given by the user (via form or link)
     * and will update the users email address if they are correct.
     *
     * @param $identifier
     * @param $subject
     * @param $activation_hash
     * @return bool|string
     */
    public function attempt_email_activation($identifier, $subject, $activation_hash)
    {
        $q = $this->db
            ->select('id, user_id, email_address, activated')
            ->get_where('email_update_requests', array(
                    'expire_time >' => time(),
                    'activated' => '0',
                    $identifier => $subject,
                    'activation_hash' => $activation_hash)
            );

        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            // Return 'activated' if already activated or a boolean indicating whether activation was successful.
            if ($row['email_activated'] == '1') {
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

}

;