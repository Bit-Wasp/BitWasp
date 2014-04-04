
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `bitwasp`
--

-- --------------------------------------------------------

--
-- Table structure for table `bw_alerts`
--

CREATE TABLE IF NOT EXISTS `bw_alerts` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `source` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains a log of the alert messages.';

-- --------------------------------------------------------

--
-- Table structure for table `bw_autorun`
--

CREATE TABLE IF NOT EXISTS `bw_autorun` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `interval` varchar(8) NOT NULL,
  `interval_type` varchar(10) NOT NULL,
  `last_update` varchar(20) DEFAULT '0',
  `description` varchar(200) NOT NULL,
  `index` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index` (`index`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Table structure for table `bw_bitcoin_public_keys`
--

CREATE TABLE IF NOT EXISTS `bw_bitcoin_public_keys` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `public_key` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `bw_blocks`
--

CREATE TABLE IF NOT EXISTS `bw_blocks` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(64) NOT NULL,
  `prev_hash` varchar(64) NOT NULL,
  `height` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `hash_2` (`hash`,`height`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_captchas`
--

CREATE TABLE IF NOT EXISTS `bw_captchas` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` varchar(16) NOT NULL,
  `solution` varchar(20) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `solution` (`solution`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_categories`
--

CREATE TABLE IF NOT EXISTS `bw_categories` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `hash` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `parent_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `hash_2` (`hash`,`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `bw_config`
--

CREATE TABLE IF NOT EXISTS `bw_config` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(30) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parameter` (`parameter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bw_config`
--

INSERT INTO `bw_config` (`id`, `parameter`, `value`) VALUES
(1, 'registration_allowed', '1'),
(2, 'openssl_keysize', '2048'),
(3, 'site_description', 'open source bitcoin marketplace'),
(4, 'site_title', 'BitWasp'),
(5, 'login_timeout', '30'),
(6, 'vendor_registration_allowed', '1'),
(7, 'encrypt_private_messages', '1'),
(8, 'force_vendor_pgp', '0'),
(9, 'captcha_length', '2'),
(10, 'allow_guests', '1'),
(11, 'price_index', 'CoinDesk'),
(13, 'delete_messages_after', '0'),
(15, 'max_main_balance', '0.00000000'),
(16, 'max_fees_balance', '0.00000000'),
(17, 'electrum_mpk', ''),
(18, 'electrum_iteration', '0'),
(19, 'electrum_gap_limit', '10000'),
(20, 'delete_logs_after', '14'),
(21, 'entry_payment_vendor', '0'),
(22, 'entry_payment_buyer', '0'),
(25, 'minimum_fee', '0.0002'),
(26, 'default_rate', '0.25'),
(27, 'global_proxy_url', ''),
(28, 'global_proxy_type', 'Disabled'),
(29, 'maintenance_mode', '0'),
(30, 'settings_preserve', ''),
(31, 'autorun_preserve', ''),
(32, 'terms_of_service', ''),
(33, 'terms_of_service_toggle', '0'),
(34, 'location_list_source', 'Default'),
(35, 'escrow_rate', '1'),
(36, 'bitcoin_callback_running', 'false'),
(37, 'bitcoin_callback_starttime','100');

-- --------------------------------------------------------

--
-- Table structure for table `bw_currencies`
--

CREATE TABLE IF NOT EXISTS `bw_currencies` (
  `id` int(9) NOT NULL,
  `name` varchar(40) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `code` varchar(5) NOT NULL,
  `crypto_magic_byte` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bw_currencies`
--

INSERT INTO `bw_currencies` (`id`, `name`, `symbol`, `code`, `crypto_magic_byte`) VALUES
(0, 'Bitcoin', 'BTC', 'BTC', '00'),
(1, 'United States Dollar', '&#36;', 'USD', ''),
(2, 'British Pound Sterling', '&pound;', 'GBP', ''),
(3, 'Euro', '&euro;', 'EUR', '');

-- --------------------------------------------------------

--
-- Table structure for table `bw_disputes`
--

CREATE TABLE IF NOT EXISTS `bw_disputes` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `dispute_message` text NOT NULL,
  `disputing_user_id` int(9) NOT NULL,
  `other_user_id` int(9) NOT NULL,
  `last_update` varchar(20) NOT NULL,
  `order_id` int(9) NOT NULL,
  `final_response` enum('0','1') NOT NULL DEFAULT '0',
  `time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `order_id_2` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_disputes_updates`
--

CREATE TABLE IF NOT EXISTS `bw_disputes_updates` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `order_id` int(9) NOT NULL,
  `dispute_id` int(9) NOT NULL,
  `posting_user_id` int(9) NOT NULL,
  `message` text NOT NULL,
  `time` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`dispute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_entry_payment`
--

CREATE TABLE IF NOT EXISTS `bw_entry_payment` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(20) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `bitcoin_address` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`),
  KEY `user_hash_2` (`user_hash`,`bitcoin_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_exchange_rates`
--

CREATE TABLE IF NOT EXISTS `bw_exchange_rates` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `time` varchar(20) NOT NULL,
  `usd` decimal(10,4) NOT NULL,
  `eur` decimal(10,4) NOT NULL,
  `gbp` decimal(10,4) NOT NULL,
  `btc` int(11) NOT NULL DEFAULT '1',
  `price_index` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bw_exchange_rates`
--

INSERT INTO `bw_exchange_rates` (`id`, `time`, `usd`, `eur`, `gbp`, `btc`, `price_index`) VALUES
(1, '1396517400', 421.6251, 306.3123, 253.2567, 1, 'CoinDesk'),
(2, '1396519320', 422.1184, 306.6707, 253.5530, 1, 'CoinDesk'),
(3, '1396520220', 437.1317, 317.5407, 263.2079, 1, 'CoinDesk');

-- --------------------------------------------------------

--
-- Table structure for table `bw_fees`
--

CREATE TABLE IF NOT EXISTS `bw_fees` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `low` decimal(20,8) NOT NULL COMMENT 'Orders exceeding this value apply to this range',
  `high` decimal(20,8) NOT NULL COMMENT 'Orders less than this value apply to this range',
  `rate` decimal(4,3) NOT NULL COMMENT 'Percentage fee to be charged for this range',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `bw_fees`
--

INSERT INTO `bw_fees` (`id`, `low`, `high`, `rate`) VALUES
(1, 0.00000000, 0.50000000, 1.000),
(2, 0.50000000, 5.00000000, 0.750),
(3, 5.00000000, 500.00000000, 0.500);

-- --------------------------------------------------------

--
-- Table structure for table `bw_images`
--

CREATE TABLE IF NOT EXISTS `bw_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(20) NOT NULL,
  `encoded` longtext NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `hash_2` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `bw_images`
--

INSERT INTO `bw_images` (`id`, `hash`, `encoded`, `height`, `width`) VALUES
(1, 'default', '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', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bw_items`
--

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
  `update_time` int(20) DEFAULT '0',
  `hidden` enum('0','1') NOT NULL,
  `ship_from` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `hash_2` (`hash`,`vendor_hash`,`category`,`hidden`,`ship_from`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `bw_item_images`
--

CREATE TABLE IF NOT EXISTS `bw_item_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `image_hash` varchar(20) NOT NULL,
  `item_hash` varchar(29) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_hash` (`item_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_key_usage`
--

CREATE TABLE IF NOT EXISTS `bw_key_usage` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `usage` varchar(20) NOT NULL,
  `mpk` varchar(150) NOT NULL,
  `iteration` varchar(150) NOT NULL,
  `public_key` varchar(150) NOT NULL,
  `address` varchar(40) NOT NULL,
  `order_id` int(9) NOT NULL,
  `fees_user_hash` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_locations_custom_list`
--

CREATE TABLE IF NOT EXISTS `bw_locations_custom_list` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `location` varchar(60) NOT NULL,
  `parent_id` int(9) NOT NULL,
  `hash` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location` (`location`,`parent_id`,`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_locations_default_list`
--

CREATE TABLE IF NOT EXISTS `bw_locations_default_list` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `location` varchar(60) NOT NULL,
  `parent_id` int(9) NOT NULL,
  `hash` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `bw_locations_default_list`
--

INSERT INTO `bw_locations_default_list` (`id`, `location`, `parent_id`, `hash`) VALUES
(1, 'Afghanistan', 0, ''),
(2, 'Albania', 0, ''),
(3, 'Algeria', 0, ''),
(4, 'American Samoa', 0, ''),
(5, 'Andorra', 0, ''),
(6, 'Angola', 0, ''),
(7, 'Anguilla', 0, ''),
(8, 'Antarctica', 0, ''),
(9, 'Antigua and Barbuda', 0, ''),
(10, 'Argentina', 0, ''),
(11, 'Armenia', 0, ''),
(12, 'Aruba', 0, ''),
(13, 'Australia', 0, ''),
(14, 'Austria', 0, ''),
(15, 'Azerbaijan', 0, ''),
(16, 'Bahamas', 0, ''),
(17, 'Bahrain', 0, ''),
(18, 'Bangladesh', 0, ''),
(19, 'Barbados', 0, ''),
(20, 'Belarus', 0, ''),
(21, 'Belgium', 0, ''),
(22, 'Belize', 0, ''),
(23, 'Benin', 0, ''),
(24, 'Bermuda', 0, ''),
(25, 'Bhutan', 0, ''),
(26, 'Bolivia', 0, ''),
(27, 'Bosnia and Herzegowina', 0, ''),
(28, 'Botswana', 0, ''),
(29, 'Bouvet Island', 0, ''),
(30, 'Brazil', 0, ''),
(31, 'British Indian Ocean Territory', 0, ''),
(32, 'Brunei Darussalam', 0, ''),
(33, 'Bulgaria', 0, ''),
(34, 'Burkina Faso', 0, ''),
(35, 'Burundi', 0, ''),
(36, 'Cambodia', 0, ''),
(37, 'Cameroon', 0, ''),
(38, 'Canada', 0, ''),
(39, 'Cape Verde', 0, ''),
(40, 'Cayman Islands', 0, ''),
(41, 'Central African Republic', 0, ''),
(42, 'Chad', 0, ''),
(43, 'Chile', 0, ''),
(44, 'China', 0, ''),
(45, 'Christmas Island', 0, ''),
(46, 'Cocos (Keeling) Islands', 0, ''),
(47, 'Colombia', 0, ''),
(48, 'Comoros', 0, ''),
(49, 'Congo', 0, ''),
(50, 'Congo, the Democratic Republic of the', 0, ''),
(51, 'Cook Islands', 0, ''),
(52, 'Costa Rica', 0, ''),
(53, 'Cote d''Ivoire', 0, ''),
(54, 'Croatia (Hrvatska)', 0, ''),
(55, 'Cuba', 0, ''),
(56, 'Cyprus', 0, ''),
(57, 'Czech Republic', 0, ''),
(58, 'Denmark', 0, ''),
(59, 'Djibouti', 0, ''),
(60, 'Dominica', 0, ''),
(61, 'Dominican Republic', 0, ''),
(62, 'East Timor', 0, ''),
(63, 'Ecuador', 0, ''),
(64, 'Egypt', 0, ''),
(65, 'El Salvador', 0, ''),
(66, 'Equatorial Guinea', 0, ''),
(67, 'Eritrea', 0, ''),
(68, 'Estonia', 0, ''),
(69, 'Ethiopia', 0, ''),
(70, 'Falkland Islands (Malvinas)', 0, ''),
(71, 'Faroe Islands', 0, ''),
(72, 'Fiji', 0, ''),
(73, 'Finland', 0, ''),
(74, 'France', 0, ''),
(75, 'France Metropolitan', 0, ''),
(76, 'French Guiana', 0, ''),
(77, 'French Polynesia', 0, ''),
(78, 'French Southern Territories', 0, ''),
(79, 'Gabon', 0, ''),
(80, 'Gambia', 0, ''),
(81, 'Georgia', 0, ''),
(82, 'Germany', 0, ''),
(83, 'Ghana', 0, ''),
(84, 'Gibraltar', 0, ''),
(85, 'Greece', 0, ''),
(86, 'Greenland', 0, ''),
(87, 'Grenada', 0, ''),
(88, 'Guadeloupe', 0, ''),
(89, 'Guam', 0, ''),
(90, 'Guatemala', 0, ''),
(91, 'Guinea', 0, ''),
(92, 'Guinea-Bissau', 0, ''),
(93, 'Guyana', 0, ''),
(94, 'Haiti', 0, ''),
(95, 'Heard and Mc Donald Islands', 0, ''),
(96, 'Holy See (Vatican City State)', 0, ''),
(97, 'Honduras', 0, ''),
(98, 'Hong Kong', 0, ''),
(99, 'Hungary', 0, ''),
(100, 'Iceland', 0, ''),
(101, 'India', 0, ''),
(102, 'Indonesia', 0, ''),
(103, 'Iran (Islamic Republic of)', 0, ''),
(104, 'Iraq', 0, ''),
(105, 'Ireland', 0, ''),
(106, 'Israel', 0, ''),
(107, 'Italy', 0, ''),
(108, 'Jamaica', 0, ''),
(109, 'Japan', 0, ''),
(110, 'Jordan', 0, ''),
(111, 'Kazakhstan', 0, ''),
(112, 'Kenya', 0, ''),
(113, 'Kiribati', 0, ''),
(114, 'Korea, Democratic People''s Republic of', 0, ''),
(115, 'Korea, Republic of', 0, ''),
(116, 'Kuwait', 0, ''),
(117, 'Kyrgyzstan', 0, ''),
(118, 'Lao, People''s Democratic Republic', 0, ''),
(119, 'Latvia', 0, ''),
(120, 'Lebanon', 0, ''),
(121, 'Lesotho', 0, ''),
(122, 'Liberia', 0, ''),
(123, 'Libyan Arab Jamahiriya', 0, ''),
(124, 'Liechtenstein', 0, ''),
(125, 'Lithuania', 0, ''),
(126, 'Luxembourg', 0, ''),
(127, 'Macau', 0, ''),
(128, 'Macedonia, The Former Yugoslav Republic of', 0, ''),
(129, 'Madagascar', 0, ''),
(130, 'Malawi', 0, ''),
(131, 'Malaysia', 0, ''),
(132, 'Maldives', 0, ''),
(133, 'Mali', 0, ''),
(134, 'Malta', 0, ''),
(135, 'Marshall Islands', 0, ''),
(136, 'Martinique', 0, ''),
(137, 'Mauritania', 0, ''),
(138, 'Mauritius', 0, ''),
(139, 'Mayotte', 0, ''),
(140, 'Mexico', 0, ''),
(141, 'Micronesia, Federated States of', 0, ''),
(142, 'Moldova, Republic of', 0, ''),
(143, 'Monaco', 0, ''),
(144, 'Mongolia', 0, ''),
(145, 'Montserrat', 0, ''),
(146, 'Morocco', 0, ''),
(147, 'Mozambique', 0, ''),
(148, 'Myanmar', 0, ''),
(149, 'Namibia', 0, ''),
(150, 'Nauru', 0, ''),
(151, 'Nepal', 0, ''),
(152, 'Netherlands', 0, ''),
(153, 'Netherlands Antilles', 0, ''),
(154, 'New Caledonia', 0, ''),
(155, 'New Zealand', 0, ''),
(156, 'Nicaragua', 0, ''),
(157, 'Niger', 0, ''),
(158, 'Nigeria', 0, ''),
(159, 'Niue', 0, ''),
(160, 'Norfolk Island', 0, ''),
(161, 'Northern Mariana Islands', 0, ''),
(162, 'Norway', 0, ''),
(163, 'Oman', 0, ''),
(164, 'Pakistan', 0, ''),
(165, 'Palau', 0, ''),
(166, 'Panama', 0, ''),
(167, 'Papua New Guinea', 0, ''),
(168, 'Paraguay', 0, ''),
(169, 'Peru', 0, ''),
(170, 'Philippines', 0, ''),
(171, 'Pitcairn', 0, ''),
(172, 'Poland', 0, ''),
(173, 'Portugal', 0, ''),
(174, 'Puerto Rico', 0, ''),
(175, 'Qatar', 0, ''),
(176, 'Reunion', 0, ''),
(177, 'Romania', 0, ''),
(178, 'Russian Federation', 0, ''),
(179, 'Rwanda', 0, ''),
(180, 'Saint Kitts and Nevis', 0, ''),
(181, 'Saint Lucia', 0, ''),
(182, 'Saint Vincent and the Grenadines', 0, ''),
(183, 'Samoa', 0, ''),
(184, 'San Marino', 0, ''),
(185, 'Sao Tome and Principe', 0, ''),
(186, 'Saudi Arabia', 0, ''),
(187, 'Senegal', 0, ''),
(188, 'Seychelles', 0, ''),
(189, 'Sierra Leone', 0, ''),
(190, 'Singapore', 0, ''),
(191, 'Slovakia (Slovak Republic)', 0, ''),
(192, 'Slovenia', 0, ''),
(193, 'Solomon Islands', 0, ''),
(194, 'Somalia', 0, ''),
(195, 'South Africa', 0, ''),
(196, 'South Georgia and the South Sandwich Islands', 0, ''),
(197, 'Spain', 0, ''),
(198, 'Sri Lanka', 0, ''),
(199, 'St. Helena', 0, ''),
(200, 'St. Pierre and Miquelon', 0, ''),
(201, 'Sudan', 0, ''),
(202, 'Suriname', 0, ''),
(203, 'Svalbard and Jan Mayen Islands', 0, ''),
(204, 'Swaziland', 0, ''),
(205, 'Sweden', 0, ''),
(206, 'Switzerland', 0, ''),
(207, 'Syrian Arab Republic', 0, ''),
(208, 'Taiwan, Province of China', 0, ''),
(209, 'Tajikistan', 0, ''),
(210, 'Tanzania, United Republic of', 0, ''),
(211, 'Thailand', 0, ''),
(212, 'Togo', 0, ''),
(213, 'Tokelau', 0, ''),
(214, 'Tonga', 0, ''),
(215, 'Trinidad and Tobago', 0, ''),
(216, 'Tunisia', 0, ''),
(217, 'Turkey', 0, ''),
(218, 'Turkmenistan', 0, ''),
(219, 'Turks and Caicos Islands', 0, ''),
(220, 'Tuvalu', 0, ''),
(221, 'Uganda', 0, ''),
(222, 'Ukraine', 0, ''),
(223, 'United Arab Emirates', 0, ''),
(224, 'United Kingdom', 0, ''),
(225, 'United States', 0, ''),
(226, 'United States Minor Outlying Islands', 0, ''),
(227, 'Uruguay', 0, ''),
(228, 'Uzbekistan', 0, ''),
(229, 'Vanuatu', 0, ''),
(230, 'Venezuela', 0, ''),
(231, 'Vietnam', 0, ''),
(232, 'Virgin Islands (British)', 0, ''),
(233, 'Virgin Islands (U.S.)', 0, ''),
(234, 'Wallis and Futuna Islands', 0, ''),
(235, 'Western Sahara', 0, ''),
(236, 'Yemen', 0, ''),
(237, 'Yugoslavia', 0, ''),
(238, 'Zambia', 0, ''),
(239, 'Zimbabwe', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `bw_logs`
--

CREATE TABLE IF NOT EXISTS `bw_logs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `caller` varchar(35) NOT NULL COMMENT 'Name of the script which left the log',
  `message` varchar(250) NOT NULL COMMENT 'The message for the admins',
  `title` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `info_level` varchar(20) NOT NULL,
  `hash` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `time` (`time`,`info_level`,`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `bw_messages`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores encrypted/plaintext messages.';

-- --------------------------------------------------------

--
-- Table structure for table `bw_orders`
--

CREATE TABLE IF NOT EXISTS `bw_orders` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `created_time` varchar(20) NOT NULL,
  `currency` int(2) NOT NULL,
  `items` text NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `shipping_costs` decimal(20,8) NOT NULL,
  `fees` decimal(20,8) NOT NULL,
  `extra_fees` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `time` varchar(20) NOT NULL,
  `progress` int(1) NOT NULL,
  `buyer_id` int(9) NOT NULL,
  `vendor_hash` varchar(20) NOT NULL,
  `finalized` enum('0','1') NOT NULL,
  `confirmed_time` varchar(20) NOT NULL,
  `vendor_selected_escrow` enum('0','1') NOT NULL DEFAULT '0',
  `dispatched_time` varchar(20) NOT NULL,
  `dispatched` enum('0','1') NOT NULL DEFAULT '0',
  `disputed_time` varchar(20) NOT NULL,
  `disputed` enum('0','1') NOT NULL DEFAULT '0',
  `selected_payment_type_time` varchar(20) NOT NULL,
  `received_time` varchar(20) NOT NULL,
  `finalized_time` varchar(20) NOT NULL,
  `buyer_public_key` varchar(150) NOT NULL,
  `vendor_public_key` varchar(150) NOT NULL,
  `admin_public_key` varchar(150) NOT NULL,
  `redeemScript` varchar(500) NOT NULL,
  `address` varchar(40) NOT NULL,
  `unsigned_transaction` text NOT NULL,
  `json_inputs` text NOT NULL,
  `partially_signed_transaction` text NOT NULL,
  `partially_signing_user_id` int(9) NOT NULL,
  `final_transaction_id` varchar(64) NOT NULL,
  `paid_time` varchar(20) NOT NULL,
  `finalized_correctly` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`progress`,`created_time`,`finalized`,`buyer_id`,`vendor_hash`,`disputed`,`address`,`finalized_correctly`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `bw_page_authorization`
--

CREATE TABLE IF NOT EXISTS `bw_page_authorization` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `auth_level` varchar(15) NOT NULL,
  `system` enum('0','1') NOT NULL,
  `timeout` int(3) NOT NULL,
  `URI` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `URI` (`URI`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `bw_page_authorization`
--

INSERT INTO `bw_page_authorization` (`id`, `auth_level`, `system`, `timeout`, `URI`) VALUES
(1, 'guestonly', '1', 0, 'login'),
(2, 'guestonly', '1', 0, 'register'),
(3, 'login', '1', 0, 'inbox'),
(4, 'login', '1', 0, 'message'),
(5, 'login', '1', 0, 'account'),
(6, 'login', '1', 0, 'user'),
(7, 'auth|all', '1', 5, 'pgp'),
(8, 'login', '0', 0, 'home'),
(9, 'vendor', '1', 0, 'listings'),
(10, 'login', '1', 0, 'bitcoin'),
(11, 'admin', '1', 0, 'admin'),
(12, 'login', '', 0, 'items'),
(13, 'buyer', '0', 0, 'purchase'),
(14, 'vendor', '1', 0, 'orders'),
(15, 'buyer', '0', 0, 'purchases');

-- --------------------------------------------------------

--
-- Table structure for table `bw_paid_orders_cache`
--

CREATE TABLE IF NOT EXISTS `bw_paid_orders_cache` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_pgp_keys`
--

CREATE TABLE IF NOT EXISTS `bw_pgp_keys` (
  `id` int(9) NOT NULL,
  `fingerprint` varchar(128) NOT NULL,
  `public_key` blob NOT NULL,
  `user_id` int(9) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `bw_registration_tokens`
--

CREATE TABLE IF NOT EXISTS `bw_registration_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(100) NOT NULL,
  `user_type` enum('1','2','3') NOT NULL,
  `token_content` varchar(128) NOT NULL,
  `entry_payment` decimal(20,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `token_content` (`token_content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_reviews`
--

CREATE TABLE IF NOT EXISTS `bw_reviews` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `review_type` varchar(10) NOT NULL,
  `subject_hash` varchar(20) NOT NULL,
  `json` text NOT NULL,
  `average_rating` varchar(4) NOT NULL,
  `timestamp` varchar(20) NOT NULL,
  `disputed` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `review_type` (`review_type`,`subject_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_review_auth_tokens`
--

CREATE TABLE IF NOT EXISTS `bw_review_auth_tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `auth_token` varchar(64) NOT NULL,
  `user_hash` varchar(20) NOT NULL,
  `review_type` varchar(20) NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`auth_token`,`user_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_shipping_costs`
--

CREATE TABLE IF NOT EXISTS `bw_shipping_costs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_id` int(9) NOT NULL,
  `destination_id` varchar(10) NOT NULL,
  `cost` decimal(20,8) NOT NULL,
  `enabled` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `destination_id` (`destination_id`,`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_transactions_block_cache`
--

CREATE TABLE IF NOT EXISTS `bw_transactions_block_cache` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `tx_id` varchar(64) NOT NULL,
  `block_height` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tx_id` (`tx_id`),
  KEY `tx_id_2` (`tx_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Table structure for table `bw_transactions_expected_cache`
--

CREATE TABLE IF NOT EXISTS `bw_transactions_expected_cache` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `outputs_hash` varchar(64) NOT NULL,
  `address` varchar(50) NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_transactions_payments_cache`
--

CREATE TABLE IF NOT EXISTS `bw_transactions_payments_cache` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `block_height` int(9) DEFAULT NULL,
  `tx_id` varchar(64) DEFAULT NULL,
  `address` varchar(40) DEFAULT NULL,
  `value` decimal(20,8) DEFAULT NULL,
  `vout` int(9) DEFAULT NULL,
  `order_id` int(9) DEFAULT NULL,
  `purpose` varchar(20) NOT NULL,
  `fees_user_hash` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tx_id` (`tx_id`,`vout`,`address`,`order_id`,`fees_user_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Table structure for table `bw_two_factor_tokens`
--

CREATE TABLE IF NOT EXISTS `bw_two_factor_tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `solution` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bw_users`
--

CREATE TABLE IF NOT EXISTS `bw_users` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `banned` enum('0','1') NOT NULL DEFAULT '0',
  `block_non_pgp` enum('0','1') DEFAULT '0',
  `entry_paid` enum('0','1') DEFAULT '0',
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
  `completed_order_count` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`,`user_name`),
  KEY `user_name` (`user_name`,`user_hash`,`banned`,`entry_paid`,`register_time`,`user_role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Table structure for table `bw_watched_addresses`
--

CREATE TABLE IF NOT EXISTS `bw_watched_addresses` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `purpose` varchar(20) NOT NULL,
  `address` varchar(35) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `address` (`address`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

