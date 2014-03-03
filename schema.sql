-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Server version: 5.5.31
-- PHP Version: 5.4.4-14+deb7u4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bitwasp`
--

CREATE TABLE IF NOT EXISTS `bw_addresses` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(20) NOT NULL,
  `bitcoin_address` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_alerts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `source` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Contains a log of the alert messages.' ;

CREATE TABLE IF NOT EXISTS `bw_autorun` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `interval` varchar(8) NOT NULL,
  `interval_type` varchar(10) NOT NULL,
  `last_update` varchar(20) DEFAULT 0,
  `description` varchar(200) NOT NULL,
  `index` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index` (`index`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_blocks` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(64) NOT NULL,
  `number` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_captchas` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` varchar(16) NOT NULL,
  `solution` varchar(20) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_categories` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `hash` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `parent_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_config` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(30) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parameter` (`parameter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `bw_config` (`id`, `parameter`, `value`) VALUES
(1, 'registration_allowed', '1'),
(2, 'openssl_keysize', '2048'),
(3, 'site_description', 'open source bitcoin marketplace'),
(4, 'site_title', 'BitWasp'),
(5, 'login_timeout', '30'),
(6, 'vendor_registration_allowed', '1'),
(7, 'encrypt_private_messages', '1'),
(8, 'force_vendor_pgp', '1'),
(9, 'captcha_length', '2'),
(10, 'allow_guests', '1'),
(11, 'price_index', 'CoinDesk'),
(12, 'refund_after_inactivity', '45'),
(13, 'delete_messages_after', '45'),
(14, 'delete_transactions_after', '0'),
(15, 'max_main_balance', '0.00000000'),
(16, 'max_fees_balance', '0.00000000'),
(17, 'electrum_mpk', ''),
(18, 'electrum_iteration', ''),
(19, 'electrum_gap_limit', ''),
(20, 'delete_logs_after', '14'),
(21, 'entry_payment_vendor', '0.00000000'),
(22, 'entry_payment_buyer', '0.00000000'),
(23, 'auto_finalize_threshold', '0'),
(24, 'balance_backup_method', 'Disabled'),
(25, 'minimum_fee', '0.0003'),
(26, 'default_rate', '0.25'),
(27, 'global_proxy_url', ''),
(28, 'global_proxy_type', 'Disabled'),
(29, 'maintenance_mode', '0'),
(30, 'settings_preserve', ''),
(31, 'autorun_preserve', ''),
(32, 'terms_of_service', ''),
(33, 'terms_of_service_toggle', '0'),
(34, 'location_list_source', 'Default');

CREATE TABLE IF NOT EXISTS `bw_currencies` (
  `id` int(9) NOT NULL,
  `name` varchar(40) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `code` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `bw_currencies` (`id`, `name`, `symbol`, `code`) VALUES
(0, 'Bitcoin', 'BTC', 'BTC'),
(1, 'United States Dollar', '&#36;', 'USD'),
(2, 'British Pound Sterling', '&pound;', 'GBP'),
(3, 'Euro', '&euro;', 'EUR');

CREATE TABLE IF NOT EXISTS `bw_disputes` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `dispute_message` text NOT NULL,
  `admin_message` text NOT NULL,
  `disputing_user_id` int(9) NOT NULL,
  `other_user_id` int(9) NOT NULL,
  `last_update` varchar(20) NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bw_escrow` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(9) NOT NULL,
  `vendor_id` int(9) NOT NULL,
  `order_id` int(9) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `fee` decimal(20,8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_entry_payment` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(20) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `bitcoin_address` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_exchange_rates` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `time` varchar(20) NOT NULL,
  `usd` decimal(10,4) NOT NULL,
  `eur` decimal(10,4) NOT NULL,
  `gbp` decimal(10,4) NOT NULL,
  `btc` int(11) NOT NULL DEFAULT '1',
  `price_index` varchar(45),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `bw_exchange_rates` (`id`, `time`, `usd`, `eur`, `gbp`, `btc`, `price_index`) VALUES
(1, '1380761400', 101.7737, 74.9879, 62.8264, 1, 'CoinDesk');

CREATE TABLE IF NOT EXISTS `bw_fees` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `low` decimal(20,8) NOT NULL COMMENT 'Orders exceeding this value apply to this range',
  `high` decimal(20,8) NOT NULL COMMENT 'Orders less than this value apply to this range',
  `rate` decimal(4,3) NOT NULL COMMENT 'Percentage fee to be charged for this range',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_logs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `caller` varchar(35) NOT NULL COMMENT 'Name of the script which left the log',
  `message` varchar(250) NOT NULL COMMENT 'The message for the admins',
  `title` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `info_level` varchar(20) NOT NULL,
  `hash` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bw_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(20) NOT NULL,
  `encoded` longtext NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `bw_images` (`id`, `hash`, `encoded`, `height`, `width`) VALUES
(0, 'default', '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', 0, 0);

CREATE TABLE IF NOT EXISTS `bw_items` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(20) NOT NULL,
  `vendor_hash` varchar(20) NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `currency` int(5) NOT NULL,
  `category` int(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` blob NOT NULL,
  `main_image` varchar(20) NOT NULL,
  `add_time` int(20) NOT NULL,
  `update_time` int(20) DEFAULT 0,
  `hidden` enum('0','1') NOT NULL,
  `ship_from` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_item_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `image_hash` varchar(20) NOT NULL,
  `item_hash` varchar(29) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `bw_locations_custom_list` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `location` varchar(60) NOT NULL,
  `parent_id` int(9) NOT NULL,
  `hash` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;



CREATE TABLE IF NOT EXISTS `bw_locations_default_list` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `location` varchar(60) NOT NULL,
  `parent_id` int(9) NOT NULL,
  `hash` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `bw_locations_default_list` (`location`, `parent_id`, `hash`) VALUES
('Afghanistan', 0, ''),
('Albania', 0, ''),
('Algeria', 0, ''),
('American Samoa', 0, ''),
('Andorra', 0, ''),
('Angola', 0, ''),
('Anguilla', 0, ''),
('Antarctica', 0, ''),
('Antigua and Barbuda', 0, ''),
( 'Argentina', 0, ''),
( 'Armenia', 0, ''),
( 'Aruba', 0, ''),
( 'Australia', 0, ''),
( 'Austria', 0, ''),
( 'Azerbaijan', 0, ''),
( 'Bahamas', 0, ''),
( 'Bahrain', 0, ''),
( 'Bangladesh', 0, ''),
( 'Barbados', 0, ''),
( 'Belarus', 0, ''),
( 'Belgium', 0, ''),
( 'Belize', 0, ''),
( 'Benin', 0, ''),
( 'Bermuda', 0, ''),
( 'Bhutan', 0, ''),
( 'Bolivia', 0, ''),
( 'Bosnia and Herzegowina', 0, ''),
( 'Botswana', 0, ''),
( 'Bouvet Island', 0, ''),
( 'Brazil', 0, ''),
( 'British Indian Ocean Territory', 0, ''),
( 'Brunei Darussalam', 0, ''),
( 'Bulgaria', 0, ''),
( 'Burkina Faso', 0, ''),
( 'Burundi', 0, ''),
( 'Cambodia', 0, ''),
( 'Cameroon', 0, ''),
( 'Canada', 0, ''),
( 'Cape Verde', 0, ''),
( 'Cayman Islands', 0, ''),
( 'Central African Republic', 0, ''),
( 'Chad', 0, ''),
( 'Chile', 0, ''),
( 'China', 0, ''),
( 'Christmas Island', 0, ''),
( 'Cocos (Keeling) Islands', 0, ''),
( 'Colombia', 0, ''),
( 'Comoros', 0, ''),
( 'Congo', 0, ''),
( 'Congo, the Democratic Republic of the', 0, ''),
( 'Cook Islands', 0, ''),
( 'Costa Rica', 0, ''),
( 'Cote d''Ivoire', 0, ''),
( 'Croatia (Hrvatska)', 0, ''),
( 'Cuba', 0, ''),
( 'Cyprus', 0, ''),
( 'Czech Republic', 0, ''),
( 'Denmark', 0, ''),
( 'Djibouti', 0, ''),
( 'Dominica', 0, ''),
( 'Dominican Republic', 0, ''),
( 'East Timor', 0, ''),
( 'Ecuador', 0, ''),
( 'Egypt', 0, ''),
( 'El Salvador', 0, ''),
( 'Equatorial Guinea', 0, ''),
( 'Eritrea', 0, ''),
( 'Estonia', 0, ''),
( 'Ethiopia', 0, ''),
( 'Falkland Islands (Malvinas)', 0, ''),
( 'Faroe Islands', 0, ''),
( 'Fiji', 0, ''),
( 'Finland', 0, ''),
( 'France', 0, ''),
( 'France Metropolitan', 0, ''),
( 'French Guiana', 0, ''),
( 'French Polynesia', 0, ''),
( 'French Southern Territories', 0, ''),
( 'Gabon', 0, ''),
( 'Gambia', 0, ''),
( 'Georgia', 0, ''),
( 'Germany', 0, ''),
( 'Ghana', 0, ''),
( 'Gibraltar', 0, ''),
( 'Greece', 0, ''),
( 'Greenland', 0, ''),
( 'Grenada', 0, ''),
( 'Guadeloupe', 0, ''),
( 'Guam', 0, ''),
( 'Guatemala', 0, ''),
( 'Guinea', 0, ''),
( 'Guinea-Bissau', 0, ''),
( 'Guyana', 0, ''),
( 'Haiti', 0, ''),
( 'Heard and Mc Donald Islands', 0, ''),
( 'Holy See (Vatican City State)', 0, ''),
( 'Honduras', 0, ''),
( 'Hong Kong', 0, ''),
( 'Hungary', 0, ''),
( 'Iceland', 0, ''),
( 'India', 0, ''),
( 'Indonesia', 0, ''),
( 'Iran (Islamic Republic of)', 0, ''),
( 'Iraq', 0, ''),
( 'Ireland', 0, ''),
( 'Israel', 0, ''),
( 'Italy', 0, ''),
( 'Jamaica', 0, ''),
( 'Japan', 0, ''),
( 'Jordan', 0, ''),
( 'Kazakhstan', 0, ''),
( 'Kenya', 0, ''),
( 'Kiribati', 0, ''),
( 'Korea, Democratic People''s Republic of', 0, ''),
( 'Korea, Republic of', 0, ''),
( 'Kuwait', 0, ''),
( 'Kyrgyzstan', 0, ''),
( 'Lao, People''s Democratic Republic', 0, ''),
( 'Latvia', 0, ''),
( 'Lebanon', 0, ''),
( 'Lesotho', 0, ''),
( 'Liberia', 0, ''),
( 'Libyan Arab Jamahiriya', 0, ''),
( 'Liechtenstein', 0, ''),
( 'Lithuania', 0, ''),
( 'Luxembourg', 0, ''),
( 'Macau', 0, ''),
( 'Macedonia, The Former Yugoslav Republic of', 0, ''),
( 'Madagascar', 0, ''),
( 'Malawi', 0, ''),
( 'Malaysia', 0, ''),
( 'Maldives', 0, ''),
( 'Mali', 0, ''),
( 'Malta', 0, ''),
( 'Marshall Islands', 0, ''),
( 'Martinique', 0, ''),
( 'Mauritania', 0, ''),
( 'Mauritius', 0, ''),
( 'Mayotte', 0, ''),
( 'Mexico', 0, ''),
( 'Micronesia, Federated States of', 0, ''),
( 'Moldova, Republic of', 0, ''),
( 'Monaco', 0, ''),
( 'Mongolia', 0, ''),
( 'Montserrat', 0, ''),
( 'Morocco', 0, ''),
( 'Mozambique', 0, ''),
( 'Myanmar', 0, ''),
( 'Namibia', 0, ''),
( 'Nauru', 0, ''),
( 'Nepal', 0, ''),
( 'Netherlands', 0, ''),
( 'Netherlands Antilles', 0, ''),
( 'New Caledonia', 0, ''),
( 'New Zealand', 0, ''),
( 'Nicaragua', 0, ''),
( 'Niger', 0, ''),
( 'Nigeria', 0, ''),
( 'Niue', 0, ''),
( 'Norfolk Island', 0, ''),
( 'Northern Mariana Islands', 0, ''),
( 'Norway', 0, ''),
( 'Oman', 0, ''),
( 'Pakistan', 0, ''),
( 'Palau', 0, ''),
('Panama', 0, ''),
( 'Papua New Guinea', 0, ''),
( 'Paraguay', 0, ''),
( 'Peru', 0, ''),
( 'Philippines', 0, ''),
( 'Pitcairn', 0, ''),
('Poland', 0, ''),
( 'Portugal', 0, ''),
( 'Puerto Rico', 0, ''),
( 'Qatar', 0, ''),
( 'Reunion', 0, ''),
( 'Romania', 0, ''),
( 'Russian Federation', 0, ''),
( 'Rwanda', 0, ''),
( 'Saint Kitts and Nevis', 0, ''),
( 'Saint Lucia', 0, ''),
( 'Saint Vincent and the Grenadines', 0, ''),
( 'Samoa', 0, ''),
( 'San Marino', 0, ''),
( 'Sao Tome and Principe', 0, ''),
( 'Saudi Arabia', 0, ''),
( 'Senegal', 0, ''),
( 'Seychelles', 0, ''),
( 'Sierra Leone', 0, ''),
( 'Singapore', 0, ''),
( 'Slovakia (Slovak Republic)', 0, ''),
( 'Slovenia', 0, ''),
( 'Solomon Islands', 0, ''),
( 'Somalia', 0, ''),
( 'South Africa', 0, ''),
( 'South Georgia and the South Sandwich Islands', 0, ''),
( 'Spain', 0, ''),
( 'Sri Lanka', 0, ''),
( 'St. Helena', 0, ''),
( 'St. Pierre and Miquelon', 0, ''),
( 'Sudan', 0, ''),
( 'Suriname', 0, ''),
( 'Svalbard and Jan Mayen Islands', 0, ''),
( 'Swaziland', 0, ''),
( 'Sweden', 0, ''),
( 'Switzerland', 0, ''),
( 'Syrian Arab Republic', 0, ''),
( 'Taiwan, Province of China', 0, ''),
( 'Tajikistan', 0, ''),
( 'Tanzania, United Republic of', 0, ''),
( 'Thailand', 0, ''),
( 'Togo', 0, ''),
( 'Tokelau', 0, ''),
( 'Tonga', 0, ''),
( 'Trinidad and Tobago', 0, ''),
( 'Tunisia', 0, ''),
( 'Turkey', 0, ''),
( 'Turkmenistan', 0, ''),
( 'Turks and Caicos Islands', 0, ''),
( 'Tuvalu', 0, ''),
( 'Uganda', 0, ''),
( 'Ukraine', 0, ''),
( 'United Arab Emirates', 0, ''),
( 'United Kingdom', 0, ''),
( 'United States', 0, ''),
( 'United States Minor Outlying Islands', 0, ''),
( 'Uruguay', 0, ''),
('Uzbekistan', 0, ''),
( 'Vanuatu', 0, ''),
( 'Venezuela', 0, ''),
( 'Vietnam', 0, ''),
( 'Virgin Islands (British)', 0, ''),
( 'Virgin Islands (U.S.)', 0, ''),
( 'Wallis and Futuna Islands', 0, ''),
('Western Sahara', 0, ''),
( 'Yemen', 0, ''),
('Yugoslavia', 0, ''),
( 'Zambia', 0, ''),
('Zimbabwe', 0, '');


CREATE TABLE IF NOT EXISTS `bw_messages` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `content` blob NOT NULL,
  `encrypted` enum('0','1') NOT NULL,
  `hash` varchar(16) NOT NULL,
  `remove_on_read` enum('0','1') NOT NULL,
  `time` int(20) NOT NULL,
  `to` int(9) NOT NULL,
  `viewed` enum('0','1') NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores encrypted/plaintext messages.' ;

CREATE TABLE IF NOT EXISTS `bw_orders` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `created_time` varchar(20) NOT NULL,
  `currency` int(2) NOT NULL,
  `items` text NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `fees` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `progress` int(1) NOT NULL,
  `buyer_id` int(9) NOT NULL,
  `vendor_hash` varchar(20) NOT NULL,
  `finalized` enum('0','1') NOT NULL,
  `confirmed_time` varchar(20) NOT NULL,
  `selected_escrow` enum('0','1') NOT NULL DEFAULT '0',
  `dispatched_time` varchar(20) NOT NULL,
  `disputed_time` varchar(20) NOT NULL,
  `disputed` enum('0','1') NOT NULL DEFAULT '0',
  `selected_payment_type_time` varchar(20) NOT NULL,
  `received_time` varchar(20) NOT NULL,
  `finalized_time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_page_authorization` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `auth_level` varchar(15) NOT NULL,
  `system` enum('0','1') NOT NULL,
  `timeout` int(3) NOT NULL,
  `URI` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `bw_page_authorization` (`id`, `URI`, `auth_level`, `timeout`, `system`) VALUES
(1, 'login', 'guestonly', 0, '1'),
(2, 'register', 'guestonly', 0, '1'),
(3, 'inbox', 'login', 0, '1'),
(4, 'message', 'login', 0, '1'),
(5, 'account', 'login', 0, '1'),
(6, 'user', 'login', 0, '1'),
(7, 'pgp', 'auth|all', 5, '1'),
(8, 'home', 'login', 0, '0'),
(9, 'listings', 'vendor', 0, '1'),
(10, 'bitcoin', 'login', 0, '1'),
(11, 'admin', 'admin', 0, '1'),
(12, 'items', 'login', 0, ''),
(13, 'order', 'buyer', 0, '0'),
(14, 'orders', 'vendor', 0, '1'),
(15, '', 'login', 0, '');

CREATE TABLE IF NOT EXISTS `bw_pending_txns` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `confirmations` varchar(6) NOT NULL,
  `credited` enum('0','1') NOT NULL,
  `category` varchar(16) NOT NULL,
  `address` varchar(40) NOT NULL,
  `time` varchar(20) NOT NULL,
  `txn_id` varchar(64) NOT NULL,
  `user_hash` varchar(20) NOT NULL,
  `value` decimal(10,8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_pgp_keys` (
  `id` int(9) NOT NULL,
  `fingerprint` varchar(128) NOT NULL,
  `public_key` blob NOT NULL,
  `user_id` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bw_registration_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(100) NOT NULL,
  `user_type` enum('1','2','3') NOT NULL,
  `token_content` varchar(128) NOT NULL,
  `entry_payment` decimal(20,8),
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_review_auth_tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `auth_token` varchar(64) NOT NULL,
  `user_hash` varchar(20) NOT NULL,
  `review_type` varchar(20) NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_reviews` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `review_type` varchar(10) NOT NULL,
  `subject_hash` varchar(20) NOT NULL,
  `json` text NOT NULL,
  `average_rating` varchar(4) NOT NULL,
  `timestamp` varchar(20) NOT NULL,
  `disputed` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_shipping_costs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) NOT NULL,
  `destination_id` varchar(10) NOT NULL,
  `cost` decimal(20,8) NOT NULL,
  `enabled` enum('0','1'),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bw_two_factor_tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `solution` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `bw_users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `banned` enum('0','1') NOT NULL DEFAULT '0',
  `bitcoin_cashout_address` varchar(40) NOT NULL,
  `bitcoin_topup_address` varchar(40) NOT NULL,
  `bitcoin_balance` decimal(10,8) NOT NULL,
  `block_non_pgp` enum('0','1') DEFAULT '0',
  `entry_paid` enum('0','1') default '0',
  `force_pgp_messages` enum('0','1') NOT NULL,
  `location` int(3) NOT NULL,
  `login_time` int(20) NOT NULL,
  `display_login_time` enum('0','1') NOT NULL,
  `password` varchar(128) NOT NULL,
  `public_key` blob NOT NULL,
  `private_key` blob NOT NULL,
  `register_time` int(20) NOT NULL,
  `salt` varchar(128) NOT NULL,
  `two_factor_auth` enum('0','1') NOT NULL,
  `user_hash` varchar(16) NOT NULL,
  `user_name` varchar(40) NOT NULL,
  `user_role` enum('Buyer','Vendor','Admin') NOT NULL,
  `local_currency` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`,`user_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
