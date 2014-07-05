<pre><?php

use BitWasp\BitcoinLib\BitcoinLib;

/**
 * Bitcoin Test Controller
 * 
 * This controller is used to test out some bitcoin functions before
 * adding them to the live code. 
 */
 
class Bitcoin_Test extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		$this->load->library('bw_bitcoin');
		
	}

	public function key() {
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
		print_r($new);echo '<br>';
		echo count($new)." - " .count($new_public_keys)."<br />";
	}

    public function a() {
        print_r(BitcoinLib::get_new_key_set('00'));
    }
};

