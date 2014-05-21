<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Form Validation Rules
 * 
 */

$config = array('register_form'=>array(
										array(	'field' => 'user_name',
												'label' => 'user name',
												'rules' => 'trim|required|alpha_dash|min_length[3]|is_unique[users.user_name]'
										),
										array( 	'field' => 'password0',
												'label' => 'password',
												'rules' => 'trim|required'
										),
										array(	'field' => 'password1',
												'label' => 'password confirmation',
												'rules' => 'trim|required|matches[password0]'
										),
										array(	'field' => 'user_type',
												'label' => 'role',
												'rules' => 'numeric|check_valid_registration_role'
										),
										array(	'field' => 'message_pin0',
												'label' => 'message PIN',
												'rules' => 'trim|required'
										),
										array('field' => 'message_pin1',
											  'label' => 'message PIN confirmation',
											  'rules' => 'trim|required|matches[message_pin0]'
										),
										array('field' => 'location',
											  'label' => 'location',
											  'rules' => 'check_valid_location'
										),
										array('field' => 'local_currency',
											  'label' => 'Local currency',
											  'rules' => 'check_valid_currency'
										),
										array(	'field' => 'captcha',
												'label' => 'captcha',
												'rules' => 'trim|required|check_captcha'
                                        )
							),
				'register_no_pin_form'=>array(
										array(	'field' => 'user_name',
												'label' => 'user name',
												'rules' => 'trim|required|alpha_dash|min_length[3]|is_unique[users.user_name]'
										),
										array( 	'field' => 'password0',
												'label' => 'password',
												'rules' => 'trim|required'
										),
										array(	'field' => 'password1',
												'label' => 'password confirmation',
												'rules' => 'trim|required|matches[password0]'
										),
										array(	'field' => 'user_type',
												'label' => 'role',
												'rules' => 'numeric|check_valid_registration_role'
										),
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'check_valid_location'
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'check_valid_currency'
										),
										array(	'field' => 'captcha',
												'label' => 'captcha',
												'rules' => 'trim|required|check_captcha'
										)
							),
				'login_form'	=>	array(
										array(	'field' => 'user_name',
												'label' => 'username',
												'rules' => 'trim|required'
										),
										array(	'field' => 'password',
												'label' => 'password',
												'rules' => 'required'
										)
							),
				'message_pin_form' => array(
										array(	'field' => 'pin',
												'label' => 'PIN',
												'rules' => 'trim|required' 
										)
							),
				'send_message' =>	array(
										array(	'field' => 'recipient',
												'label' => 'recipient.',
												'rules' => 'trim|required|htmlspecialchars|check_user_exists'		// check user exists.
										),
										array(	'field' => 'subject',
												'label' => 'subject',
												'rules' => 'trim|htmlspecialchars|xss_clean'
										),
										array(	'field' => 'message',
												'label' => 'message',
												'rules' => 'trim|required|htmlspecialchars|xss_clean|nl2br|check_pgp_required_for_user[recipient]'
										),
										array(	'field' => 'delete_on_read',
												'label' => 'Delete After Reading?',
												'rules' => 'check_delete_on_read'			// NULL or 1.
										)
							),
				'add_pgp' =>		array(
										array(	'field' => 'public_key',
												'label' => 'public key',
												'rules' => 'required'
										)
							),
				'delete_pgp' => 	array(
										array(	'field' => 'delete',
												'label' => '',
												'rules' => 'check_bool_areyousure'		// callback for 0 or 1
										)
							),
				'account_edit' =>	array(
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'required|check_valid_location'	// Error will detail incorrect location.
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'check_valid_currency'
										),
										array(	'field' => 'display_login_time',
												'label' => 'displaying login time',
												'rules' => 'check_bool_enabled'		// Error will show enabled/disabled
										),
										array(	'field' => 'force_pgp_messages',
												'label' => 'forced PGP messages',
												'rules' => 'check_bool_enabled'		// Error will show enabled/disabled
										),
										array(	'field' => 'block_non_pgp',
												'label' => 'blocking non PGP messages.',
												'rules' => 'check_bool_enabled'
										)
							),
				'account_edit_no_pgp'=>	array(
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'required|check_valid_location'	// Error will detail incorrect location.
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'check_valid_currency'
										),
										array(	'field' => 'display_login_time',
												'label' => 'displaying login time',
												'rules' => 'check_bool_enabled'		// Error will show enabled/disabled
										)
							),
				'add_listing' => array(
										array('field' => 'name',
											  'label' => 'item name',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'description',
											  'label' => 'item description',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'category',
											  'label' => 'category',
											  'rules' => 'required|check_valid_category',
										),
										array('field' => 'ship_from',
											  'label' => 'ship from',
											  'rules' => 'required|check_valid_location'
										),
										array('field' => 'price',
											  'label' => 'listing price',
											  'rules' => 'required|check_bitcoin_amount',
										),
										array('field' => 'hidden',
											  'label' => 'hidden item',
											  'rules' => 'required|check_bool_areyousure'
									),
										array('field' => 'prefer_upfront',
											  'label' => 'upfront prefered',
											  'rules' => 'required|check_bool_areyousure'
										)
											  
							),
				'edit_listing' => array(
										array('field' => 'name',
											  'label' => 'item name',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'description',
											  'label' => 'description',
											  'rules' => 'required|htmlspecialchars|nl2br',
										),
										array('field' => 'category',
											  'label' => 'Category',
											  'rules' => 'required|check_valid_category|check_not_parent_category',
										),
										array('field' => 'price',
											  'label' => 'item price',
											  'rules' => 'check_bitcoin_amount',
										),
										array('field' => 'currency',
											  'label' => '',
											  'rules' => 'check_valid_currency',
										),
										array('field' => 'ship_from',
											  'label' => '',
											  'rules' => 'check_valid_location_shipfrom'
										)
							),
				'add_shipping_cost' => array(
										array('field' => 'add_location',
											  'label' => 'Location',
											  'rules' => 'check_valid_location_shipfrom'
										),
										array('field' => 'add_price',
											  'label' => 'shipping price',
											  'rules' => 'check_bitcoin_amount_free'
										)
							),
				'authorize' => array(
										array('field' => 'password',
											  'label' => 'password',
											  'rules' => 'required'
										),
										array('field' => 'captcha',
											  'label' => 'captcha',
											  'rules' => 'check_captcha'
										) 
							),
				'add_image' => array(
										array('field' => 'userfile',
											  'label' => 'file',
											  'rules' => 'strip_image_tags'
										)
							),
				'admin_edit_' => array(
										array('field' => 'site_title',
											  'label' => 'Site title',
											  'rules' => 'required|htmlspecialchars'
										),
										array('field' => 'site_description',
											  'label' => 'Site description',
											  'rules' => 'htmlspecialchars'
										),
										array('field' => 'openssl_keysize',
											  'label' => 'keysize',
											  'rules' => 'check_rsa_keysize'
										),
										array('field' => 'allow_guests',
											  'label' => '',
											  'rules' => 'check_bool_enabled'
										),
										array('field' => 'global_proxy_type',
											  'label' => '',
											  'rules' => 'check_proxy_type'
										),
										array('field' => 'global_proxy_url',
											  'label' => '',
											  'rules' => 'check_proxy_url'
										)
							),
				'admin_edit_users' => array(
										array('field' => 'login_timeout',
											  'label' => 'Session timeout',
											  'rules' => 'trim|required|greater_than_equal_to[5]'
										),
										array('field' => 'captcha_length',
											  'label' => 'captcha',
											  'rules' => 'trim|required|greater_than[0]|less_than_equal_to[16]'
										),
										array('field' => 'registration_allowed',
											  'label' => 'registration allowed?',
											  'rules' => 'check_bool_enabled'
										),
										array('field' => 'vendor_registration_allowed',
											  'label' => 'vendor registration allowed?',
											  'rules' => 'check_bool_enabled'
										),
										array('field' => 'encrypt_private_messages',
											  'label' => 'encrypted private messages',
											  'rules' => 'check_bool_enabled'
										),
										array('field' => 'force_vendor_pgp',
											  'label' => 'force vendors using PGP',
											  'rules' => 'check_bool_enabled'
										),
										array('field' => 'entry_payment_vendor',
											  'label' => 'Vendor registration fee',
											  'rules' => 'check_bitcoin_amount_free'
										),
										array('field' => 'entry_payment_buyer',
											  'label' => 'Buyer registration fee',
											  'rules' => 'check_bitcoin_amount_free'
										)
							),
				'admin_edit_bitcoin' => array(
										array('field' => 'price_index',
											  'label' => 'Price Index',
											  'rules' => 'check_price_index'
										),
										array('field' => 'electrum_mpk',
											  'label' => 'master public key',
											  'rules' => 'check_master_public_key'
										),
										array('field' => 'electrum_iteration',
											  'label' => 'electrum address index',
											  'rules' => 'required|is_natural_no_zero'
										)
							),
				'admin_add_category' => array(
										array('field' => 'create_name',
											  'label' => 'category name',
											  'rules' => 'required|htmlspecialchars'
										),
										array('field' => 'category_parent',
											  'label' => 'Parent category',
											  'rules' => 'check_is_parent_category'
										)
							),
				'admin_rename_category' => array(
										array('field' => 'rename_id',
											  'label' => 'Category',
											  'rules' => 'check_valid_category'
										),
										array('field' => 'category_name',
											  'label' => 'New name',
											  'rules' => 'required|htmlspecialchars'
										)
							),
				'admin_delete_category' => array(
										array('field' => 'delete_id',
											  'label' => 'Category',
											  'rules' => 'check_valid_category'
										)
							),
				'admin_add_custom_location' => array(
										array('field' => 'create_location',
											  'label' => 'Location name',
											  'rules' => 'required|htmlspecialchars'
										),
										array('field' => 'location',
											  'label' => 'Parent location',
											  'rules' => 'check_custom_parent_location_exists'
										)
							),
				'admin_delete_custom_location' => array(
										array('field' => 'location_delete',
											  'label' => 'Location',
											  'rules' => 'check_custom_location_exists'
										)
							),
				'admin_update_location_list_source' => array(
										array('field' => 'location_source',
											  'label' => 'List',
											  'rules' => 'check_valid_location_list_source'
										)
							),
				'admin_category_orphans' => array(
										array('field' => 'category_id',
											  'label' => 'Category',
											  'rules' => 'check_valid_category_root'
										)
							),
				'admin_create_token' => array(
										array('field' => 'user_role',
											  'label' => 'User role',
											  'rules' => 'required|check_role_any'
										),
										array('field' => 'token_comment',
											  'label' => 'Comment (optional)',
											  'rules' => 'htmlspecialchars'
										),
										array('field' => 'entry_payment',
											  'label' => 'Registration Fee',
											  'rules' => 'check_registration_token_charge'
										)
							),
				'admin_delete_item' => array(
										array('field' => 'reason_for_removal',
											  'label' => 'reason for removal.',
											  'rules' => 'required|htmlspecialchars'
										)
							),
				'admin_ban_user' => array(
										array('field' => 'ban_user',
											  'label' => '',
											  'rules' => 'check_bool_areyousure'
										)
							),
				'admin_edit_autorun' => array(
										array('field' => 'jobs[]',
											  'label' => 'Intervals',
											  'rules' => 'check_autorun_interval'
										)
							),
				'admin_dispute_message' => array(
										array('field' => 'admin_message',
											  'label' => 'dispute response',
											  'rules' => 'required|htmlspecialchars'
										)
							),
				'admin_trusted_user_update' => array(
										array(	'field' => 'trusted_user_review_count',
												'label' => 'review count',
												'rules' => 'is_natural'
										),
										array(	'field' => 'trusted_user_rating',
												'label' => 'rating',
												'rules' => 'check_user_rating_input'
										),
										array(	'field' => 'trusted_user_order_count',
												'label' => 'order count',
												'rules' => 'is_natural'
										)
							),
				'admin_update_fee_config' => array(
										array('field' => 'default_rate',
											  'label' => 'Default fee rate',
											  'rules' => 'greater_than_equal_to[0]'
										),
										array('field' => 'minimum_fee',
											  'label' => 'Minimum fee',
											  'rules' => 'greater_than_equal_to[0.0001]'
										),
										array('field' => 'escrow_rate',
											  'label' => 'additonal escrow rate',
											  'rules' => 'greater_than_equal_to[0]',
										),
										array('field' => 'upfront_rate',
											  'label' => 'additional upfront rate',
											  'rules' => 'greater_than_equal_to[0]',
										)
							),
				'admin_add_fee' => array(
										array('field' => 'lower_limit',
											  'label' => 'range lower limit',
											  'rules' => 'check_bitcoin_amount_free'
										),
										array('field' => 'upper_limit',
											  'label' => 'range upper limit',
											  'rules' => 'check_bitcoin_amount|greater_than[lower_limit]'
										),
										array('field' => 'percentage_fee',
											  'label' => 'percentage fee',
											  'rules' => 'greater_than[0]'
										)	  
							),
				'admin_search_username' => array(
										array('field' => 'user_name',
											  'label' => 'user name',
											  'rules' => 'required'
										)
							),
				'admin_search_user_list' => array(
										array('field' => 'search_for',
											  'label' => '',
											  'rules' => 'check_user_search_for'
										),
										array('field' => 'with_property',
											  'label' => '',
											  'rules' => 'check_user_search_with_property'
										),
										array('field' => 'order_by',
											  'label' => '',
											  'rules' => 'check_user_search_order_by'
										),
										array('field' => 'list',
											  'label' => '',
											  'rules' => 'check_user_search_list'
										)
							),
				'admin_delete_user' => array(
										array('field' => 'user_delete',
											  'label' => '',
											  'rules' => 'check_bool_areyousure'
										)
							),
				'admin_maintenance_mode' => array(
										array('field' => 'maintenance_mode',
											  'label' => 'Maintenance mode',
											  'rules' => 'check_bool_areyousure'
										)
							),
				'admin_tos' => array(
										array('field' => 'terms_of_service_toggle',
											  'label' => 'Terms of Service',
											  'rules' => 'check_bool_areyousure'
										)
							),
				'add_dispute_update' => array(
										array('field' => 'update_message',
											  'label' => 'dispute response',
											  'rules' => 'required|htmlspecialchars|min_length[30]'
										)
							),
				'order_dispute' => array(
										array('field' => 'dispute_message',
											  'label' => 'Dispute reason',
											  'rules' => 'required|min_length[30]|htmlspecialchars'
										)
							),
				'order_place' => array(
									
										array('field' => 'buyer_address',
											  'label' => 'Address',
											  'rules' => 'required|htmlspecialchars'
										),
										// PHP checking done in controller.
										array('field' => 'bitcoin_public_key',
											  'label' => 'Public Key',
											  'rules' => 'required|check_bitcoin_public_key'
										)
							),
				'vendor_submit_review' => array(
										array('field' => 'buyer_communication',
											  'label' => "buyer's communication",
											  'rules' => 'callback_check_numeric_rating'
										),
										array('field' => 'buyer_cooperation',
											  'label' => "buyer's cooperation",
											  'rules' => 'callback_check_numeric_rating'
										),
										array('field' => 'buyer_comments_source',
											  'label' => "buyer comments source",
											  'rules' => 'callback_check_comments_source'
										)
							),
				'input_transaction' => array(
										array('field' => 'partially_signed_transaction',
											  'label' => 'partially signed transaction',
											  'rules' => 'required'
										)
							),
				'ship_from_submit' => array(
										array('field' => 'location',
											  'label' => 'Origin location',
											  'rules' => 'check_valid_location_shipto'
										)
							),
				'ship_to_submit' => array(
										array('field' => 'location',
											  'label' => 'Destination location',
											  'rules' => 'check_valid_location_shipto'
										)
							),							
				'submit_totp_token' => array(
										array('field' => 'totp_token',
											  'label' => 'Auth token',
											  'rules' => 'exact_length[6]|is_natural'
										)
							),
				'submit_pgp_token' => array(	
										array('field' => 'answer',
											  'label' => 'solution',
											  'rules' => 'required'
										)
							)
			);

