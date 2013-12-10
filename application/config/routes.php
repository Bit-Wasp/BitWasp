<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.

*/
//$route['home'] = "welcome";							// Default config, must be logged in.


$route['image/(:any)'] = 'image/index/$1';

$route['register'] = 'users/register';				// System. Require user is a guest.
$route['register/(:any)'] = 'users/register/$1';
$route['login'] = 'users/login';
$route['login/two_factor'] = 'users/two_factor';	// half session.
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
$route['location/(:any)'] = 'items/location/$1';
$route['location/(:any)/(:any)'] = 'items/location/$1/$2';
$route['category/(:any)'] = 'items/category/$1';

// Bitcoin testing functions. Remove before production!!
$route['bitcoin/i'] = 'bitcoin_test/index';
$route['bitcoin/balance'] = 'bitcoin_test/current_balance';
$route['bitcoin/get_block/(:any)'] = 'bitcoin_test/get_block/$1';
$route['bitcoin/send'] = 'bitcoin_test/sendtome';
$route['bitcoin/txn/(:any)'] = 'bitcoin_test/transaction/$1';
$route['bitcoin/reset'] = 'btc_internal/reset';

$route['bitcoin'] = 'bitcoin/panel';
$route['cashout'] = 'bitcoin/cashout';

$route['callback/install_config'] = 'btc_internal/install_config';

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
$route['admin/users/list'] = 'admin/user_list';
$route['admin/delete_item/(:any)'] = 'admin/delete_item/$1';
$route['admin/tokens'] = 'admin/user_tokens';
$route['admin/tokens/delete/(:any)'] = 'admin/delete_token/$1';
$route['admin/disputes'] = 'admin/dispute';

$route['order/dispute/(:num)'] = 'orders/dispute/$1';
$route['orders/dispute/(:num)'] = 'orders/dispute/$1';
// Vendor
$route['orders'] = 'orders/list_orders';
// Buye
$route['order/list'] = 'orders/list_purchases';
$route['order/recount'] = 'orders/recount_all';
$route['order/place/(:num)'] = 'orders/place/$1';
$route['order/finalize/(:num)'] = 'orders/finalize/$1';
$route['order/cancel/(:num)'] = 'orders/cancel/$1';
$route['order/received/(:any)'] = 'orders/received_order/$1';
$route['order/(:any)'] = 'orders/purchase_item/$1';
// Both


$route['404_override'] = '';
$route['default_controller'] = 'welcome';

/* End of file routes.php */
/* Location: ./application/config/routes.php */

