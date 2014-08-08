<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Onchain Library
 *
 *
 * @package        BitWasp
 * @subpackage    Libraries
 * @category    Onchain
 * @author        BitWasp
 */
class OnchainLib
{
    protected $CI;
    protected $service_name;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('onchain_model');
        $this->CI->load->library('ciqrcode');

    }

    public function sign_request($sign_order_id, $tx_hash)
    {
        $request = $this->CI->onchain_model->require_request($this->CI->current_user->user_id, 'sign', $sign_order_id);
        $string = 'sign|' . $this->service_name . '|' . base_url('onchain/sign') . '|usertoken|' . $request['user_token'] . '|totptoken|' . $request['totp_token'] . '|crc|' . $tx_hash;
        $request['qr'] = $this->CI->ciqrcode->generate_base64(array('data' => $string));
        return $request;
    }

    public function handle_sign_get_request($get_request)
    {
        $this->CI->load->model('order_model');
        $order = $this->CI->order_model->get($get_request['sign_order_id']);
        if ($order == FALSE)
            return 'A general error occurred';

        $tx = ($order['partially_signed_transaction'] !== '') ? $order['partially_signed_transaction'] : $order['unsigned_transaction'];
        $tx_crc = substr(hash('sha256', $tx), 0, 8);
        if ($tx_crc !== $get_request['crc'])
            return 'Transaction has changed, please refresh';

        return strtolower(trim($tx));
    }

    public function handle_sign_post_request($post_request)
    {

        $this->CI->load->model('order_model');
        $this->CI->load->model('bip32_model');
        $this->CI->load->library('bw_bitcoin');

        if (!isset($post_request['auth_id'])) {
            // Need this in order to delete it later, as the action is a write action.
            return 'Unable to proceed, no auth ID';
        }

        $decode_tx = \BitWasp\BitcoinLib\RawTransaction::decode($post_request['tx']);
        if ($decode_tx == FALSE)
            return 'Invalid transaction';

        $order = $this->CI->order_model->get($post_request['sign_order_id']);

        $signing_user = ($post_request['user_id'] == $order['buyer']['id']) ? $order['buyer'] : $order['vendor'];
        $signing_pubkey_id = $order[(strtolower($signing_user['user_role']) . '_public_key')];
        $user_key = $this->CI->bip32_model->get_child_key($signing_pubkey_id);

        $handle = $this->CI->bw_bitcoin->handle_order_tx_submission($order, $post_request['tx'], $user_key);

        if (is_string($handle))
            echo $handle;

        return $handle;
        //$this->CI->onchain_model->clear_auth($post_request['auth_id']);

    }

    public function mpk_request()
    {
        $request = $this->CI->onchain_model->require_request($this->CI->current_user->user_id, 'mpk');
        $string = 'mpk|' . $this->service_name . '|' . base_url('onchain/mpk') . '|usertoken|' . $request['user_token'] . '|totptoken|' . $request['totp_token'];
        $request['qr'] = $this->CI->ciqrcode->generate_base64(array('data' => $string));
        return $request;
    }

    public function handle_mpk_request($bip32_array)
    {
        $this->CI->load->model('bip32_model');
        if (!isset($bip32_array['auth_id'])) {
            // Need this in order to delete it later, as the action is a write action.
            return 'Unable to proceed, no auth ID';
        }

        $check_key_valid = \BitWasp\BitcoinLib\BIP32::import($bip32_array['key']) !== FALSE;

        $insert = array(
            'user_id' => $bip32_array['user_id'],
            'key' => $bip32_array['key'],
            'provider' => 'Onchain'
        );

        return ($check_key_valid)
            ? (($this->CI->bip32_model->add($insert) == TRUE AND $this->CI->onchain_model->clear_auth($bip32_array['auth_id'] == TRUE)) ? 'Key set up successfully!' : 'Error setting key')
            : 'Invalid BIP32 key';

    }
}

;
