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

function smarty_function_returnMessage($params,&$smarty)
{
	if(strlen($params['returnMessage']) > 0) {
		return '<div class="alert alert-'.$params['class'].'">'.$params['returnMessage'].'</div>';
	} else if(isset($params['defaultMessage']) && strlen($params['defaultMessage']) > 0) {

        return '<div class="alert alert-warning">'.$params['defaultMessage'].'</div>';
    }

    return '';
}
