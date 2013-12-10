<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Layout Library
 *
 * Library which takes care of loading the templates for displaying
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
class Layout {

	protected $bw_config;

	/**
	 * Construct
	 * 
	 * Load the CodeIgniter framework. Loads the sites title, description,
	 * categories (if logged in, or logged out but the admin allows),
	 * the users balance & role (if logged in).
	 * 
	 * This stores predefined information about the job, such as the name,
	 * description, and the frequency at which it should be run.
	 */	
	public function __construct($data) {

		$CI = &get_instance();
		$CI->load->model('currencies_model');
		$CI->load->model('categories_model');
		$CI->load->library('form_validation');
		// Header data; used to include clientside PGP.
		if(!isset($data['header_meta'])) 
			$data['header_meta'] = ''; 

		$bar['role'] 				= 'guest';
		$bar['allow_guests'] 		= $CI->bw_config->allow_guests;
		$category_data['cats'] 		= '';
		$category_data['block'] 	= FALSE;
		$category_data['locations'] = $CI->general_model->locations_list();
		$data['site_title'] 		= $CI->bw_config->site_title;
		$data['site_description']	= $CI->bw_config->site_description;
		$footer['price_index']		= $CI->bw_config->price_index;
		$footer['exchange_rates']	= $CI->currencies_model->get_exchange_rates();
		
		//Check if there are categories to display
		if(!isset($data['currentCat'])) $data['currentCat'] = array(); 

		if($CI->current_user->logged_in()) { 
			
			$CI->load->model('bitcoin_model');		
			// If the user is logged in, load their role, and the categories. 
			$bar['role'] = strtolower($CI->current_user->user_role);			
			$bar['balance'] = $CI->bitcoin_model->current_balance();
			$bar['current_user'] = $CI->current_user->status();
			$balance = $bar['balance']*$CI->current_user->currency['rate'];
			$balance = ($CI->current_user->currency['id'] == '0') ? round($balance, '8', PHP_ROUND_HALF_UP) : round($balance, '2', PHP_ROUND_HALF_UP);
			$bar['local_balance'] = $balance;
			$bar['count_unread_messages'] = $CI->general_model->count_unread_messages();
			if($bar['role'] == 'vendor')
				$bar['count_new_orders'] = $CI->general_model->count_new_orders();
				
			$categories = $CI->categories_model->menu();		
			$category_data['cats'] = (empty($categories)) ? 'No Categories' : $this->menu($categories , 0, $data['currentCat']); 
		} else {
			// If a numeric user_id is set and two_factor or force_pgp flags are set, choose the required bar.
			if(isset($CI->current_user->user_id) && is_numeric($CI->current_user->user_id) &&
			   $CI->current_user->two_factor == TRUE || $CI->current_user->force_pgp == TRUE || $CI->current_user->entry_payment == TRUE) 
					$bar['role'] = 'half';
			
			// If guests are allowed to browse, load the categories.
			if($bar['allow_guests'] == TRUE) {
				$categories = $CI->categories_model->menu();		
				$category_data['cats'] = (empty($categories)) ? 'No Categories' : $this->menu($categories , 0, $data['currentCat']); 
				
			} else {
				// Otherwise, block categories on the pages users have access to.
				if($CI->general->matches_any($CI->current_user->URI[0], array('login','register')))
					$category_data['block'] = TRUE;
			}
		}	
		
		$header = array('title' => $data['title'],
						'site_title' => $data['site_title'],
						'site_description' => $data['site_description'],
						'maintenance_mode' => $CI->bw_config->maintenance_mode,
						'header_meta' => $data['header_meta']);
		
		// Load the HTML.
		$CI->load->view('templates/header',$header);
		$CI->load->view('templates/bar/'.$bar['role'], $bar);
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
	 */	
	public function menu($categories, $level, $params) {
		$content = ''; 
		$level++; 
		
		if($level !== 1) 
			$content .= "<ul>\n";

		// Pregenerate the URL. Checks for trailing slashes, fixes up
		// issues when mod_rewrite is disabled.
		// Loop through each parent category
		foreach($categories as $category) {
			//Check if were are currently viewing this category, if so, set it as active
			$content .= "<li "; 
			if(isset($params['id'])) {
				if($params['id']==$category['id'])  
					$content .= "class='active'"; 
			} $content .= ">\n";

			// Display link if category contains items. 
			$content .= ($category['count'] == 0) ? '<span>'.$category['name'].'</span>' : anchor('category/'.$category['hash'], $category['name'].' ('.$category['count'].")\n");
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
