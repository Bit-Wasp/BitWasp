<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Academic Free License version 3.0
 *
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['image/(:any)'] = 'image/index/$1';

$route['register'] = 'users/register';				// System. Require user is a guest.
$route['register/(:any)'] = 'users/register/$1';
$route['login'] = 'users/login';
$route['login/pgp_factor'] = 'users/pgp_factor';	// half session.
$route['login/totp_factor'] = 'users/totp_factor';	// half session
$route['register/pgp'] = 'users/register_pgp';		// half session.
$route['register/payment'] = 'users/payment';		// half session.
$route['logout'] = 'users/logout';				
$route['authorize'] = 'authorize';					// System. Require user is logged in.

$route['message/pin'] = 'messages/enter_pin';		// System. Require user is logged in.
$route['message/sent'] = 'messages/sent';
$route['message/deleted'] = 'messages/deleted';
$route['message/delete/(:any)'] = 'messages/delete/$1';
$route['message/send/(:any)'] = 'messages/send/$1';
$route['message/send'] = 'messages/send';
$route['message/(:any)'] = 'messages/read/$1';
$route['inbox'] = 'messages/inbox';

$route['user/(:any)'] = 'accounts/view/$1';			// Optionally require auth?
$route['account/edit'] = 'accounts/edit';		
$route['account/disable_2fa'] = 'accounts/disable_2fa';
$route['account/pgp_factor'] = 'accounts/enable_pgp_factor';
$route['account/two_factor'] = 'accounts/two_factor';
$route['account'] = 'accounts/me';
$route['pgp/add'] = 'accounts/add_pgp';				// Optionally require a password.
$route['pgp/delete'] = 'accounts/delete_pgp';
$route['pgp/replace'] = 'accounts/replace_pgp';

$route['listings'] = 'listings/manage';				// Must be a vendor
$route['listings/add'] = 'listings/add';
$route['listings/delete/(:any)'] = 'listings/delete/$1';
$route['listings/images/(:any)'] = 'listings/images/$1';
$route['listings/edit/(:any)'] = 'listings/edit/$1';
$route['listings/delete_image/(:any)'] = 'listings/delete_image/$1';

$route['item/(:any)'] = 'items/get/$1';
$route['items'] = 'items/index';
$route['items/(:any)'] = 'items/index/$1';
$route['location/(:any)/(:any)/(:num)'] = 'items/location/$1/$2/$3';
$route['location/(:any)/(:any)'] = 'items/location/$1/$2';
$route['location/(:any)'] = 'items/location/$1';
$route['category/(:any)/(:num)'] = 'items/category/$1/$2';
$route['category/(:any)'] = 'items/category/$1';

$route['admin'] = 'admin';
$route['admin/bitcoin'] = 'admin/bitcoin';
$route['admin/bitcoin/topup'] = 'admin/topup_addresses';
$route['admin/edit'] = 'admin/edit_general';
$route['admin/edit/users'] = 'admin/edit_users';
$route['admin/edit/items'] = 'admin/edit_items';
$route['admin/edit/bitcoin'] = 'admin/edit_bitcoin';
$route['admin/edit/autorun'] = 'admin/edit_autorun';
$route['admin/category/orphans/(:any)'] = 'admin/category_orphans/$1';
$route['admin/items/fees'] = 'admin/fees';
$route['admin/users/delete/(:any)'] = 'admin/user_delete/$1';
$route['admin/users/list/(:num)'] = 'admin/user_list/$1';
$route['admin/users/list'] = 'admin/user_list';
$route['admin/delete_item/(:any)'] = 'admin/delete_item/$1';
$route['admin/tokens'] = 'admin/user_tokens';
$route['admin/tokens/delete/(:any)'] = 'admin/delete_token/$1';
$route['admin/disputes'] = 'admin/dispute';
$route['admin/order/(:num)'] = 'orders/details/$1';
$route['order/dispute/(:num)'] = 'orders/dispute/$1';
$route['orders/dispute/(:num)'] = 'orders/dispute/$1';

// Vendor
$route['orders'] = 'orders/vendor_orders';
$route['orders/accept/(:num)'] = 'orders/vendor_accept/$1';
$route['orders/finalize_early/(:num)'] = 'orders/vendor_finalize_early/$1';
$route['orders/refund/(:num)'] = 'orders/vendor_refund/$1';

// Buyer
$route['purchases/details/(:num)'] = 'orders/details/$1';
$route['purchases'] = 'orders/buyer_orders';
$route['purchases/confirm/(:num)'] = 'orders/buyer_confirm/$1';
$route['purchases/dispute/(:num)'] = 'orders/dispute/$1';
$route['purchase/(:any)'] = 'orders/purchase_item/$1';
// Both


$route['404_override'] = '';
$route['default_controller'] = 'welcome';

$route['translate_uri_dashes'] = FALSE;

/* End of file routes.php */
/* Location: ./application/config/routes.php */
