<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once dirname(__FILE__).'/../views/templates/Template.php';

/**
 * Layout Library
 *
 * Simply loads the Template class from the template folder.
 * 
 * Also contains a function to generate the menu's HTML.
 * 
 * @package		BitWasp
 * @subpackage	Library
 * @category	Layout
 * @author		BitWasp
 */
class Layout {

	/**
	 * Construct
	 * 
	 * Pass data from controller to layout template constructor.
	 * 
	 * @param		array	$data
	 */	
	public function __construct($data) {
		new Template($data);
	}
};

 /* End of file Layout.php */
