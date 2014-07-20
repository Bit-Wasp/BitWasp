<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Onchain Controller
 *
 * This class handles app requests for transactions, master public key submissions..
 *
 * @package     BitWasp
 * @subpackage    Controllers
 * @category    Onchain
 * @author      BitWasp
 *
 */
class Onchain extends CI_Controller
{

    var $request;
    var $auth;
    var $get_request;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('onchain_model');
        $this->load->library('onchainlib');
        $this->load->helper('url');

        // Ensure request is valid
        if (uri_string() == 'onchain/mpk') {
            $this->request = 'mpk';
        }

        if (uri_string() == 'onchain/sign') {
            $this->request = 'sign';

        }
        $this->get_request = (strlen($this->input->get('usertoken')) > 0 AND strlen($this->input->get('totptoken')) > 0) ? TRUE : FALSE;
        if (!isset($this->request))
            redirect('');

        // Check authorization for request.
        $this->_auth();

    }

    /**
     * _auth
     *
     * This function providers authentication information to the controller
     * if the request is valid. Otherwise it terminates execution.
     */
    protected function _auth()
    {
        $user_token = '';
        $totp_token = '';

        if ($this->request == 'mpk') {
            if ($this->get_request) {
                $user_token = $this->input->get('usertoken');
                $totp_token = $this->input->get('totptoken');
            } else {
                $user_token = $this->input->post('usertoken');
                $totp_token = $this->input->post('totptoken');
            }
        }

        if ($this->request == 'sign') {
            if ($this->get_request) {
                $user_token = $this->input->get('usertoken');
                $totp_token = $this->input->get('totptoken');
            } else {
                $user_token = $this->input->post('usertoken');
                $totp_token = $this->input->post('totptoken');
            }
        }

        $this->auth = $this->onchain_model->app_auth($this->request, $user_token, $totp_token);
        if ($this->auth == FALSE) {
            echo 'Not Authorized. QR codes expire, please refresh the page!';
            exit(0);
        }
    }


    /**
     * MPK
     * This URI exposes the onchainlib\handle_mpk_request() function,
     * which returns the response to the given MPK; either successful,
     * invalid MPK, or general error.
     *
     * @return mixed
     */
    public function mpk()
    {
        if (!$this->auth) return FALSE;

        $this->load->library('onchainlib');
        // Now have an authorized request

        $bip32_array = array(
            'auth_id' => $this->auth['id'],
            'user_id' => $this->auth['user_id'],
            'key' => $this->input->post('mpk')
        );

        echo $this->onchainlib->handle_mpk_request($bip32_array);
    }

    public function sign()
    {
        if (!$this->auth) return FALSE;

        if ($this->get_request) {
            // This is a request for the transaction to sign
            $get_request = array(
                'user_id' => $this->auth['user_id'],
                'sign_order_id' => $this->auth['sign_order_id'],
                'user_token' => $this->auth['user_token'],
                'crc' => $this->input->get('crc')
            );
            echo $this->onchainlib->handle_sign_get_request($get_request);

        } else {
            $post_request = array(
                'auth_id' => $this->auth['id'],
                'user_id' => $this->auth['user_id'],
                'sign_order_id' => $this->auth['sign_order_id'],
                'tx' => $this->input->post('tx'),
                'crc' => $this->input->post('crc')
            );

            echo $this->onchainlib->handle_sign_post_request($post_request);
        }
        // Now have an authorized request
    }
}

;
