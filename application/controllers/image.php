<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Image extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('images_model');
	}

	// For every image, we have a large image. 
	public function index($image_hash) {
		$image = $this->images_model->get($image_hash."_l");
		if($image == FALSE)
			redirect('');
		
		echo "<center><img src=\"data:image/jpeg;base64,{$image['encoded']}\"></center>";
	}
	
};

/* End of file Image.php */
