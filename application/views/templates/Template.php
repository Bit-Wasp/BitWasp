<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Template Library
 *
 * This library handles loading information and templates for displaying
 * the site. If the user is logged in, we load their role, bitcoin balance,
 * number of unread messages, new orders (if a vendor).
 * If not logged in, we check if users are allowed to view the site without
 * being logged in (set by the admin). 
 * 
 * Also contains a function to generate the menu's HTML.
 * 
 * @package		BitWasp
 * @subpackage	Library
 * @category	Layout
 * @author		BitWasp
 */
class Template {

	/**
	 * Construct
	 * 
	 * Renders all the view files, and load the necessary information.
	 * 
	 * @param		array	$data
	 */	
	public function __construct($data) {

		$CI = &get_instance();
		$CI->load->model('categories_model');
		$CI->load->library('form_validation');
		
		// Header data; used to include clientside PGP.
		if(!isset($data['header_meta'])) 
			$data['header_meta'] = ''; 

		$info = json_decode($CI->session->flashdata('returnMessage'));
		if (count($info) !== 0 && !isset($data['returnMessage']) && isset($info->message))
		{
			$data['returnMessage'] = $info->message;
		
			if (isset($info->success) && $info->success == TRUE)
				$data['success'] = TRUE;
		}

        $data['site_title'] 		= $CI->bw_config->site_title;
        $data['site_description']	= $CI->bw_config->site_description;
        $footer['price_index']		= $CI->bw_config->price_index;
        $footer['exchange_rates']	= $CI->bw_config->exchange_rates;
        $footer['currencies']	    = $CI->bw_config->currencies;

        // Load cryptocurrency
        $data['coin'] = $CI->bw_config->currencies[0];
        $bar['coin'] = $CI->bw_config->currencies[0];

        // Load data about current user
        $data['current_user'] = $CI->current_user->status();
        $bar['current_user'] = $CI->current_user->status();

		$bar['allow_guests'] 		= $CI->bw_config->allow_guests;

		$category_data['cats'] 		= '';
		$category_data['block'] 	= FALSE;

		//Check if there are categories to display
		if ( ! isset($data['currentCat'])) 
			$data['currentCat'] = array(); 

		if ($CI->current_user->logged_in())
		{

			$bar['count_unread_messages'] = $CI->general_model->count_unread_messages();
			if($CI->current_user->user_role == 'Vendor')
				$bar['count_new_orders'] = $CI->general_model->count_new_orders();
				
			$categories = $CI->categories_model->menu();		
			$category_data['cats'] = (empty($categories)) ? 'No Categories' : $this->menu($categories , 0, $data['currentCat']); 
			
			$category_data['locations_w_select'] = $CI->location_model->generate_select_list($CI->bw_config->location_list_source, 'location', 'span12', FALSE, array('worldwide' => TRUE));
			$category_data['locations_select'] = $CI->location_model->generate_select_list($CI->bw_config->location_list_source, 'location', 'span12');
			if(isset($data['ship_from_error']))
				$category_data['ship_from_error'] = $data['ship_from_error'];
			if(isset($data['ship_to_error']))
				$category_data['ship_to_error'] = $data['ship_to_error'];
			
		} else {

			// If guests are allowed to browse, load the categories.
			if($CI->bw_config->allow_guests == TRUE) {
				$categories = $CI->categories_model->menu();		
				$category_data['cats'] = (empty($categories)) ? 'No Categories' : $this->menu($categories , 0, $data['currentCat']); 
				
			} else {
				// Otherwise, block categories on the pages users have access to.
				if(in_array($CI->current_user->URI[0], array('login','register')))
					$category_data['block'] = TRUE;
			}
		}
		
		$header = array('title' => $data['title'],
						'site_title' => $data['site_title'],
						'site_description' => $data['site_description'],
						'maintenance_mode' => $CI->bw_config->maintenance_mode,
						'header_meta' => $data['header_meta']);

		// Load the HTML.
		$CI->load->view('templates/header', $header);
		$CI->load->view('templates/bar/'.strtolower($CI->current_user->user_role), $bar);
		$CI->load->view('templates/midsection');
        $CI->load->view('templates/sidebar', $category_data);
        $CI->load->view($data['page'], $data);
		$CI->load->view('templates/footer',$footer);
			
	}

	/**
	 * Menu
	 * 
	 * A recursive function to generate a menu from an array of categories.
	 * Uses each categories parent ID to determine where it should be placed.
	 * 
	 * @param		array	$categories
	 * @param		int		$level
	 * @param		array	$params
	 * @return		string
	 */	
	public function menu($categories, $level, $params)
	{
		$content = ''; 
		$level++; 
		
		if($level !== 1)
			$content .= "<ul>\n";
			
		// Pregenerate the URL. Checks for trailing slashes, fixes up
		// issues when mod_rewrite is disabled.
		// Loop through each parent category
		foreach($categories as $category) {
			//Check if were are currently viewing this category, if so, set it as active
			$content .= "<li"; 
			if(isset($params['id'])) {
				if($params['id']==$category['id'])  
					$content .= " class='active'"; 
			} $content .= ">";

			// Display link if category contains items. 
			$content .= ($category['count'] == 0) ? '<span>'.$category['name'].'</span>' : anchor('category/'.$category['hash'], $category['name'].' ('.$category['count'].")");
			
			// Check if we need to recurse into children.
			if(isset($category['children']))  
				$content .= $this->menu($category['children'], $level, $params); 
			
			$content .= "</li>\n";
		}
		
		if($level!==1)
			$content .= "</ul>\n"; 

		return $content;
	}

};

 /* End of file Layout.php */
