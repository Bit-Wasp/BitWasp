<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$data['page'] = 'welcome_message';
		$data['title'] = 'Welcome';
		$this->load->library('layout',$data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
