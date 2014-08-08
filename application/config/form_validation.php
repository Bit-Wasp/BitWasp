<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Form Validation Rules
 *
 */

$config = array('register_form' => array(
    array('field' => 'user_name',
        'label' => 'user name',
        'rules' => 'required|alpha_dash|min_length[3]|is_unique[users.user_name]'
    ),
    array('field' => 'password0',
        'label' => 'password',
        'rules' => 'required'
    ),
    array('field' => 'password1',
        'label' => 'password confirmation',
        'rules' => 'required|matches[password0]'
    ),
    array('field' => 'user_type',
        'label' => 'role',
        'rules' => 'required|numeric|check_valid_registration_role'
    ),
    array('field' => 'message_pin0',
        'label' => 'message PIN',
        'rules' => 'required'
    ),
    array('field' => 'message_pin1',
        'label' => 'message PIN confirmation',
        'rules' => 'required|matches[message_pin0]'
    ),
    array('field' => 'location',
        'label' => 'location',
        'rules' => 'required|check_valid_location'
    ),
    array('field' => 'local_currency',
        'label' => 'local currency',
        'rules' => 'required|check_valid_currency'
    ),
    array('field' => 'captcha',
        'label' => 'captcha',
        'rules' => 'required|check_captcha'
    )
),
    'register_no_pin_form' => array(
        array('field' => 'user_name',
            'label' => 'user name',
            'rules' => 'required|alpha_dash|min_length[3]|is_unique[users.user_name]'
        ),
        array('field' => 'password0',
            'label' => 'password',
            'rules' => 'required'
        ),
        array('field' => 'password1',
            'label' => 'password confirmation',
            'rules' => 'required|matches[password0]'
        ),
        array('field' => 'user_type',
            'label' => 'role',
            'rules' => 'required|numeric|check_valid_registration_role'
        ),
        array('field' => 'location',
            'label' => 'location',
            'rules' => 'required|check_valid_location'
        ),
        array('field' => 'local_currency',
            'label' => 'local currency',
            'rules' => 'required|check_valid_currency'
        ),
        array('field' => 'captcha',
            'label' => 'captcha',
            'rules' => 'required|check_captcha'
        )
    ),
    'login_form' => array(
        array('field' => 'user_name',
            'label' => 'username',
            'rules' => 'required'
        ),
        array('field' => 'password',
            'label' => 'password',
            'rules' => 'required'
        )
    ),
    'message_pin_form' => array(
        array('field' => 'pin',
            'label' => 'message pin',
            'rules' => 'required'
        )
    ),
    'send_message' => array(
        array('field' => 'recipient',
            'label' => 'recipient',
            'rules' => 'required|check_user_exists' // check user exists.
        ),
        array('field' => 'message',
            'label' => 'message',
            'rules' => 'required|check_pgp_required_for_user[recipient]'
        ),
        array('field' => 'delete_on_read',
            'label' => 'Delete After Reading?',
            'rules' => 'check_delete_on_read' // NULL or 1.
        )
    ),
    'add_pgp' => array(
        array('field' => 'public_key',
            'label' => 'public key',
            'rules' => 'required'
        )
    ),
    'delete_pgp' => array(
        array('field' => 'delete',
            'label' => '',
            'rules' => 'required|check_bool_areyousure' // callback for 0 or 1
        )
    ),
    'account_edit' => array(
        array('field' => 'location',
            'label' => 'location',
            'rules' => 'required|check_valid_location' // Error will detail incorrect location.
        ),
        array('field' => 'local_currency',
            'label' => 'local currency',
            'rules' => 'required|check_valid_currency'
        ),
        array('field' => 'display_login_time',
            'label' => 'displaying login time',
            'rules' => 'required|check_bool_enabled' // Error will show enabled/disabled
        ),
        array('field' => 'force_pgp_messages',
            'label' => 'forced PGP messages',
            'rules' => 'required|check_bool_enabled' // Error will show enabled/disabled
        ),
        array('field' => 'block_non_pgp',
            'label' => 'blocking non PGP messages.',
            'rules' => 'required|check_bool_enabled'
        )
    ),
    'account_edit_no_pgp' => array(
        array('field' => 'location',
            'label' => 'location',
            'rules' => 'required|check_valid_location' // Error will detail incorrect location.
        ),
        array('field' => 'local_currency',
            'label' => 'local currency',
            'rules' => 'required|check_valid_currency'
        ),
        array('field' => 'display_login_time',
            'label' => 'displaying login time',
            'rules' => 'required|check_bool_enabled' // Error will show enabled/disabled
        )
    ),
    'add_listing' => array(
        array('field' => 'name',
            'label' => 'item name',
            'rules' => 'required|max_length[100]',
        ),
        array('field' => 'description',
            'label' => 'item description',
            'rules' => 'required',
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
            'rules' => 'required',
        ),
        array('field' => 'description',
            'label' => 'description',
            'rules' => 'required',
        ),
        array('field' => 'category',
            'label' => 'category',
            'rules' => 'required|check_valid_category',
        ),
        array('field' => 'price',
            'label' => 'item price',
            'rules' => 'required|check_bitcoin_amount',
        ),
        array('field' => 'currency',
            'label' => '',
            'rules' => 'required|check_valid_currency',
        ),
        array('field' => 'ship_from',
            'label' => '',
            'rules' => 'required|check_valid_location_shipfrom'
        )
    ),
    'add_shipping_cost' => array(
        array('field' => 'add_location',
            'label' => 'location',
            'rules' => 'required|check_valid_location_shipfrom'
        ),
        array('field' => 'add_price',
            'label' => 'shipping price',
            'rules' => 'required|check_bitcoin_amount_free'
        )
    ),
    'authorize' => array(
        array('field' => 'password',
            'label' => 'password',
            'rules' => 'required'
        ),
        array('field' => 'captcha',
            'label' => 'captcha',
            'rules' => 'required|check_captcha'
        )
    ),
    'checkable' => array(
        array('field' => 'checkable',
            'label' => '',
            'rules' => 'check_checkable'
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
            'label' => 'site title',
            'rules' => 'required'
        ),
        array('field' => 'site_description',
            'label' => 'site description',
            'rules' => 'required'
        ),
        array('field' => 'openssl_keysize',
            'label' => 'OpenSSL keysize',
            'rules' => 'required|check_rsa_keysize'
        ),
        array('field' => 'allow_guests',
            'label' => 'allow guests',
            'rules' => 'required|check_bool_enabled'
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
            'label' => 'session timeout',
            'rules' => 'required|greater_than_equal_to[5]'
        ),
        array('field' => 'request_emails',
            'label' => 'request emails',
            'rules' => 'required|check_bool_enabled'
        ),
        array('field' => 'captcha_length',
            'label' => 'captcha length',
            'rules' => 'required|greater_than[0]|less_than_equal_to[16]'
        ),
        array('field' => 'registration_allowed',
            'label' => 'registration allowed?',
            'rules' => 'required|check_bool_enabled'
        ),
        array('field' => 'vendor_registration_allowed',
            'label' => 'vendor registration allowed?',
            'rules' => 'required|check_bool_enabled'
        ),
        array('field' => 'encrypt_private_messages',
            'label' => 'encrypted private messages',
            'rules' => 'required|check_bool_enabled'
        ),
        array('field' => 'force_vendor_pgp',
            'label' => 'force vendors using PGP',
            'rules' => 'required|check_bool_enabled'
        ),
        array('field' => 'entry_payment_vendor',
            'label' => 'vendor registration fee',
            'rules' => 'check_bitcoin_amount_free'
        ),
        array('field' => 'entry_payment_buyer',
            'label' => 'buyer registration fee',
            'rules' => 'check_bitcoin_amount_free'
        )
    ),
    'admin_edit_bitcoin' => array(
        array('field' => 'price_index',
            'label' => 'price Index',
            'rules' => 'required'
        ),
        array('field' => 'bip32_mpk',
            'label' => 'master public key',
            'rules' => 'required|validate_bip32_key|validate_is_public_bip32'
        ),
        array('field' => 'bip32_iteration',
            'label' => 'address index',
            'rules' => 'required|is_natural'
        )
    ),
    'admin_add_category' => array(
        array('field' => 'create_name',
            'label' => 'category name',
            'rules' => 'required'
        ),
        array('field' => 'category_parent',
            'label' => 'parent category',
            'rules' => 'required|check_is_parent_category'
        )
    ),
    'admin_rename_category' => array(
        array('field' => 'rename_id',
            'label' => 'category',
            'rules' => 'required|check_valid_category'
        ),
        array('field' => 'category_name',
            'label' => 'new name',
            'rules' => 'required'
        )
    ),
    'admin_delete_category' => array(
        array('field' => 'delete_id',
            'label' => 'category',
            'rules' => 'required|check_valid_category'
        )
    ),
    'admin_add_custom_location' => array(
        array('field' => 'create_location',
            'label' => 'location name',
            'rules' => 'required'
        ),
        array('field' => 'location',
            'label' => 'parent location',
            'rules' => 'required|check_custom_parent_location_exists'
        )
    ),
    'admin_delete_custom_location' => array(
        array('field' => 'location_delete',
            'label' => 'location',
            'rules' => 'required|check_custom_location_exists'
        )
    ),
    'admin_update_location_list_source' => array(
        array('field' => 'location_source',
            'label' => 'list source',
            'rules' => 'required|check_valid_location_list_source'
        )
    ),
    'admin_category_orphans' => array(
        array('field' => 'category_id',
            'label' => 'category',
            'rules' => 'required|check_valid_category_root'
        )
    ),
    'admin_create_token' => array(
        array('field' => 'user_role',
            'label' => 'user role',
            'rules' => 'required|check_role_any'
        ),
        array('field' => 'entry_payment',
            'label' => 'registration fee',
            'rules' => 'required|check_registration_token_charge'
        )
    ),
    'admin_delete_item' => array(
        array('field' => 'reason_for_removal',
            'label' => 'reason for removal.',
            'rules' => 'required'
        )
    ),
    'admin_ban_user' => array(
        array('field' => 'ban_user',
            'label' => '',
            'rules' => 'required|check_bool_areyousure'
        )
    ),
    'admin_edit_autorun' => array(
        array('field' => 'jobs[]',
            'label' => 'intervals',
            'rules' => 'required|check_autorun_interval'
        )
    ),
    'admin_dispute_message' => array(
        array('field' => 'admin_message',
            'label' => 'dispute response',
            'rules' => 'required'
        )
    ),
    'admin_trusted_user_update' => array(
        array('field' => 'trusted_user_review_count',
            'label' => 'review count',
            'rules' => 'required|is_natural'
        ),
        array('field' => 'trusted_user_rating',
            'label' => 'rating',
            'rules' => 'required|check_user_rating_input'
        ),
        array('field' => 'trusted_user_order_count',
            'label' => 'order count',
            'rules' => 'required|is_natural'
        )
    ),
    'admin_update_fee_config' => array(
        array('field' => 'default_rate',
            'label' => 'default fee rate',
            'rules' => 'required|greater_than_equal_to[0]'
        ),
        array('field' => 'minimum_fee',
            'label' => 'minimum fee',
            'rules' => 'required|greater_than_equal_to[0.0001]'
        ),
        array('field' => 'escrow_rate',
            'label' => 'additonal escrow charge',
            'rules' => 'required|greater_than_equal_to[0]',
        ),
        array('field' => 'upfront_rate',
            'label' => 'additional upfront charge',
            'rules' => 'required|greater_than_equal_to[0]',
        )
    ),
    'admin_add_fee' => array(
        array('field' => 'lower_limit',
            'label' => 'lower limit',
            'rules' => 'required|check_bitcoin_amount_free'
        ),
        array('field' => 'upper_limit',
            'label' => 'upper limit',
            'rules' => 'required|check_bitcoin_amount'
        ),
        array('field' => 'percentage_fee',
            'label' => 'percentage fee',
            'rules' => 'required|greater_than[0]'
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
            'label' => 'user delete',
            'rules' => 'required|check_bool_areyousure'
        )
    ),
    'admin_maintenance_mode' => array(
        array('field' => 'maintenance_mode',
            'label' => 'maintenance mode',
            'rules' => 'required|check_bool_areyousure'
        )
    ),
    'admin_tos' => array(
        array('field' => 'terms_of_service_toggle',
            'label' => 'terms of service',
            'rules' => 'required|check_bool_areyousure'
        )
    ),
    'add_dispute_update' => array(
        array('field' => 'update_message',
            'label' => 'dispute response',
            'rules' => 'required'
        )
    ),
    'order_dispute' => array(
        array('field' => 'dispute_message',
            'label' => 'dispute reason',
            'rules' => 'required|min_length[30]'
        )
    ),
    'order_place' => array(
        array('field' => 'buyer_address',
            'label' => 'address',
            'rules' => 'required'
        )
    ),
    'vendor_submit_review' => array(

        array('field' => 'buyer_communication',
            'label' => "buyer's communication",
            'rules' => 'required|check_valid_rating_choice'
        ),
        array('field' => 'buyer_cooperation',
            'label' => "buyer's cooperation",
            'rules' => 'required|check_valid_rating_choice'
        ),
        array('field' => 'buyer_comments_source',
            'label' => "comments source",
            'rules' => 'required|check_review_comments_source'
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
            'label' => 'origin location',
            'rules' => 'required|check_valid_location_shipto'
        )
    ),
    'ship_to_submit' => array(
        array('field' => 'location',
            'label' => 'destination location',
            'rules' => 'required|check_valid_location_shipto'
        )
    ),
    'submit_totp_token' => array(
        array('field' => 'totp_token',
            'label' => 'two factor token',
            'rules' => 'required|exact_length[6]|is_natural'
        )
    ),
    'submit_pgp_token' => array(
        array('field' => 'answer',
            'label' => 'solution',
            'rules' => 'required'
        )
    ),
    'submit_public_keys' => array(
        array('field' => 'public_key_list',
            'label' => 'public keys',
            'rules' => 'required'
        )
    ),
    'submit_payout_address' => array(
        array('field' => 'address',
            'label' => 'address',
            'rules' => 'required|check_bitcoin_address'
        ),
        array('field' => 'password',
            'label' => 'password',
            'rules' => 'required'
        )
    ),
    'submit_dispute_refund_address' => array(
        array('field' => 'refund_address',
            'label' => 'refund address',
            'rules' => 'required|check_bitcoin_address'
        )
    ),
    'submit_buyer_purchase' => array(
        array('field' => 'item_hash',
            'label' => 'public keys',
            'rules' => 'required|alpha_numeric'
        )
    ),
    'submit_buyer_order_recount' => array(
        array('field' => 'recount_order_id',
            'label' => '',
            'rules' => 'is_numeric'
        ),
        array('field' => 'quantity[]',
            'label' => 'quantity',
            'rules' => 'is_numeric'
        )
    ),
    'submit_buyer_order_place' => array(
        array('field' => 'order_place_id',
            'label' => '',
            'rules' => 'is_numeric'
        ),
        array('field' => 'quantity[]',
            'label' => 'quantity',
            'rules' => 'is_numeric'
        )
    ),
    'submit_buyer_cancel_order' => array(
        array('field' => 'order_cancel_id',
            'label' => '',
            'rules' => 'is_numeric'
        )
    ),
    'submit_buyer_received_upfront_order' => array(
        array('field' => 'order_received_upront_id',
            'label' => '',
            'rules' => 'is_numeric'
        )
    ),
    'submit_vendor_accept_order' => array(
        array('field' => 'vendor_accept_order_id',
            'label' => '',
            'rules' => 'is_numeric'
        )
    ),
    'submit_delete_message' => array(
        array('field' => 'delete_message_hash',
            'label' => '',
            'rules' => 'required|alpha_numeric'
        )
    ),
    'submit_delete_all_messages' => array(
        array('field' => 'delete_message',
            'label' => '',
            'rules' => 'required|alpha_numeric'
        )
    ),
    'submit_vendor_delete_listing' => array(
        array('field' => 'delete_listing_hash',
            'label' => '',
            'rules' => 'required|alpha_numeric'
        )
    ),
    'admin_delete_token' => array(
        array('field' => 'delete_token_content',
            'label' => '',
            'rules' => 'required|alpha_numeric'
        )
    ),
    'admin_delete_fee_rate' => array(
        array('field' => 'token_id',
            'label' => '',
            'rules' => 'required|is_numeric'
        )
    ),
    'admin_resolve_dispute' => array(
        array('field' => 'resolve_dispute_id',
            'label' => '',
            'rules' => 'required|is_numeric'
        )
    ),
    'submit_bip32_js' => array(
        array('field' => 'js_extended_public_key',
            'label' => 'wallet passphrase',
            'rules' => 'required'
        )
    ),
    'submit_bip32_manual' => array(
        array('field' => 'manual_public_key',
            'label' => 'extended public key',
            'rules' => 'required|validate_bip32_key|validate_is_public_bip32'
        )
    ),
    'submit_js_signed_transaction' => array(
        array('field' => 'js_transaction',
            'label' => 'wallet passphrase',
            'rules' => 'required'
        )
    ),
    'change_password' => array(
        array('field' => 'current_password',
            'label' => 'password',
            'rules' => 'required'
        ),
        array('field' => 'new_password0',
            'label' => 'password',
            'rules' => 'required'
        ),
        array('field' => 'new_password1',
            'label' => 'confirmation',
            'rules' => 'required|matches[new_password0]'
        )
    ),
    'submit_email_activation' => array(
        array('field' => 'email_address',
            'label' => 'email',
            'rules' => 'required'
        ),
        array('field' => 'activation_hash',
            'label' => 'activation token',
            'rules' => 'required'
        )
    ),
    'submit_new_email_address' => array(
        array('field' => 'email_address',
            'label' => 'email address',
            'rules' => 'required|valid_email|is_unique[users.email_address]'
        ),
        array('field' => 'password',
            'label' => 'password',
            'rules' => 'required'
        )
    ),
    'delete_email_change_record' => array(
        array('field' => 'delete_request[]',
            'label' => '',
            'rules' => 'required'
        )
    )
);

