<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Welcome Controller
 *
 * This class deals with the homepage.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Welcome
 * @author		BitWasp
 * 
 */
class Welcome extends CI_Controller {

	public function construct() {
		parent::__construct();
	}
	/**
	 * Index
	 * 
	 * Controller to handle the homepage.
	 */
	public function index()
	{
		$data['page'] = 'welcome_message';
		$data['title'] = 'Welcome';
		$this->load->library('layout',$data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
