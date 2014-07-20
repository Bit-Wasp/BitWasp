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

function smarty_function_rating($params,&$smarty)
{
	$rating = (isset($params['rating']) AND is_numeric($params['rating'])) ? round($params['rating'], 0, PHP_ROUND_HALF_UP) : 0;
	
	$string = '';
	for($i = 0; $i < $rating; $i++) 
		$string .= '<span class="glyphicon glyphicon-star"></span>';
	for($j = $i; $j < 5; $j++)
		$string .= '<span class="glyphicon glyphicon-star-empty"></span>';
		
	return $string;


}
