<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_base extends CI_Migration {

	public function up() {

		## Create Table bw_alerts
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`source` varchar(30) NOT NULL ");
		$this->dbforge->add_field("`message` text NOT NULL ");
		$this->dbforge->add_field("`time` int(11) NOT NULL ");
		$this->dbforge->create_table("bw_alerts", TRUE);
		$this->db->query('ALTER TABLE  `bw_alerts` ENGINE = InnoDB');
		## Create Table bw_autorun
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`name` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`interval` varchar(8) NOT NULL ");
		$this->dbforge->add_field("`interval_type` varchar(10) NOT NULL ");
		$this->dbforge->add_field("`last_update` varchar(20) NULL ");
		$this->dbforge->add_field("`description` varchar(200) NOT NULL ");
		$this->dbforge->add_field("`index` varchar(40) NOT NULL ");
		$this->dbforge->create_table("bw_autorun", TRUE);
		$this->db->query('ALTER TABLE  `bw_autorun` ENGINE = InnoDB');
		## Create Table bw_bip32_keys
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`key` varchar(600) NOT NULL ");
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`provider` enum('Manual','Onchain','JS') NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`key_index` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_bip32_keys", TRUE);
		$this->db->query('ALTER TABLE  `bw_bip32_keys` ENGINE = InnoDB');
		## Create Table bw_bip32_user_keys
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`order_hash` varchar(30) NOT NULL ");
		$this->dbforge->add_field("`user_role` enum('Buyer','Vendor','Admin') NOT NULL ");
		$this->dbforge->add_field("`parent_extended_public_key` varchar(300) NOT NULL ");
		$this->dbforge->add_field("`provider` enum('Manual','Onchain','JS') NOT NULL ");
		$this->dbforge->add_field("`extended_public_key` varchar(300) NOT NULL ");
		$this->dbforge->add_field("`public_key` varchar(130) NOT NULL ");
		$this->dbforge->add_field("`key_index` int(9) NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->create_table("bw_bip32_user_keys", TRUE);
		$this->db->query('ALTER TABLE  `bw_bip32_user_keys` ENGINE = InnoDB');
		## Create Table bw_bitcoin_public_keys
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`public_key` varchar(150) NOT NULL ");
		$this->dbforge->create_table("bw_bitcoin_public_keys", TRUE);
		$this->db->query('ALTER TABLE  `bw_bitcoin_public_keys` ENGINE = InnoDB');
		## Create Table bw_blocks
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`hash` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`prev_hash` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`height` int(10) NOT NULL ");
		$this->dbforge->create_table("bw_blocks", TRUE);
		$this->db->query('ALTER TABLE  `bw_blocks` ENGINE = InnoDB');
		## Create Table bw_categories
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`description` varchar(100) NOT NULL ");
		$this->dbforge->add_field("`hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`name` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`parent_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_categories", TRUE);
		$this->db->query('ALTER TABLE  `bw_categories` ENGINE = InnoDB');
		## Create Table bw_config
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`parameter` varchar(30) NOT NULL ");
		$this->dbforge->add_field("`value` text NOT NULL ");
		$this->dbforge->create_table("bw_config", TRUE);
		$this->db->query('ALTER TABLE  `bw_config` ENGINE = InnoDB');
		## Create Table bw_currencies
		$this->dbforge->add_field("`id` int(9) NOT NULL ");
		$this->dbforge->add_field("`name` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`symbol` varchar(10) NOT NULL ");
		$this->dbforge->add_field("`code` varchar(5) NOT NULL ");
		$this->dbforge->add_field("`crypto_magic_byte` varchar(2) NOT NULL ");
		$this->dbforge->create_table("bw_currencies", TRUE);
		$this->db->query('ALTER TABLE  `bw_currencies` ENGINE = InnoDB');
		## Create Table bw_disputes
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`dispute_message` text NOT NULL ");
		$this->dbforge->add_field("`disputing_user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`other_user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`last_update` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`final_response` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->create_table("bw_disputes", TRUE);
		$this->db->query('ALTER TABLE  `bw_disputes` ENGINE = InnoDB');
		## Create Table bw_disputes_updates
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`dispute_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`posting_user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`message` text NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->create_table("bw_disputes_updates", TRUE);
		$this->db->query('ALTER TABLE  `bw_disputes_updates` ENGINE = InnoDB');
		## Create Table bw_entry_payment
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`user_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`amount` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`bitcoin_address` varchar(40) NOT NULL ");
		$this->dbforge->create_table("bw_entry_payment", TRUE);
		$this->db->query('ALTER TABLE  `bw_entry_payment` ENGINE = InnoDB');
		## Create Table bw_exchange_rates
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`usd` decimal(10,4) NOT NULL ");
		$this->dbforge->add_field("`eur` decimal(10,4) NOT NULL ");
		$this->dbforge->add_field("`gbp` decimal(10,4) NOT NULL ");
		$this->dbforge->add_field("`btc` int(11) NOT NULL DEFAULT '1' ");
		$this->dbforge->add_field("`price_index` varchar(45) NULL ");
		$this->dbforge->create_table("bw_exchange_rates", TRUE);
		$this->db->query('ALTER TABLE  `bw_exchange_rates` ENGINE = InnoDB');
		## Create Table bw_fees
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`low` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`high` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`rate` decimal(4,3) NOT NULL ");
		$this->dbforge->create_table("bw_fees", TRUE);
		$this->db->query('ALTER TABLE  `bw_fees` ENGINE = InnoDB');
		## Create Table bw_images
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`encoded` longtext NOT NULL ");
		$this->dbforge->add_field("`height` int(11) NOT NULL ");
		$this->dbforge->add_field("`width` int(11) NOT NULL ");
		$this->dbforge->create_table("bw_images", TRUE);
		$this->db->query('ALTER TABLE  `bw_images` ENGINE = InnoDB');
		## Create Table bw_item_images
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`image_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`item_hash` varchar(25) NOT NULL ");
		$this->dbforge->create_table("bw_item_images", TRUE);
		$this->db->query('ALTER TABLE  `bw_item_images` ENGINE = InnoDB');
		## Create Table bw_items
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`vendor_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`price` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`currency` int(5) NOT NULL ");
		$this->dbforge->add_field("`category` int(5) NOT NULL ");
		$this->dbforge->add_field("`name` varchar(100) NOT NULL ");
		$this->dbforge->add_field("`description` blob NOT NULL ");
		$this->dbforge->add_field("`main_image` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`add_time` int(20) NOT NULL ");
		$this->dbforge->add_field("`update_time` int(20) NULL ");
		$this->dbforge->add_field("`hidden` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`prefer_upfront` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`ship_from` int(5) NOT NULL ");
		$this->dbforge->create_table("bw_items", TRUE);
		$this->db->query('ALTER TABLE  `bw_items` ENGINE = InnoDB');
		## Create Table bw_key_usage
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`usage` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`mpk` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`iteration` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`public_key` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`address` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`fees_user_hash` varchar(25) NOT NULL ");
		$this->dbforge->create_table("bw_key_usage", TRUE);
		$this->db->query('ALTER TABLE  `bw_key_usage` ENGINE = InnoDB');
		## Create Table bw_locations_custom_list
		$this->dbforge->add_field("`id` int(4) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`location` varchar(60) NOT NULL ");
		$this->dbforge->add_field("`parent_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`hash` varchar(30) NOT NULL ");
		$this->dbforge->create_table("bw_locations_custom_list", TRUE);
		$this->db->query('ALTER TABLE  `bw_locations_custom_list` ENGINE = InnoDB');
		## Create Table bw_locations_default_list
		$this->dbforge->add_field("`id` int(4) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`location` varchar(60) NOT NULL ");
		$this->dbforge->add_field("`parent_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`hash` varchar(30) NOT NULL ");
		$this->dbforge->create_table("bw_locations_default_list", TRUE);
		$this->db->query('ALTER TABLE  `bw_locations_default_list` ENGINE = InnoDB');
		## Create Table bw_logs
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`caller` varchar(35) NOT NULL ");
		$this->dbforge->add_field("`message` varchar(250) NOT NULL ");
		$this->dbforge->add_field("`title` varchar(50) NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`info_level` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`hash` varchar(25) NOT NULL ");
		$this->dbforge->create_table("bw_logs", TRUE);
		$this->db->query('ALTER TABLE  `bw_logs` ENGINE = InnoDB');
		## Create Table bw_messages
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`content` blob NOT NULL ");
		$this->dbforge->add_field("`encrypted` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`rsa_encrypted` enum('0','1') NULL ");
		$this->dbforge->add_field("`aes_iv` mediumblob NULL ");
		$this->dbforge->add_field("`aes_key` mediumblob NULL ");
		$this->dbforge->add_field("`hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`remove_on_read` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`time` int(20) NOT NULL ");
		$this->dbforge->add_field("`to` int(9) NOT NULL ");
		$this->dbforge->add_field("`viewed` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_messages", TRUE);
		$this->db->query('ALTER TABLE  `bw_messages` ENGINE = InnoDB');
		## Create Table bw_onchain_requests
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`user_token` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`totp_secret` varchar(50) NOT NULL ");
		$this->dbforge->add_field("`request_type` enum('mpk','sign') NOT NULL ");
		$this->dbforge->add_field("`sign_order_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->create_table("bw_onchain_requests", TRUE);
		$this->db->query('ALTER TABLE  `bw_onchain_requests` ENGINE = InnoDB');
		## Create Table bw_orders
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`created_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`currency` int(2) NOT NULL ");
		$this->dbforge->add_field("`items` text NOT NULL ");
		$this->dbforge->add_field("`price` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`shipping_costs` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`fees` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`extra_fees` decimal(20,8) NOT NULL DEFAULT '0.00000000' ");
		$this->dbforge->add_field("`time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`progress` int(1) NOT NULL ");
		$this->dbforge->add_field("`buyer_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`vendor_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`finalized` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`confirmed_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`vendor_selected_escrow` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`vendor_selected_upfront` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`vendor_selected_upfront_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`dispatched_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`dispatched` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`disputed_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`disputed` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`selected_payment_type_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`received_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`finalized_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`buyer_public_key` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`vendor_public_key` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`admin_public_key` varchar(150) NOT NULL ");
		$this->dbforge->add_field("`redeemScript` varchar(500) NOT NULL ");
		$this->dbforge->add_field("`address` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`unsigned_transaction` text NOT NULL ");
		$this->dbforge->add_field("`json_inputs` text NOT NULL ");
		$this->dbforge->add_field("`partially_signed_transaction` text NOT NULL ");
		$this->dbforge->add_field("`partially_signing_user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`partially_signed_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`refund_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`refund_completed_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`refunded` enum('0','1') NULL ");
		$this->dbforge->add_field("`final_transaction_id` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`paid_time` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`finalized_correctly` enum('0','1') NOT NULL ");
		$this->dbforge->create_table("bw_orders", TRUE);
		$this->db->query('ALTER TABLE  `bw_orders` ENGINE = InnoDB');
		## Create Table bw_page_authorization
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`auth_level` varchar(15) NOT NULL ");
		$this->dbforge->add_field("`system` enum('0','1') NOT NULL ");
		$this->dbforge->add_field("`timeout` int(3) NOT NULL ");
		$this->dbforge->add_field("`URI` varchar(30) NOT NULL ");
		$this->dbforge->create_table("bw_page_authorization", TRUE);
		$this->db->query('ALTER TABLE  `bw_page_authorization` ENGINE = InnoDB');
		## Create Table bw_paid_orders_cache
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_paid_orders_cache", TRUE);
		$this->db->query('ALTER TABLE  `bw_paid_orders_cache` ENGINE = InnoDB');
		## Create Table bw_pgp_keys
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`fingerprint` varchar(128) NOT NULL ");
		$this->dbforge->add_field("`public_key` blob NOT NULL ");
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_pgp_keys", TRUE);
		$this->db->query('ALTER TABLE  `bw_pgp_keys` ENGINE = InnoDB');
		## Create Table bw_registration_tokens
		$this->dbforge->add_field("`id` int(11) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`comment` varchar(100) NOT NULL ");
		$this->dbforge->add_field("`user_type` enum('1','2','3') NOT NULL ");
		$this->dbforge->add_field("`token_content` varchar(128) NOT NULL ");
		$this->dbforge->add_field("`entry_payment` decimal(20,8) NULL ");
		$this->dbforge->create_table("bw_registration_tokens", TRUE);
		$this->db->query('ALTER TABLE  `bw_registration_tokens` ENGINE = InnoDB');
		## Create Table bw_review_auth_tokens
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`auth_token` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`user_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`review_type` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_review_auth_tokens", TRUE);
		$this->db->query('ALTER TABLE  `bw_review_auth_tokens` ENGINE = InnoDB');
		## Create Table bw_reviews
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`review_type` varchar(10) NOT NULL ");
		$this->dbforge->add_field("`subject_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`json` text NOT NULL ");
		$this->dbforge->add_field("`average_rating` varchar(4) NOT NULL ");
		$this->dbforge->add_field("`timestamp` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`disputed` enum('0','1') NOT NULL ");
		$this->dbforge->create_table("bw_reviews", TRUE);
		$this->db->query('ALTER TABLE  `bw_reviews` ENGINE = InnoDB');
		## Create Table bw_shipping_costs
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`item_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`destination_id` varchar(10) NOT NULL ");
		$this->dbforge->add_field("`cost` decimal(20,8) NOT NULL ");
		$this->dbforge->add_field("`enabled` enum('0','1') NULL DEFAULT '1' ");
		$this->dbforge->create_table("bw_shipping_costs", TRUE);
		$this->db->query('ALTER TABLE  `bw_shipping_costs` ENGINE = InnoDB');
		## Create Table bw_transactions_block_cache
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`tx_id` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`tx_raw` text NULL ");
		$this->dbforge->add_field("`block_height` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_transactions_block_cache", TRUE);
		$this->db->query('ALTER TABLE  `bw_transactions_block_cache` ENGINE = InnoDB');
		## Create Table bw_transactions_broadcast_cache
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`transaction` varchar(1024) NOT NULL ");
		$this->dbforge->add_field("`attempts_remaining` enum('1','2','3','4','5') NOT NULL DEFAULT '2' ");
		$this->dbforge->create_table("bw_transactions_broadcast_cache", TRUE);
		$this->db->query('ALTER TABLE  `bw_transactions_broadcast_cache` ENGINE = InnoDB');
		## Create Table bw_transactions_expected_cache
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`outputs_hash` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`address` varchar(50) NOT NULL ");
		$this->dbforge->add_field("`order_id` int(9) NOT NULL ");
		$this->dbforge->create_table("bw_transactions_expected_cache", TRUE);
		$this->db->query('ALTER TABLE  `bw_transactions_expected_cache` ENGINE = InnoDB');
		## Create Table bw_transactions_payments_cache
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`block_height` int(9) NULL ");
		$this->dbforge->add_field("`tx_id` varchar(64) NULL ");
		$this->dbforge->add_field("`address` varchar(40) NULL ");
		$this->dbforge->add_field("`value` decimal(20,8) NULL ");
		$this->dbforge->add_field("`vout` int(9) NULL ");
		$this->dbforge->add_field("`pkScript` varchar(150) NULL ");
		$this->dbforge->add_field("`order_id` int(9) NULL ");
		$this->dbforge->add_field("`purpose` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`fees_user_hash` varchar(25) NOT NULL ");
		$this->dbforge->create_table("bw_transactions_payments_cache", TRUE);
		$this->db->query('ALTER TABLE  `bw_transactions_payments_cache` ENGINE = InnoDB');
		## Create Table bw_two_factor_tokens
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`user_id` int(9) NOT NULL ");
		$this->dbforge->add_field("`solution` varchar(128) NOT NULL ");
		$this->dbforge->create_table("bw_two_factor_tokens", TRUE);
		$this->db->query('ALTER TABLE  `bw_two_factor_tokens` ENGINE = InnoDB');
		## Create Table bw_used_public_keys
		$this->dbforge->add_field("`id` int(9) NOT NULL ");
		$this->dbforge->add_field("`public_key_sha256` varchar(64) NOT NULL ");
		$this->dbforge->create_table("bw_used_public_keys", TRUE);
		$this->db->query('ALTER TABLE  `bw_used_public_keys` ENGINE = InnoDB');
		## Create Table bw_users
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`banned` enum('0','1') NULL ");
		$this->dbforge->add_field("`block_non_pgp` enum('0','1') NULL ");
		$this->dbforge->add_field("`entry_paid` enum('0','1') NULL ");
		$this->dbforge->add_field("`force_pgp_messages` enum('0','1') NULL ");
		$this->dbforge->add_field("`location` int(3) NOT NULL ");
		$this->dbforge->add_field("`login_time` int(20) NOT NULL ");
		$this->dbforge->add_field("`display_login_time` enum('0','1') NULL ");
		$this->dbforge->add_field("`password` varchar(128) NOT NULL ");
		$this->dbforge->add_field("`public_key` blob NOT NULL ");
		$this->dbforge->add_field("`private_key` blob NOT NULL ");
		$this->dbforge->add_field("`private_key_salt` varchar(64) NOT NULL ");
		$this->dbforge->add_field("`register_time` int(20) NOT NULL ");
		$this->dbforge->add_field("`salt` varchar(128) NOT NULL ");
		$this->dbforge->add_field("`user_hash` varchar(25) NOT NULL ");
		$this->dbforge->add_field("`user_name` varchar(40) NOT NULL ");
		$this->dbforge->add_field("`user_role` enum('Buyer','Vendor','Admin') NOT NULL ");
		$this->dbforge->add_field("`local_currency` int(11) NOT NULL ");
		$this->dbforge->add_field("`completed_order_count` int(9) NULL ");
		$this->dbforge->add_field("`totp_secret` varchar(25) NULL ");
		$this->dbforge->add_field("`totp_two_factor` enum('0','1') NULL ");
		$this->dbforge->add_field("`pgp_two_factor` enum('0','1') NULL ");
		$this->dbforge->add_field("`wallet_salt` varchar(128) NOT NULL ");
		$this->dbforge->create_table("bw_users", TRUE);
		$this->db->query('ALTER TABLE  `bw_users` ENGINE = InnoDB');
		## Create Table bw_watched_addresses
		$this->dbforge->add_field("`id` int(9) NOT NULL auto_increment");
		$this->dbforge->add_key("id",true);
		$this->dbforge->add_field("`purpose` varchar(20) NOT NULL ");
		$this->dbforge->add_field("`address` varchar(35) NOT NULL ");
		$this->dbforge->create_table("bw_watched_addresses", TRUE);
		$this->db->query('ALTER TABLE  `bw_watched_addresses` ENGINE = InnoDB');
	 }

	public function down()	{
		### Drop table bw_alerts ##
		$this->dbforge->drop_table("bw_alerts", TRUE);
		### Drop table bw_autorun ##
		$this->dbforge->drop_table("bw_autorun", TRUE);
		### Drop table bw_bip32_keys ##
		$this->dbforge->drop_table("bw_bip32_keys", TRUE);
		### Drop table bw_bip32_user_keys ##
		$this->dbforge->drop_table("bw_bip32_user_keys", TRUE);
		### Drop table bw_bitcoin_public_keys ##
		$this->dbforge->drop_table("bw_bitcoin_public_keys", TRUE);
		### Drop table bw_blocks ##
		$this->dbforge->drop_table("bw_blocks", TRUE);
		### Drop table bw_categories ##
		$this->dbforge->drop_table("bw_categories", TRUE);
		### Drop table bw_config ##
		$this->dbforge->drop_table("bw_config", TRUE);
		### Drop table bw_currencies ##
		$this->dbforge->drop_table("bw_currencies", TRUE);
		### Drop table bw_disputes ##
		$this->dbforge->drop_table("bw_disputes", TRUE);
		### Drop table bw_disputes_updates ##
		$this->dbforge->drop_table("bw_disputes_updates", TRUE);
		### Drop table bw_entry_payment ##
		$this->dbforge->drop_table("bw_entry_payment", TRUE);
		### Drop table bw_exchange_rates ##
		$this->dbforge->drop_table("bw_exchange_rates", TRUE);
		### Drop table bw_fees ##
		$this->dbforge->drop_table("bw_fees", TRUE);
		### Drop table bw_images ##
		$this->dbforge->drop_table("bw_images", TRUE);
		### Drop table bw_item_images ##
		$this->dbforge->drop_table("bw_item_images", TRUE);
		### Drop table bw_items ##
		$this->dbforge->drop_table("bw_items", TRUE);
		### Drop table bw_key_usage ##
		$this->dbforge->drop_table("bw_key_usage", TRUE);
		### Drop table bw_locations_custom_list ##
		$this->dbforge->drop_table("bw_locations_custom_list", TRUE);
		### Drop table bw_locations_default_list ##
		$this->dbforge->drop_table("bw_locations_default_list", TRUE);
		### Drop table bw_logs ##
		$this->dbforge->drop_table("bw_logs", TRUE);
		### Drop table bw_messages ##
		$this->dbforge->drop_table("bw_messages", TRUE);
		### Drop table bw_onchain_requests ##
		$this->dbforge->drop_table("bw_onchain_requests", TRUE);
		### Drop table bw_orders ##
		$this->dbforge->drop_table("bw_orders", TRUE);
		### Drop table bw_page_authorization ##
		$this->dbforge->drop_table("bw_page_authorization", TRUE);
		### Drop table bw_paid_orders_cache ##
		$this->dbforge->drop_table("bw_paid_orders_cache", TRUE);
		### Drop table bw_pgp_keys ##
		$this->dbforge->drop_table("bw_pgp_keys", TRUE);
		### Drop table bw_registration_tokens ##
		$this->dbforge->drop_table("bw_registration_tokens", TRUE);
		### Drop table bw_review_auth_tokens ##
		$this->dbforge->drop_table("bw_review_auth_tokens", TRUE);
		### Drop table bw_reviews ##
		$this->dbforge->drop_table("bw_reviews", TRUE);
		### Drop table bw_shipping_costs ##
		$this->dbforge->drop_table("bw_shipping_costs", TRUE);
		### Drop table bw_transactions_block_cache ##
		$this->dbforge->drop_table("bw_transactions_block_cache", TRUE);
		### Drop table bw_transactions_broadcast_cache ##
		$this->dbforge->drop_table("bw_transactions_broadcast_cache", TRUE);
		### Drop table bw_transactions_expected_cache ##
		$this->dbforge->drop_table("bw_transactions_expected_cache", TRUE);
		### Drop table bw_transactions_payments_cache ##
		$this->dbforge->drop_table("bw_transactions_payments_cache", TRUE);
		### Drop table bw_two_factor_tokens ##
		$this->dbforge->drop_table("bw_two_factor_tokens", TRUE);
		### Drop table bw_used_public_keys ##
		$this->dbforge->drop_table("bw_used_public_keys", TRUE);
		### Drop table bw_users ##
		$this->dbforge->drop_table("bw_users", TRUE);
		### Drop table bw_watched_addresses ##
		$this->dbforge->drop_table("bw_watched_addresses", TRUE);

	}
}