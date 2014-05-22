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


    public function a() {
        print_r(BitcoinLib::get_new_key_set('00'));
    }
};

