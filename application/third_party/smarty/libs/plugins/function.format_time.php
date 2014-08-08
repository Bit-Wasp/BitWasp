<?php
/**
 * Smarty plugin
 */

/**
 * Smarty {url} function plugin
 *
 * Type:     function
 * Name:     url
 * Author: 	 Thomas Kerin
 */

function smarty_function_format_time($params,&$smarty)
{    
	if (!function_exists('get_instance')) {
		$smarty->trigger_error("url: Cannot load CodeIgniter");
		return;
	}
	$CI = &get_instance();
        
	return $CI->general->format_time($params['ts']);
}
