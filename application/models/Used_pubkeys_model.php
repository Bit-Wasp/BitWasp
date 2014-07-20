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
class Used_pubkeys_model extends CI_Model
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function log_public_key($public_key)
    {
        if (is_array($public_key)) {
            if (count($public_key) == 0)
                return TRUE;

            $insert = array();
            foreach ($public_key as $key) {
                $insert[] = array('public_key_sha256' => hash('sha256', $key));
            }
            return $this->db->insert_batch('used_public_keys', $insert) == TRUE;
        } elseif (is_string($public_key) == TRUE) {
            return $this->db->insert('used_public_keys', array('public_key_sha256' => hash('sha256', $public_key))) == TRUE;
        }
    }

    public function remove_used_keys($public_key_array)
    {
        // Build an array of hash of public key => public_key
        $hash_key_array = array();
        foreach ($public_key_array as $public_key) {
            $hash_key_array[(hash('sha256', $public_key))] = $public_key;
        }

        // Take the keys of this array, to search for used keys in the log.
        $keys = array_keys($hash_key_array);

        $used_results = $this->db->select("public_key_sha256")
            ->from('used_public_keys')
            ->where_in($keys)
            ->get()
            ->result_array();

        if (count($used_results) > 0) {
            // If used public keys are found on the list:
            // Take all hashes from the results, to filter corresponding keys
            foreach ($used_results as $arr) {
                $hashes[] = $arr['public_key_sha256'];
            }

            // Flip hashes array into keys; Remove entries in $hash_key_array (hash=>pubkey) with these keys;
            // Flip resulting array to (pubkey=>hash); Return the keys of this array - the clean public keys
            $clean_keys = array_keys(array_flip(array_diff_key($hash_key_array, array_flip($hashes))));
        } else {
            // Otherwise, all keys are unique, so pass them back.
            $clean_keys = $public_key_array;
        }
        return $clean_keys;

    }
}

;
