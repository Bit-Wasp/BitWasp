<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Form Validation Rules
 * 
 * This file contains the form validation rules to be used throughout
 * the application. All rules are centralized here, never hard coded to
 * a form. For custom callbacks (rules prefixed with callback_) which
 * call functions in the controller performing the validation,
 * the corresponding error messages are found in ./application/language/english/form_validation_lang.php.
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
												'rules' => 'numeric|callback_check_role'
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
											  'rules' => 'callback_check_location'
										),
										array('field' => 'local_currency',
											  'label' => 'Local currency',
											  'rules' => 'callback_check_valid_currency'
										),
										array('field' => 'captcha',
                                              'label' => 'captcha',
                                              'rules' => 'trim|required|callback_check_captcha'
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
												'rules' => 'numeric|callback_check_role'
										),
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'callback_check_location'
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'callback_check_valid_currency'
										),
										array( 'field' => 'captcha',
                                               'label' => 'captcha',
                                               'rules' => 'trim|required|callback_check_captcha'
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
										),
										array(	'field' => 'captcha',
												'label' => 'captcha text',
												'rules' => 'required|callback_check_captcha'
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
												'rules' => 'trim|required|callback_check_user_exists'		// check user exists.
										),
										array(	'field' => 'subject',
												'label' => 'subject',
												'rules' => 'trim|htmlspecialchars|xss_clean'
										),
										array(	'field' => 'message',
												'label' => 'message',
												'rules' => 'trim|required|htmlspecialchars|xss_clean|nl2br|callback_check_pgp_is_required'
										),
										array(	'field' => 'delete_on_read',
												'label' => 'Delete After Reading?',
												'rules' => 'callback_check_delete_on_read'			// callback for NULL or 1.
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
												'rules' => 'callback_check_bool'		// callback for 0 or 1
										)
							),
				'account_edit' =>	array(
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'required|callback_check_location'	// Error will detail incorrect location.
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'callback_check_valid_currency'
										),
										array(	'field' => 'display_login_time',
												'label' => '',
												'rules' => 'callback_check_bool'		// Error will show enabled/disabled
										),
										array(	'field' => 'force_pgp_messages',
												'label' => 'forced PGP messages',
												'rules' => 'callback_check_bool'		// Error will show enabled/disabled
										),
										array(	'field' => 'two_factor_auth',
												'label' => 'two-factor authentication.',
												'rules' => 'callback_check_bool'		// Error will show enabled/disabled
										),
										array(	'field' => 'block_non_pgp',
												'label' => 'blocking non PGP messages.',
												'rules' => 'callback_check_bool'
										)
							),
				'account_edit_no_pgp'=>	array(
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'required|callback_check_location'	// Error will detail incorrect location.
										),
										array(	'field' => 'local_currency',
												'label' => 'Local currency',
												'rules' => 'callback_check_valid_currency'
										),
										array(	'field' => 'display_login_time',
												'label' => '',
												'rules' => 'callback_check_bool'		// Error will show enabled/disabled
										)
							),
				'two_factor' => array(	
										array('field' => 'answer',
											  'label' => 'solution',
											  'rules' => 'required'
										)
							),
				'add_listing' => array(
										array('field' => 'name',
											  'label' => 'Item name',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'description',
											  'label' => 'Description',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'category',
											  'label' => 'Category',
											  'rules' => 'required|callback_check_category_exists|callback_block_access_to_parent_category',
										),
										array('field' => 'ship_from',
											  'label' => 'Ship from',
											  'rules' => 'callback_check_location'
										),
										array('field' => 'price',
											  'label' => 'Price',
											  'rules' => 'callback_check_is_positive',
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
											  'rules' => 'required|callback_check_category_exists|callback_block_access_to_parent_category',
										),
										array('field' => 'price',
											  'label' => 'Price',
											  'rules' => 'callback_check_is_positive',
										),
										array('field' => 'currency',
											  'label' => 'Currency',
											  'rules' => 'callback_check_currency_exists',
										),
										array('field' => 'ship_from',
											  'label' => 'Ship from',
											  'rules' => 'callback_check_location'
										)
							),
				'add_shipping_cost' => array(
										array('field' => 'add_location',
											  'label' => 'Location',
											  'rules' => 'callback_check_shipping_location'
										),
										array('field' => 'add_price',
											  'label' => 'Price',
											  'rules' => 'callback_check_is_positive'
										)
							),
				'authorize' => array(
										array('field' => 'password',
											  'label' => 'password',
											  'rules' => 'required'
										),
										array('field' => 'captcha',
											  'label' => 'captcha',
											  'rules' => 'callback_check_captcha'
										) // This one's going to be annoying..
							),
				'add_image' => array(
										array('field' => 'userfile',
											  'label' => 'file',
											  'rules' => ''
										)
							),
				'admin_edit_' => array(
										array('field' => 'site_title',
											  'label' => 'Site title',
											  'rules' => 'required|htmlentities'
										),
										array('field' => 'site_description',
											  'label' => 'Site description',
											  'rules' => 'htmlentities'
										),
										array('field' => 'openssl_keysize',
											  'label' => 'keysize',
											  'rules' => 'numeric'
										),
										array('field' => 'allow_guests',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										),
										array('field' => 'global_proxy_type',
											  'label' => '',
											  'rules' => 'callback_check_proxy_type'
										),
										array('field' => 'global_proxy_url',
											  'label' => '',
											  'rules' => 'callback_check_proxy_url'
										)
							),
				'admin_edit_users' => array(
										array('field' => 'login_timeout',
											  'label' => 'Session timeout',
											  'rules' => 'trim|required|callback_check_session_timeout'
										),
										array('field' => 'captcha_length',
											  'label' => 'Captcha length',
											  'rules' => 'trim|required|callback_check_captcha_length'
										),
										array('field' => 'registration_allowed',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										),
										array('field' => 'vendor_registration_allowed',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										),
										array('field' => 'encrypt_private_messages',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										),
										array('field' => 'force_vendor_pgp',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										),
										array('field' => 'delete_messages_after',
											  'label' => 'Oldest message age',
											  'rules' => 'max_length[3]|callback_check_is_positive'
										),
										array('field' => 'entry_payment_vendor',
											  'label' => 'Vendor registration fee',
											  'rules' => 'callback_check_is_postive'
										),
										array('field' => 'entry_payment_buyer',
											  'label' => 'Buyer registration fee',
											  'rules' => 'callback_check_is_positive'
										)
							),
				'admin_edit_bitcoin' => array(
										array('field' => 'price_index',
											  'label' => 'Price Index',
											  'rules' => 'callback_check_price_index'
										),
										array('field' => 'electrum_mpk',
											  'label' => 'master public key',
											  'rules' => 'callback_check_master_public_key'
										),
							),
				'admin_add_category' => array(
										array('field' => 'create_name',
											  'label' => 'category name',
											  'rules' => 'required|htmlentities'
										),
										array('field' => 'category_parent',
											  'label' => 'Parent category',
											  'rules' => 'callback_check_category_exists'
										)
							),
				'admin_rename_category' => array(
										array('field' => 'rename_id',
											  'label' => 'Category',
											  'rules' => 'callback_check_can_delete_category'
										),
										array('field' => 'category_name',
											  'label' => 'New name',
											  'rules' => 'required|htmlentities'
										)
							),
				'admin_delete_category' => array(
										array('field' => 'delete_id',
											  'label' => 'Category',
											  'rules' => 'callback_check_can_delete_category'
										)
							),
				'admin_add_custom_location' => array(
										array('field' => 'create_location',
											  'label' => 'Location name',
											  'rules' => 'required|htmlentities'
										),
										array('field' => 'location',
											  'label' => 'Parent location',
											  'rules' => 'callback_check_custom_parent_location_exists'
										)
							),
				'admin_delete_custom_location' => array(
										array('field' => 'location_delete',
											  'label' => 'Location',
											  'rules' => 'callback_check_custom_location_exists'
										)
							),
				'admin_update_location_list_source' => array(
										array('field' => 'location_source',
											  'label' => 'List',
											  'rules' => 'callback_check_valid_location_list_source'
										)
							),
				'admin_category_orphans' => array(
										array('field' => 'category_id',
											  'label' => 'Category',
											  'rules' => 'callback_check_category_exists'
										)
							),
				'admin_create_token' => array(
										array('field' => 'user_role',
											  'label' => 'User role',
											  'rules' => 'required|callback_check_admin_roles'
										),
										array('field' => 'token_comment',
											  'label' => 'Comment (optional)',
											  'rules' => 'htmlentities'
										),
										array('field' => 'entry_payment',
											  'label' => 'Registration Fee',
											  'rules' => 'callback_check_registration_token_fee'
										)
							),
				'admin_delete_item' => array(
										array('field' => 'reason_for_removal',
											  'label' => 'reason for removal.',
											  'rules' => 'required|htmlentities'
										)
							),
				'admin_ban_user' => array(
										array('field' => 'ban_user',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										)
							),
				'admin_edit_autorun' => array(
										array('field' => 'jobs[]',
											  'label' => 'Intervals',
											  'rules' => 'is_numeric|callback_check_is_positive'
										)
							),
				'admin_dispute_message' => array(
										array('field' => 'admin_message',
											  'label' => 'dispute response',
											  'rules' => 'required|htmlentities'
										)
							),
				'admin_update_fee_config' => array(
										array('field' => 'default_rate',
											  'label' => 'Default fee rate',
											  'rules' => 'callback_check_is_positve'
										),
										array('field' => 'minimum_fee',
											  'label' => 'Minimum fee',
											  'rules' => 'callback_check_is_positive'
										)	  
							),
				'admin_add_fee' => array(
										array('field' => 'lower_limit',
											  'label' => 'Lower limit',
											  'rules' => 'callback_check_is_positve'
										),
										array('field' => 'upper_limit',
											  'label' => 'Upper limit',
											  'rules' => 'callback_check_is_positive'
										),
										array('field' => 'percentage_fee',
											  'label' => 'Percentage fee',
											  'rules' => 'callback_check_is_positive'
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
											  'rules' => 'callback_check_user_search_for'
										),
										array('field' => 'with_property',
											  'label' => '',
											  'rules' => 'callback_check_user_search_with_property'
										),
										array('field' => 'order_by',
											  'label' => '',
											  'rules' => 'callback_check_user_search_order_by'
										),
										array('field' => 'list',
											  'label' => '',
											  'rules' => 'callback_check_user_search_list'
										)
							),
				'admin_delete_user' => array(
										array('field' => 'user_delete',
											  'label' => '',
											  'rules' => 'callback_check_bool'
										)
							),
				'admin_maintenance_mode' => array(
										array('field' => 'maintenance_mode',
											  'label' => 'Maintenance mode',
											  'rules' => 'callback_check_bool'
										)
							),
				'admin_tos' => array(
										array('field' => 'terms_of_service_toggle',
											  'label' => 'Terms of Service',
											  'rules' => 'callback_check_bool'
										)
							),
				'add_dispute_update' => array(
										array('field' => 'update_message',
											  'label' => 'dispute response',
											  'rules' => 'required|htmlentities|min_length[30]'
										)
							),
				'order_dispute' => array(
										array('field' => 'dispute_message',
											  'label' => 'Dispute reason',
											  'rules' => 'required|min_length[30]|htmlentities'
										)
							),
				'order_place' => array(
										array('field' => 'buyer_address',
											  'label' => 'Address',
											  'rules' => 'required|htmlentities'
										),
										array('field' => 'bitcoin_public_key',
											  'label' => 'Public Key',
											  'rules' => 'required|callback_check_bitcoin_public_key'
										)
							),
				'vendor_accept_order' => array(
										array('field' => 'selected_escrow',
											  'label' => 'Escrow?',
											  'rules' => 'callback_check_bool'
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
							)
				
			);
