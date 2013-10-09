<?php
// Add errors for:
//  callback_register_check_role
// callback_check_delete_on_read
// calback_recipient_exists
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
										array(	'field' => 'message_pin1',
												'label' => 'message PIN confirmation',
												'rules' => 'trim|required|matches[message_pin0]'
										),
										array(	'field' => 'location',
												'label' => 'location',
												'rules' => 'required|numeric'
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
												'rules' => 'required|numeric'
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
												'rules' => 'trim|required|callback_user_exists'		// check user exists.
										),
										array(	'field' => 'subject',
												'label' => 'subject',
												'rules' => 'trim|htmlspecialchars|xss_clean'
										),
										array(	'field' => 'message',
												'label' => 'message',
												'rules' => 'trim|required|htmlspecialchars|xss_clean|nl2br'
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
											  'label' => 'item name',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'description',
											  'label' => 'description',
											  'rules' => 'required|htmlspecialchars',
										),
										array('field' => 'category',
											  'label' => 'category',
											  'rules' => 'required|callback_check_category_exists',
										),
										array('field' => 'price',
											  'label' => 'price',
											  'rules' => 'numeric|callback_check_price_positive',
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
											  'label' => 'category',
											  'rules' => 'required|callback_check_category_exists',
										),
										array('field' => 'price',
											  'label' => 'price',
											  'rules' => 'numeric',
										),
										array('field' => 'currency',
											  'label' => 'currency',
											  'rules' => 'required|callback_check_currency_exists',
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
				'update_cashout_address' => array(		
										array('field' => 'cashout_address',
											  'label' => 'Cashout address',
											  'rules' => 'alpha_numeric|callback_check_bitcoin_address'
										)
							),
				'cashout_form' => array(
										array('field' => 'amount',
											  'label' => 'Amount',
											  'rules' => 'decimal|callback_has_sufficient_balance'
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
										)
							),
				'admin_edit_users' => array(
										array('field' => 'login_timeout',
											  'label' => 'Session timeout',
											  'rules' => 'trim|required|numeric'
										),
										array('field' => 'captcha_length',
											  'label' => 'Captcha length',
											  'rules' => 'trim|required|numeric|callback_check_captcha_length'
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
										)
							),
				'admin_edit_bitcoin' => array(
										array('field' => '',
											  'label' => '',
											  'rules' => ''
										),
										array('field' => '',
											  'label' => '',
											  'rules' => ''
										)
							),
				'admin_add_category' => array(
										array('field' => 'category_name',
											  'label' => 'Category name',
											  'rules' => 'required|htmlentities'
										),
										array('field' => 'category_parent',
											  'label' => 'Parent category',
											  'rules' => 'callback_check_category_exists'
										)
							),
				'admin_rename_category' => array(
										array('field' => 'category_id',
											  'label' => 'Category',
											  'rules' => 'callback_check_can_delete_category'
										),
										array('field' => 'category_name',
											  'label' => 'New name',
											  'rules' => 'required|htmlentities'
										)
							),
				'admin_delete_category' => array(
										array('field' => 'category_id',
											  'label' => 'Category',
											  'rules' => 'callback_check_can_delete_category'
										)
							),
				'admin_transfer_bitcoins' => array(
										array('field' => 'from',
											  'label' => 'Sending account',
											  'rules' => 'callback_check_bitcoin_account_exists'
										),
										array('field' => 'to',
											  'label' => 'Receiving account',
											  'rules' => 'callback_check_bitcoin_account_exists'
										),
										array('field' => 'amount',
											  'label' => 'amount',
											  'rules' => 'trim|required|numeric'
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
										)
							),
				'admin_delete_item' => array(
										array('field' => 'reason_for_removal',
											  'label' => 'reason for removal.',
											  'rules' => 'required|htmlentities'
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
										)
							)

			);
