<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth Model
 *
 * This class handles the database queries for two step authentication,
 * and authorizing requests to restricted pages,
 *
 * @package        BitWasp
 * @subpackage    Model
 * @category    Auth
 * @author        BitWasp
 *
 */
class Auth_model extends CI_Model
{

    /**
     * Check Auth
     *
     * Check the level of authorization required for the URI[0]
     *
     * @access    public
     * @param    string $URI
     * @return    string / bool
     */
    public function check_auth($URI)
    {
        $query = $this->db->select('auth_level')->get_where('page_authorization', array('URI' => $URI));

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['auth_level'];
        }

        return FALSE;
    }

    /**
     * Check Authorization Timeout
     *
     * Load the authorization timeout of this URI.
     *
     * @access    public
     * @param    string $URI
     * @return    int(seconds)/FALSE
     */
    public function check_auth_timeout($URI)
    {
        $query = $this->db->select('timeout')->get_where('page_authorization', array('URI' => $URI));

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['timeout'] * 60;
        }

        return FALSE;
    }

    /**
     * Check Two Factor Token
     *
     * Check the supplied solution to a two-factor auth.
     *
     * @access    public
     * @param    string $token
     * @return    boolean
     */
    public function check_two_factor_token($token)
    {
        $result = FALSE;
        $query = $this->db->select('id')
            ->where('user_id', $this->current_user->user_id)
            ->where('solution', $token)
            ->get('two_factor_tokens');

        if ($query->num_rows() > 0)
            $result = TRUE;


        return $result;
    }

    /**
     * Add Two Factor Token
     *
     * Store the solution of a two-factor challenge for the user.
     *
     * @access    public
     * @param    string $token
     * @return    bool
     */
    public function add_two_factor_token($token)
    {
        $this->db->where('user_id', $this->current_user->user_id);
        $this->db->delete('two_factor_tokens');

        $array = array('solution' => $token,
            'user_id' => $this->current_user->user_id);
        return $this->db->insert('two_factor_tokens', $array) == TRUE;
    }
}

;
