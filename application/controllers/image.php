<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Image Controller
 *
 * This class handles displaying large images for users to view.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Image
 * @author		BitWasp
 * 
 */

class Image extends CI_Controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @see		Models/Images_Model
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('images_model');
	}

	/**
	 * Load the large version of an image.
	 * URI: /image/$image_hash
	 * @param	string
	 * @access	public
	 * @see		Models/Bitcoin_Model
	 * @see		Libraries/Bw_Bitcoin
	 */
	public function index($image_hash) {
		// Append _l to specify we want the large record.
		$image = $this->images_model->get($image_hash."_l");
		// Redirect if the image does not exist.
		if($image == FALSE)
			redirect('');
		
		echo "<center><img src=\"data:image/jpeg;base64,{$image['encoded']}\"></center>";
	}
	
};

/* End of file Image.php */
