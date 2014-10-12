<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Bip32 extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->load->model('bip32_model');

        $this->coin = $this->bw_config->currencies[0];
    }

    public function index()
    {
        $this->load->model('users_model');
        $this->load->library('onchainlib');
        $data['title'] = 'Key Settings';

        $data['wallet_salt'] = $this->users_model->wallet_salt($this->current_user->user_id);
        $data['key'] = $this->bip32_model->get($this->current_user->user_id);

        if ($data['key'] == FALSE) {
            $data['header_meta'] = $this->load->view('bip32/create_key_form', array(), TRUE);
            $data['display_onchain_qr'] = TRUE;
            $data['onchain_mpk'] = $this->onchainlib->mpk_request();
            // Create Onchain bip32 request for master public key if not already exists

            // Display QR encode for Onchain.io
            $data['page'] = 'bip32/no_key';

            // Manually submitted BIP32 key.
            if ($this->input->post('manual_submit') == 'Submit') {
                if ($this->form_validation->run('submit_bip32_manual') == TRUE) {
                    $insert_bip32_array = array(
                        'user_id' => $this->current_user->user_id,
                        'key' => $this->input->post('manual_public_key'),
                        'provider' => 'Manual'
                    );
                }
            }

            // Onchain submitted out of band using totp tokens

            // JS submitted key
            if ($this->input->post('js_submit') == 'Submit') {
                if ($this->form_validation->run('submit_bip32_js') == TRUE) {
                    $insert_bip32_array = array(
                        'user_id' => $this->current_user->user_id,
                        'key' => $this->input->post('js_extended_public_key'),
                        'provider' => 'JS'
                    );
                }
            }

            if (isset($insert_bip32_array)) {
                if ($this->bip32_model->add($insert_bip32_array) == TRUE) {
                    $this->current_user->set_return_message('Your key has been set up!', 'success');
                    redirect('bip32');
                }
            }
        } else {
            $data['page'] = 'bip32/' . strtolower($data['key']['provider']) . '_page';
            $data['usage'] = $this->bip32_model->get_user_key_usage($this->current_user->user_id);
            $this->_partial('key_usage_html', 'bip32/created_addresses');
            // Used keys
            switch ($data['key']['provider']) {
                case 'Manual':
                    $data['page'] = 'bip32/manual_page';
                    break;
                case 'Onchain':
                    $data['page'] = 'bip32/onchain_page';
                    break;
                case 'JS':
                    $data['header_meta'] = $this->load->view('bip32/display_current_key', array(), TRUE);
                    $data['page'] = 'bip32/js_page';
                    break;
            }
        }
        $this->_render($data['page'], $data);
    }

}