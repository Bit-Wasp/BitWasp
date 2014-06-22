<?php
/**
 * Smarty plugin
 */

/**
 * Smarty {url} function plugin
 *
 * Type:     function
 * Name:     url
 * @author:  Trimo
 * @mail:     trimo.1992[at]gmail[dot]com
 */

function smarty_function_form($params,&$smarty)
{
    if (!function_exists('current_url')) {
        if (!function_exists('get_instance')) {
            $smarty->trigger_error("url: Cannot load CodeIgniter");
            return;
        }
        $CI = &get_instance();
        $CI->load->helper('form');
    }
    
    # {form method='open' action='' attr=''}
    if($params['method'] == 'open') {
		if(!isset($params['action']))
			return FALSE;
	
		if(!isset($params['attr']))
			return form_open($params['action']);
		
		return form_open($params['action'], $params['attr']);
	} else if($params['method'] == 'open-multipart') {
		if(!isset($params['action']))
			return FALSE;
	
		if(!isset($params['attr']))
			return form_open_multipart($params['action']);
		
		return form_open_multipart($params['action'], $params['attr']);
	} elseif($params['method'] == 'set_value') {
		if(!isset($params['field']))
			return FALSE;
		return set_value($params['field']);
	} elseif($params['method'] == 'form_error') {
        if(!isset($params['field']))
            return FALSE;
        return form_error($params['field']);
    } elseif($params['method'] == 'validation_errors') {
		return validation_errors();
	}
	
	
	return FALSE;
}
