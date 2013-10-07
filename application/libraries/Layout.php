<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Layout {

	protected $bw_config;
	
	public function __construct($data){

		$CI = &get_instance();
		$CI->load->model('currencies_model');
		$CI->load->model('categories_model');

		// Header data; used to include clientside PGP.
		if(!isset($data['header_meta'])) 
			$data['header_meta'] = ''; 

		$bar['role'] = 'guest';
		$bar['allow_guests'] = $CI->bw_config->allow_guests;
		$category_data['cats'] = '';
		$category_data['block'] = FALSE;
		
		$data['site_title'] = $CI->bw_config->site_title;
		$data['site_description'] = $CI->bw_config->site_description;
		$footer['price_index'] = $CI->bw_config->price_index;
		$footer['exchange_rates'] = $CI->currencies_model->get_exchange_rates();
		
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
			   $CI->current_user->two_factor == TRUE || $CI->current_user->force_pgp == TRUE ) 
					$bar['role'] = 'half';
			
			// If guests are allowed to browse, load the categories.
			if($bar['allow_guests'] == TRUE){
				$categories = $CI->categories_model->menu();		
				$category_data['cats'] = (empty($categories)) ? 'No Categories' : $this->menu($categories , 0, $data['currentCat']); 
				
			} else {
				// Otherwise, block categories on the pages users have access to.
				if($CI->general->matches_any($CI->current_user->URI[0], array('login','register')))
					$category_data['block'] = TRUE;
			}
		}	
		
		// Load the HTML.
		$CI->load->view('templates/header',$data);
		$CI->load->view('templates/bar/'.$bar['role'], $bar);
		$CI->load->view('templates/midsection');
        $CI->load->view('templates/sidebar', $category_data);
//        print_r($CI->current_user->status());
        $CI->load->view($data['page'], $data);
		$CI->load->view('templates/footer',$footer);
			
	}

	//Output the categories as an unordered list.
	public function menu($categories, $level, $params){
		if(!isset($content)) 
			$content = ''; 
			
		$level++; 
		
		if($level !== 1) 
			$content .= "<ul>\n";

		// Loop through each parent category
		foreach($categories as $category) {
			//Check if were are currently viewing this category, if so, set it as active
			$content .= "<li "; 
			if(isset($params['id'])) {
				if($params['id']==$category['id'])  
					$content .= "class='active'"; 
			} $content .= ">\n";

			// Display link if category contains items.
			$content .= ($category['count'] == 0) ? '<span>'.$category['name'].'</span>' : '<a href="'.site_url().'category/'.$category['hash'].'">'.$category['name'].' ('. $category['count'] .")</a>\n";

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
