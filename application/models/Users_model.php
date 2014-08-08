<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Users Model
 *
 * This class handles the database queries relating to users.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Users
 * @author        BitWasp
 *
 */
class Users_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add User.
     *
     * Add a user to the table. Use prepared statements..
     *
     * @access    public
     * @param    array $data
     * @param    string $token_info
     * @return    boolean
     */
    public function add($data, $token_info = NULL)
    {
        $ret = $this->db->insert('users', $data) == TRUE;
        if ($token_info !== null)
            $this->delete_registration_token($token_info['id']);

        return $ret;
    }

    /**
     * Delete Registration Token
     *
     * Delete a registration token as specified by $id.
     *
     * @param    int $id
     * @return    boolean
     */
    public function delete_registration_token($id)
    {
        return ($this->db->delete('registration_tokens', array('id' => $id)) == TRUE) ? TRUE : FALSE;
    }

    public function check_user_exists(array $user)
    {
        $key = array_keys($user);
        $key = $key[0];
        if (in_array($key, array('user_hash', 'id', 'user_name'))) {
            return $this->db->where($key, $user[$key])->from('users')->count_all_results() == 1;
        } else {
            return FALSE;
        }
    }

    /**
     * Deletes
     *
     * Deletes a user account.
     *
     * @access    public
     * @param    string $user_hash
     * @return    boolean
     */
    public function delete($user_hash)
    {
        $user = $this->get(array('user_hash' => $user_hash));
        if ($user == FALSE)
            return FALSE;

        $this->db->where('user_hash', $user_hash);
        $delete_user = $this->db->delete('users');

        return $delete_user == TRUE;

    }

    /**
     * Get
     *
     * Get a user, based on $user['user_hash'], $user['id'], $user['user_name']
     *
     * @access    public
     * @param    array $user
     * @return    array/FALSE
     */
    public function get(array $user)
    {
        $key = array_keys($user);
        $key = $key[0];
        if (in_array($key, array('user_hash', 'id', 'user_name'))) {
            $query = $this->db->select('id, banned, user_hash, user_name, local_currency, user_role, salt, force_pgp_messages, public_key, pgp_two_factor, totp_two_factor, entry_paid, email_address, email_activated, activation_hash, activation_id')
                ->get_where('users', array($key => $user[$key]));
        } else {
            return FALSE; //No suitable field found.
        }

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $row['public_key'] = base64_decode($row['public_key']);
            return $row;
        }
        return FALSE;
    }

    /**
     * Message Data
     *
     * Load information regarding the users RSA encryption keys.
     *
     * @access    public
     * @param    array $user
     * @return    array/FALSE
     */
    public function message_data(array $user)
    {

        if (isset($user['user_hash'])) {
            $query = $this->db->select('public_key, private_key, private_key_salt')
                ->get_where('users', array('user_hash' => $user['user_hash']));
        } elseif (isset($user['id'])) {
            $query = $this->db->select('public_key, private_key, private_key_salt')
                ->get_where('users', array('id' => $user['id']));
        } elseif (isset($user['user_name'])) {
            $query = $this->db->select('public_key, private_key, private_key_salt')
                ->get_where('users', array('user_name' => $user['user_name']));
        } else {
            return FALSE; //No suitable field found.
        }

        if ($query->num_rows() > 0) {
            $row = $query->row_array();

            $results = array('private_key_salt' => $row['private_key_salt'],
                'public_key' => base64_decode($row['public_key']),
                'private_key' => base64_decode($row['private_key']));
            return $results;
        }

        return FALSE;
    }

    public function wallet_salt($user_id)
    {
        $row = $this->db->get_where('users', array('id' => $user_id))->row_array();
        return $row['wallet_salt'];
    }

    /**
     * Check Password.
     *
     * Returns userdata when a users username, password and salt are entered correctly.
     *
     * @access    public
     * @param    string $user_name
     * @param    string $salt
     * @param    string $password
     * @return    array/FALSE
     */
    public function check_password($user_name, $password)
    {
        $this->db->select('id')
            ->where('user_name', $user_name)
            ->where('password', $password);

        $query = $this->db->get('users');

        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Add Registration Token
     *
     * Add an array describing a registration token.
     *
     * @access    public
     * @param    string $token
     * @return    boolean
     */
    public function add_registration_token($token)
    {
        return ($this->db->insert('registration_tokens', $token) == TRUE) ? TRUE : FALSE;
    }

    /**
     * List Registration Tokens
     *
     * This function loads a list of the current registration tokens
     * on record.
     *
     * @return        array/FALSE
     */
    public function list_registration_tokens()
    {
        $query = $this->db->get('registration_tokens');
        if ($query->num_rows() > 0) {
            $array = $query->result_array();
            foreach ($array as &$entry) {
                $entry['role'] = $this->general->role_from_id($entry['user_type']);
            }
            return $array;
        }
        return FALSE;
    }

    /**
     * Check Registration Token
     *
     * This function checks whether a registration token is valid or now.
     * Returns info about the token on success, FALSE on failure.
     *
     * @param    string $token
     * @return    array/FALSE
     */
    public function check_registration_token($token)
    {

        $this->db->select('id, user_type, token_content, entry_payment');
        $query = $this->db->get_where('registration_tokens', array('token_content' => $token));

        if ($query->num_rows() > 0) {
            $info = $query->row_array();
            $info['user_type'] = array('int' => $info['user_type'],
                'txt' => $this->general->role_from_id($info['user_type']));

            return $info;
        } else {
            return FALSE;
        }
    }

    /**
     * Increase Order Counter
     *
     * This function accepts a $user_id, and increases that users completed
     * order count by one.
     *
     * @param    int $user_id
     * @return    boolean
     */
    public function increase_order_count($user_id)
    {
        $this->db->select('completed_order_count')
            ->where('id', $user_id);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();

            $update = array('completed_order_count' => ($row['completed_order_count'] + 1));
            $this->db->where('id', $user_id);
            return ($this->db->update('users', $update) == TRUE) ? TRUE : FALSE;
        } else {
            return FALSE;
        }

    }

    /**
     * Set Login
     *
     * Set the users login time (user specified by $id)
     * @param    int $id
     * @return    boolean
     */
    public function set_login($id)
    {
        $change = array('login_time' => time());

        $this->db->where('id', $id);
        $query = $this->db->update('users', $change);
        return ($query) ? TRUE : FALSE;
    }

    /**
     * Set Entry Fee
     *
     * This function is used to record a figure that the user must pay
     * in order to register on the website. $info['user_hash'], $info['amount']
     * and $info['bitcoin_address'] must be supplied.
     *
     * @param    array $info
     * @return    boolean
     */
    public function set_entry_payment($info)
    {
        $info['time'] = time();
        return ($this->db->insert('entry_payment', $info)) ? TRUE : FALSE;
    }

    /**
     * Set Entry Paid
     *
     * This function is run when entry is free or when the user has
     * paid for their site. If this entry is not set in the table, when
     * they try to log in, users will be directed to the intermediary
     * payment page.
     *
     * @param    string $user_hash
     * @return    boolean
     */
    public function set_entry_paid($user_hash)
    {
        $array = array('entry_paid' => '1');
        $this->db->where('user_hash', $user_hash);
        return ($this->db->update('users', array('entry_paid' => '1'))) ? TRUE : FALSE;
    }

    /**
     * Set Payment Address
     *
     * If the bitcoin daemon is offline, when they register, we'll need
     * to add a proper address.
     *
     * @param    string $user_hash
     * @param    string $address
     * @return    boolean
     */
    public function set_payment_address($user_hash, $address)
    {
        if ($address == NULL)
            return FALSE;
        $this->db->where('user_hash', $user_hash);
        return ($this->db->update('entry_payment', array('bitcoin_address' => $address))) ? TRUE : FALSE;
    }

    /**
     * Get Payment Address Owner
     *
     * Function to return the user has associated with the particular
     * $address. Called when a watched address is found with a fee purpose
     *
     * @param    string $address
     * @return    string/FALSE
     */
    public function get_payment_address_owner($address)
    {
        $this->db->select('user_hash');
        $this->db->where('bitcoin_address', $address);
        $query = $this->db->get('entry_payment');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return ($row['user_hash']);
        }
        return FALSE;
    }

    /**
     * Get Entry Payment
     *
     * This function will load the details of the required entry
     * payment for this user.
     *
     * @param    string $user_hash
     * @return    array/FALSE
     */
    public function get_entry_payment($user_hash)
    {
        $this->db->where('user_hash', $user_hash);
        $query = $this->db->get('entry_payment');
        return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
    }

    /**
     * Delete Entry Payment
     *
     * This function deletes the record of the users entry payment once
     * they have registered their account.
     *
     * @param    string $user_hash
     * @return    boolean
     */
    public function delete_entry_payment($user_hash)
    {
        $this->db->where('user_hash', $user_hash);
        return ($this->db->delete('entry_payment') == TRUE) ? TRUE : FALSE;
    }


    /**
     * Count User List
     *
     * This function returns the total count of users. Additional parameters
     * can be supplied to narrow down the request.
     *
     * @param    array(opt) $params
     * @return    int
     */
    public function count_user_list($params)
    {
        $this->db->select('id');

        if (isset($params['order_by'])) {
            $this->db->order_by("{$params['order_by']}", "{$params['list']}");
            unset($params['order_by']);
            unset($params['list']);
        }

        foreach ($params as $column => $value) {
            $this->db->where("{$column}", "{$value}");
        }

        $this->db->from('users');
        return $this->db->count_all_results();
    }

    /**
     * List Users
     *
     * Display a list of users. Can supply a list of parameters to narrow
     * down the dataset, and also does pagination.
     *
     * @param    array(opt) $params
     * @param    int $users_per_page
     * @param    int $start
     * @return    array/FALSE
     */
    public function user_list($params = array(), $users_per_page, $start)
    {
        if (isset($params['order_by'])) {
            $this->db->order_by("{$params['order_by']}", "{$params['list']}");
            unset($params['order_by']);
            unset($params['list']);
        }

        // Apply the conditions in $params
        foreach ($params as $column => $value) {
            $this->db->where("{$column}", "{$value}");
        }

        // Pagination
        $this->db->limit($users_per_page, $start);

        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $results = array();
            foreach ($query->result_array() as $result) {
                $tmp = $result;
                $tmp['register_time_f'] = $this->general->format_time($tmp['register_time']);
                $tmp['login_time_f'] = $this->general->format_time($tmp['login_time']);
                array_push($results, $tmp);
            }
            return $results;
        }
        return FALSE;
    }

    /**
     * Search User
     *
     * Search for a user specified by $user_name. Returns an array with information
     * if the search is successful, otherwise returns FALSE. On failure,
     * FALSE will make the admin user list appear again, and declare the
     * user was not found.
     *
     * @param    string $user_name
     * @return    array/FALSE
     */
    public function search_user($user_name)
    {
        $this->db->like('user_name', $user_name);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $users = array();
            foreach ($query->result_array() as $result) {
                $user = $result;
                $user['register_time_f'] = $this->general->format_time($user['register_time']);
                $user['login_time_f'] = $this->general->format_time($user['login_time']);
                array_push($users, $user);
            }
            return $users;
        }
        return FALSE;
    }

    /**
     * Set Activated Email
     *
     * This function takes a $user_id and marks that account as having its email activated.
     *
     * @param $user_id
     */
    protected function _set_activated_email($user_id)
    {
        $this->db->where('id', $user_id)->update('users', array('email_activated' => '1'));
    }

    /**
     * Attempt Email Activation
     *
     * This function is used to try activate a users account for the first time.  If successful,
     * it will set the email as activated, and the function returns TRUE. If the details were invalid,
     * FALSE is returned. If the email was already updated, then return 'activated'
     *
     * @param $identifier
     * @param $subject
     * @param $activation_hash
     * @return bool|string
     */
    public function attempt_email_activation($identifier, $subject, $activation_hash)
    {
        $q = $this->db->select('id, email_activated')->get_where('users', array($identifier => $subject, 'activation_hash' => $activation_hash));
        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            if ($row['email_activated'] == '1') {
                return 'activated';
            } else {
                $this->_set_activated_email($row['id']);
                return TRUE;
            }
        }
        return FALSE;
    }
}

;

/* End of File: Users_Model.php */
