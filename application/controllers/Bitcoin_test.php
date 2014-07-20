<pre><?php

use BitWasp\BitcoinLib\BitcoinLib;

/**
 * Bitcoin Test Controller
 *
 * This controller is used to test out some bitcoin functions before
 * adding them to the live code.
 */
class Bitcoin_Test extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('bw_bitcoin');

    }

    public function t()
    {
        $this->load->model('order_model');
        $this->load->model('bip32_model');
        $this->load->library('bw_bitcoin');
        $tx = '01000000017cdf81676ea054efae3d323f4c978578ffbac3cb1557cc75eb2443d9dd92138d00000000b500483045022100f25346b415cdd784c0f5f9c112d2a84eb0c9c097a5f9fd4fb308d8b4ce6be308022065938de62a0d994a0175cbdbfd28e1e3aac8d52726d10e4b99896249477d1258014c69522102da5f2dbc9a81741b1aa1c4735e77d9979164de722dd09b8f422015ba2c60bc9d2103e984760efa04c914fcaaec7245131ee4a29362d2b1c0000f7f7aa13430ebad8521038600cdf006847545ade2e569a8c3682526cddeeda992707bf26143d93928095553aeffffffff02d8270000000000001976a914f761a8e7a8712f51c068396d889ea17849b9d85f88ac584d0000000000001976a91435de7aba2bdad66f2b4c6c6b3626305cca7e39df88ac00000000';

        $order = $this->order_model->get('39');
        $user_key = $order['public_keys']['buyer'];
        $handle = $this->bw_bitcoin->handle_order_tx_submission($order, $tx, $user_key);
        print_r($handle);
        echo '<hr>';
        print_r($tx);
        echo '<hr>';
        print_r($user_key);
        echo '<hr>';
        print_r($order);
        echo '<hr>';

        error_log("-----------------------------------------------");
        error_log("-----------------------------------------------");
    }


    public function key()
    {
        $this->load->model('used_pubkeys_model');

        $used_keys = array(
            'a',
            'b',
            'c'
        );

        $this->used_pubkeys_model->log_public_key($used_keys);

        $new_public_keys = array(
            'j',
            'a',
            'u',
            's',
            'y',
            'c'
        );

        $new = $this->used_pubkeys_model->remove_used_keys($new_public_keys);
        print_r($new);
        echo '<br>';
        echo count($new) . " - " . count($new_public_keys) . "<br />";
    }

    public function a()
    {
        print_r(BitcoinLib::get_new_key_set('00'));
    }
}

;

