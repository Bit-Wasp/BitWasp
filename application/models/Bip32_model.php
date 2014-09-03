<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * BIP32 Model
 *
 * Model to contain database queries for dealing with users BIP32 keys
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Fees
 * @author        BitWasp
 *
 */
class Bip32_model extends CI_Model
{
    /**
     * Construct
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {
        $data['time'] = time();
        return $this->db->insert('bip32_keys', $data) == TRUE;
    }

    public function get($user_id)
    {
        $query = $this->db->get_where('bip32_keys', array('user_id' => $user_id));
        return ($query->num_rows() == 1)
            ? $query->row_array()
            : FALSE;
    }

    public function recurse_until_unique_bip32_key($bip32_key_row)
    {
        $this->load->model('used_pubkeys_model');
        
        // Loop until a unique key is found.
        $valid = FALSE;
        while ($valid == FALSE) {
            $new_key = \BitWasp\BitcoinLib\BIP32::build_key($bip32_key_row['key'], $bip32_key_row['key_index']);
            $public_key = \BitWasp\BitcoinLib\BIP32::extract_public_key($new_key);

            // Check that when previously used keys are removed, result is still one (never used before)
            if (count($this->used_pubkeys_model->remove_used_keys(array($public_key))) == 1) {
                $valid = TRUE;
            } else {
                $bip32_key_row['key_index']++;
            }
        }

        return array(
            'parent_extended_public_key' => $bip32_key_row['key'],
            'provider' => (isset($bip32_key_row['provider']) ? $bip32_key_row['provider'] : 'Manual'),
            'extended_public_key' => $new_key[0],
            'public_key' => $public_key,
            'key_index' => $bip32_key_row['key_index']
        );
    }

    public function get_next_bip32_child($user_id)
    {
        $this->load->model('used_pubkeys_model');
        $key = $this->get($user_id);
        // Key will be M/0'
        return is_array($key) ? $this->recurse_until_unique_bip32_key($key)
            : FALSE;
    }

    public function get_next_admin_child()
    {
        $admin_key = array(
            'key' => $this->bw_config->bip32_mpk,
            'key_index' => $this->bw_config->bip32_iteration
        );

        $child = $this->recurse_until_unique_bip32_key($admin_key);
        if ($child == FALSE)
            return FALSE;

        $this->config_model->update(array('bip32_iteration' => ($this->bw_config->bip32_iteration + 1)));
        return $child;
    }

    public function update_next_index($info)
    {
        if ($info['user_role'] !== 'Admin')
            $this->db->where('user_id', $info['user_id'])->update('bip32_keys', array('key_index' => ($info['key_index'] + 1)));
    }

    public function add_child_key($info)
    {
        $info['time'] = time();
        if ($this->db->insert('bip32_user_keys', $info) == TRUE) {
            $info['id'] = $this->db->insert_id();

            // Update that users next key index
            $this->update_next_index($info);
            return $info;
        } else {
            return FALSE;
        }
    }

    public function get_child_key($id)
    {
        $query = $this->db->get_where('bip32_user_keys', array('id' => $id));
        return ($query->num_rows() > 0)
            ? $query->row_array()
            : FALSE;
    }

    public function get_user_key_usage($user_id)
    {
        $query = $this->db->get_where('bip32_user_keys', array('user_id' => $user_id))->result_array();

        if (count($query) > 0) {
            foreach ($query as &$row) {
                $row['address'] = \BitWasp\BitcoinLib\BitcoinLib::public_key_to_address($row['public_key'], $this->bw_config->currencies[0]['crypto_magic_byte']);
            }
        }
        return $query;

    }

}

;
