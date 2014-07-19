<?php

class Onchain_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('totp');
    }

    public function add_request($info)
    {
        $info['time'] = time();
        $insert = $this->db->insert('onchain_requests', $info);
        $info['id'] = $this->db->insert_id();
        $info['totp_token'] = $this->totp->getCode($info['totp_secret']);
        return ($insert == TRUE) ? $info : FALSE;
    }

    public function create_request($user_id, $request_type, $sign_order_id = null)
    {
        if (!in_array($request_type, array('mpk', 'sign')))
            return FALSE;
        if ($request_type == 'sign' AND $sign_order_id == NULL)
            return FALSE;

        $info = array(
            'user_id' => $user_id,
            'request_type' => $request_type,
            'user_token' => $this->general->unique_hash('onchain_requests', 'user_token', '32'),
            'totp_secret' => $this->totp->createSecret()
        );

        if ($request_type == 'sign')
            $info['sign_order_id'] = $sign_order_id;
        return $this->add_request($info);
    }

    public function get_request($user_id, $request_type, $sign_order_id = null)
    {
        $where = array('user_id' => $user_id, 'request_type' => $request_type);
        if ($sign_order_id !== null)
            $where['sign_order_id'] = $sign_order_id;

        $query = $this->db->get_where('onchain_requests', $where);
        if ($query->num_rows() == 0)
            return FALSE;

        $row = $query->row_array();
        $row['totp_token'] = $this->totp->getCode($row['totp_secret']);
        return $row;
    }

    public function require_request($user_id, $request_type, $sign_order_id = null)
    {
        // If request type is allowed, get it.. If it fails, create it and pass it back.
        if (in_array($request_type, array('mpk', 'sign'))) {
            $get = $this->get_request($user_id, $request_type, $sign_order_id);

            return ($get == FALSE)
                ? $this->create_request($user_id, $request_type, $sign_order_id)
                : $get;
        } else {
            // Not an allowed request, return FALSE;
            return FALSE;
        }
    }

    public function app_auth($request, $token, $totp_token)
    {
        $q = $this->db->select('*')
            ->where('user_token', $token)
            ->where('request_type', $request)
            ->from('onchain_requests')
            ->get();

        if ($q->num_rows() > 0) {
            $q = $q->row_array();

            if ($this->totp->verifyCode($q['totp_secret'], $totp_token, 5) == TRUE)
                return $q;
        }
        return FALSE;
    }

    public function clear_auth($request_id)
    {
        return $this->db->where('id', $request_id)->delete('onchain_requests') == TRUE;
    }

}

;