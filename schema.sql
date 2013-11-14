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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_blocks` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(64) NOT NULL,
  `number` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_captchas` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `key` varchar(16) NOT NULL,
  `solution` varchar(20) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_categories` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `hash` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `parent_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_config` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(30) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parameter` (`parameter`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

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
(26, 'default_rate', '0.25');

CREATE TABLE IF NOT EXISTS `bw_country_codes` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `country` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

INSERT INTO `bw_country_codes` (`id`, `country`) VALUES
(1, 'Undeclared'),
(2, 'Afghanistan'),
(3, 'Albania'),
(4, 'Algeria'),
(5, 'American Samoa'),
(6, 'Andorra'),
(7, 'Angola'),
(8, 'Anguilla'),
(9, 'Antarctica'),
(10, 'Antigua and Barbuda'),
(11, 'Argentina'),
(12, 'Armenia'),
(13, 'Aruba'),
(14, 'Australia'),
(15, 'Austria'),
(16, 'Azerbaijan'),
(17, 'Bahamas'),
(18, 'Bahrain'),
(19, 'Bangladesh'),
(20, 'Barbados'),
(21, 'Belarus'),
(22, 'Belgium'),
(23, 'Belize'),
(24, 'Benin'),
(25, 'Bermuda'),
(26, 'Bhutan'),
(27, 'Bolivia'),
(28, 'Bosnia and Herzegowina'),
(29, 'Botswana'),
(30, 'Bouvet Island'),
(31, 'Brazil'),
(32, 'British Indian Ocean Territory'),
(33, 'Brunei Darussalam'),
(34, 'Bulgaria'),
(35, 'Burkina Faso'),
(36, 'Burundi'),
(37, 'Cambodia'),
(38, 'Cameroon'),
(39, 'Canada'),
(40, 'Cape Verde'),
(41, 'Cayman Islands'),
(42, 'Central African Republic'),
(43, 'Chad'),
(44, 'Chile'),
(45, 'China'),
(46, 'Christmas Island'),
(47, 'Cocos (Keeling) Islands'),
(48, 'Colombia'),
(49, 'Comoros'),
(50, 'Congo'),
(51, 'Congo, the Democratic Republic of the'),
(52, 'Cook Islands'),
(53, 'Costa Rica'),
(54, 'Cote d''Ivoire'),
(55, 'Croatia (Hrvatska)'),
(56, 'Cuba'),
(57, 'Cyprus'),
(58, 'Czech Republic'),
(59, 'Denmark'),
(60, 'Djibouti'),
(61, 'Dominica'),
(62, 'Dominican Republic'),
(63, 'East Timor'),
(64, 'Ecuador'),
(65, 'Egypt'),
(66, 'El Salvador'),
(67, 'Equatorial Guinea'),
(68, 'Eritrea'),
(69, 'Estonia'),
(70, 'Ethiopia'),
(71, 'Falkland Islands (Malvinas)'),
(72, 'Faroe Islands'),
(73, 'Fiji'),
(74, 'Finland'),
(75, 'France'),
(76, 'France Metropolitan'),
(77, 'French Guiana'),
(78, 'French Polynesia'),
(79, 'French Southern Territories'),
(80, 'Gabon'),
(81, 'Gambia'),
(82, 'Georgia'),
(83, 'Germany'),
(84, 'Ghana'),
(85, 'Gibraltar'),
(86, 'Greece'),
(87, 'Greenland'),
(88, 'Grenada'),
(89, 'Guadeloupe'),
(90, 'Guam'),
(91, 'Guatemala'),
(92, 'Guinea'),
(93, 'Guinea-Bissau'),
(94, 'Guyana'),
(95, 'Haiti'),
(96, 'Heard and Mc Donald Islands'),
(97, 'Holy See (Vatican City State)'),
(98, 'Honduras'),
(99, 'Hong Kong'),
(100, 'Hungary'),
(101, 'Iceland'),
(102, 'India'),
(103, 'Indonesia'),
(104, 'Iran (Islamic Republic of)'),
(105, 'Iraq'),
(106, 'Ireland'),
(107, 'Israel'),
(108, 'Italy'),
(109, 'Jamaica'),
(110, 'Japan'),
(111, 'Jordan'),
(112, 'Kazakhstan'),
(113, 'Kenya'),
(114, 'Kiribati'),
(115, 'Korea, Democratic People''s Republic of'),
(116, 'Korea, Republic of'),
(117, 'Kuwait'),
(118, 'Kyrgyzstan'),
(119, 'Lao, People''s Democratic Republic'),
(120, 'Latvia'),
(121, 'Lebanon'),
(122, 'Lesotho'),
(123, 'Liberia'),
(124, 'Libyan Arab Jamahiriya'),
(125, 'Liechtenstein'),
(126, 'Lithuania'),
(127, 'Luxembourg'),
(128, 'Macau'),
(129, 'Macedonia, The Former Yugoslav Republic of'),
(130, 'Madagascar'),
(131, 'Malawi'),
(132, 'Malaysia'),
(133, 'Maldives'),
(134, 'Mali'),
(135, 'Malta'),
(136, 'Marshall Islands'),
(137, 'Martinique'),
(138, 'Mauritania'),
(139, 'Mauritius'),
(140, 'Mayotte'),
(141, 'Mexico'),
(142, 'Micronesia, Federated States of'),
(143, 'Moldova, Republic of'),
(144, 'Monaco'),
(145, 'Mongolia'),
(146, 'Montserrat'),
(147, 'Morocco'),
(148, 'Mozambique'),
(149, 'Myanmar'),
(150, 'Namibia'),
(151, 'Nauru'),
(152, 'Nepal'),
(153, 'Netherlands'),
(154, 'Netherlands Antilles'),
(155, 'New Caledonia'),
(156, 'New Zealand'),
(157, 'Nicaragua'),
(158, 'Niger'),
(159, 'Nigeria'),
(160, 'Niue'),
(161, 'Norfolk Island'),
(162, 'Northern Mariana Islands'),
(163, 'Norway'),
(164, 'Oman'),
(165, 'Pakistan'),
(166, 'Palau'),
(167, 'Panama'),
(168, 'Papua New Guinea'),
(169, 'Paraguay'),
(170, 'Peru'),
(171, 'Philippines'),
(172, 'Pitcairn'),
(173, 'Poland'),
(174, 'Portugal'),
(175, 'Puerto Rico'),
(176, 'Qatar'),
(177, 'Reunion'),
(178, 'Romania'),
(179, 'Russian Federation'),
(180, 'Rwanda'),
(181, 'Saint Kitts and Nevis'),
(182, 'Saint Lucia'),
(183, 'Saint Vincent and the Grenadines'),
(184, 'Samoa'),
(185, 'San Marino'),
(186, 'Sao Tome and Principe'),
(187, 'Saudi Arabia'),
(188, 'Senegal'),
(189, 'Seychelles'),
(190, 'Sierra Leone'),
(191, 'Singapore'),
(192, 'Slovakia (Slovak Republic)'),
(193, 'Slovenia'),
(194, 'Solomon Islands'),
(195, 'Somalia'),
(196, 'South Africa'),
(197, 'South Georgia and the South Sandwich Islands'),
(198, 'Spain'),
(199, 'Sri Lanka'),
(200, 'St. Helena'),
(201, 'St. Pierre and Miquelon'),
(202, 'Sudan'),
(203, 'Suriname'),
(204, 'Svalbard and Jan Mayen Islands'),
(205, 'Swaziland'),
(206, 'Sweden'),
(207, 'Switzerland'),
(208, 'Syrian Arab Republic'),
(209, 'Taiwan, Province of China'),
(210, 'Tajikistan'),
(211, 'Tanzania, United Republic of'),
(212, 'Thailand'),
(213, 'Togo'),
(214, 'Tokelau'),
(215, 'Tonga'),
(216, 'Trinidad and Tobago'),
(217, 'Tunisia'),
(218, 'Turkey'),
(219, 'Turkmenistan'),
(220, 'Turks and Caicos Islands'),
(221, 'Tuvalu'),
(222, 'Uganda'),
(223, 'Ukraine'),
(224, 'United Arab Emirates'),
(225, 'United Kingdom'),
(226, 'United States'),
(227, 'United States Minor Outlying Islands'),
(228, 'Uruguay'),
(229, 'Uzbekistan'),
(230, 'Vanuatu'),
(231, 'Venezuela'),
(232, 'Vietnam'),
(233, 'Virgin Islands (British)'),
(234, 'Virgin Islands (U.S.)'),
(235, 'Wallis and Futuna Islands'),
(236, 'Western Sahara'),
(237, 'Yemen'),
(238, 'Yugoslavia'),
(239, 'Zambia'),
(240, 'Zimbabwe');

CREATE TABLE IF NOT EXISTS `bw_currencies` (
  `id` int(9) NOT NULL,
  `name` varchar(40) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `code` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `last_update` varchar(20) NOT NULL,
  `order_id` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bw_escrow` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(9) NOT NULL,
  `vendor_id` int(9) NOT NULL,
  `order_id` int(9) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_entry_payment` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(20) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `bitcoin_address` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_hash` (`user_hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_exchange_rates` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `time` varchar(20) NOT NULL,
  `usd` decimal(10,4) NOT NULL,
  `eur` decimal(10,4) NOT NULL,
  `gbp` decimal(10,4) NOT NULL,
  `btc` int(11) NOT NULL DEFAULT '1',
  `price_index` varchar(45),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

INSERT INTO `bw_exchange_rates` (`id`, `time`, `usd`, `eur`, `gbp`, `btc`, `price_index`) VALUES
(1, '1380761400', 101.7737, 74.9879, 62.8264, 1, 'CoinDesk');

CREATE TABLE IF NOT EXISTS `bw_fees` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `low` decimal(20,8) NOT NULL COMMENT 'Orders exceeding this value apply to this range',
  `high` decimal(20,8) NOT NULL COMMENT 'Orders less than this value apply to this range',
  `rate` decimal(4,3) NOT NULL COMMENT 'Percentage fee to be charged for this range',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bw_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `hash` varchar(20) NOT NULL,
  `encoded` longtext NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

INSERT INTO `bw_images` (`id`, `hash`, `encoded`, `height`, `width`) VALUES
(0, 'default', 'iVBORw0KGgoAAAANSUhEUgAAAMgAAACWCAIAAAAUvlBOAAAYDElEQVR4nO2deXwURdrHn56ZZCaZTBJCTDAQYrivoICEQ4ggiAiiAuoqLJcKKAjiK+LyAXRdxfWjgiJyCLwovq6Ici0r7hsRIqfGYFjOcAjBhEPuXJMDMtPvHz1TU11d3dPdM8n0O6nfH3zmW9Vd59NPPV1dRo7neVAhnuc5jkM/CKReyTDMkDr1csi53W7CMqhSrjsoHTCI0JOGxoshkmA6atAEqsV7pYCoaK2ICgk5chyHjylDfZ7CJOczCFNFIq6X3q58gRpEiaHCYHUknFDrLSbi2VW4B08hEL8eWbomxJuBHGEIUWgSQ4RoolUiJ3czMf1o7jlvgCa9BrcwrSjnOEMlfaMZrgjaJ9QCmN0QF+ElUhN110pFgwwi9YFp4IhLpSVYCPZruXitcik6EK/IIIPIEP3WsxSq3G5QWSLu9rSimjbUgwLvSFgiGh+VKErFR1YO60GhXRN1DGLYox6PxfO81HQUbpYuH8ZxNkx1JGRtyI35RROI7RG/jop+W4Bfrwn9Vl0/GEgXwhU5r8DrSvwj733pC4rjUeknqchkWOGmpnL1NKGlDWUH4rEIQ1GP+IPCexUSRF1miCPuk9Sg75MO/gONtRTRzVTh86QJ1S/edY0KfW/gyGtZfzwbpEDbnVIpvETdHstoaAQTNw4iqbctE++NthT8EGhxNrj/1Id4LaFC3ZFiWKIgNNFqkLIUUqvBUwgE8cQE6HvxloQKiQFhqMPJiT7pKJgtNbGOkGhMPSPeJIa4tPkItKhx2LEFdBFCvCYpShuhVajMkDt/I7TBaKhjCaLvvFOl8LhrcpJUxC24jkZHJTIFRaINUuWVAiVSLTpYFsbQmKhVFrwU5WiOmhiUdRAME0kYxGsaEJWdjhQp/zGF3NALiyaeTqyG1FyViNpErIkhQdyLMxQmi+M4znteWRWiX8rGq1KBPx94f0KCwepIOKEOWUDysOLLotYKlBdTBZSrt/4RbyFD4YeO51PWY+mWQR6yABFCt5FmZJSamhyyYzNM/oV7Mk78uiaH7NgMiajLDHHkOA53S36RHZshUaHvDRx5LesPOzZDRyOYuHEQSb1tsWMzdNQdKYYlCkITrQbZsRkKEgPCUIeTY8dmSMSbxBCXNh9BLEZyVxM/pOkBCi8Q7xhDg6DC7FNRtEEqlOX3gSZcHy4dPjOIy2hQ0DhzaRzksRAKWY8ykhukcpYITExa5Dnd4Nd5KAu/UnCBulGuWIYhROR9kPyiBS9C+TVBjYwzFrqRSSqVy58I3W43iJdShXtAfg0mGqEV6+LtUh8G3pewRK3Rc3De6YKo0FqVcdpgNNRqjibwGlqwLEz3MoSW59AiGMNDGA05jhOsTS0iq2KvfkwK4jVuVXD484qHVniJ9WBzqGUhf0bRM4c3qYEjaI+xLCgVH2XA7MmvVQXF8ogOgPZHJFiIN4kh+q3VKGX/lyfqbYVwb7qDGyO4K6ZgyfdXk3HHI10NQcsTr7yY+l1qA7k9WGgQ92kcxI1EDYoeVoNMgxGsKuRtMBpqXQothLmhskCLiwq8D7ixa+1D0FHHIIQ36phT33aDkMrJxB/KswIBi82rwVHrI+rZbsDtQ7kOlEK1JyJLE+IdCCEaZy6Ng9SFRRnJDVLl1YGJSaXYsRmG/hF5HyS/yI7N0Nd0Jlwqlz8RsmMzBAbel7BErdGz4TadQ2tVxmmD0VCrObJjMySCMTyE0ZDjOMHa1CKyKvbqx6QgXuNWBYc/r3hohZeo1eZc5WfWLfnvnNOXAcBijx8yfubgjKQIM7UQ/sqx7cs/XVdUAoldH3pl3NBGUWaUV0dP4cW8rxZ+saO0CpL6jHlrbF8iFz1zaLAYgo4dSl5GbrdbLsvvlYXbF3VL9dmOvVnPxdt+k7m75sDX89rHAwCkj5hXdKNGrtgg4tG101JiAQBg8BvU9tdd1f9/0e12ox9qsG6PzQhynvt52gtzT1VQgxulYvk6cFd+K2IKikxoTIkpxy+iGQQdURDnUebgZ7u3AAA48fW4FxecqeDwWSSuldau2f2qRkLSi4lOMRRmlsc/2iiiBc0tSsIHWiUK7aCYiSVt/PxRxaOfy75Qmbfx4497Z84f1zfKrBS3EVk4Fh/Mzjvt9JVts3fpMyg1VudbNLXe0j8K8vYVlFlsLbv0uTM1FgBqrpzKyT1aedMNABGOxC7dezWNs+DlXD35064jF4XfCemde2a0tFkU2uAuPX8878Dx8lsozZbetUeXOxorNxhvBk3RnbKy2iRGS++9dvZAbn5htTfdkdiqe6/OcRad2w0a4jO0oOIrK1oy1SAht9vti7G6PX343MUfl01Pj7ECQExS1ur9xeKLKTEWsWCXFh9c8+60AZmZLZOt+FiaI6wtMzIzx8zdf7C4SksogMdYRG7umheaAUDUbRMW/1h8Kn/53DFd2zeP9BqK2eZo1anbzKXfXqrg3W534b61L41/uH1qPGpSTFJan/ufXp9XVFVL1lty4fimD18amNW9U6sUmxnvR0RSeodBI8Z8sftM1S2XpM1VxQf3zx2TiTeDJuvopT/j996qunH4u1VjRgzqkJ4UgV1nczTp1C3zpcVbTl6qUB826bANwBkvSFqucsW4fIaVPuLHohvuyuLPZgwQOtY669Uj5bewa5WCd3dtTcH2VY/3ShTutUQ2bpOB1L6JwzNisYm9Xl21/XpNrcrOS4N3lLtv1aQmABDp6PrA8Kz2jQAgOiGlfceMjIyMdmneZlgTnnp1xcq3p7ROBABonNpWaFDTBMHuTY3aPfjZT2eJkr+a1dsKAMDZ429v29HbiY7tUxI8biYqufuinDOiNruqD3234N5Uoa0RTVq08/S89R0O9Ig5kjMyMjIy7l34g+/eW+WFH09/MDkKAMBqT2zdwTdqyQ5PHzo9+vLe326oNxT1Vij8C0Se3zpQitSeUBZpWG63++ovD7WxmwBM1riRc766UFHrvZg0LLy0yye2DE21C86p2yPPr/nmlxJfZtXRH76cNbpffIQZAMCe+ubW34iWyCHhsfBcj2F5LMje+8nZm3MLqm7xPM87i/ZPfahNJPa2ExnTYvjzf91bWCrcfmr7ikd6pQv5KY8vrBEP3bfzhzftPfLD5Wuy9x4Tniye5/lbVQW5a5/v01Eo8I7HFt3Ahr3ij/2TMhMBwByb+qeXlx76o0IoqurGuZyv3rinWSwAQErnzQWip9FVefHreU/EWU2myOiOj0zf8v3+6zW+3EPbl78y/B67iQOwtRr7cY0Kk0IWgpuKXwTpnW6aJWoSYVhC4qkflvRKMQEAJHSev+mQt1BZj8W7Sz+b1FUY8Z5TlxSV0moqLVoytadwTfMBb553qWoe4bFwIcMyxbcZ/db6cnHulaNbH+0s3AnmiM4f7jpdLcq/eXz9nGZxgh99IFf9sJ34PBkAAJLSR/+MVXk+b6lgcR36v3xQ0v09Cx4WWnLf69sqsfSLB9YPaRsNYOsx6p1TpTcp1V3LH99T6GXaurOaJ1elRP+TJl71KzohXvyhjZrbqu+oN2ce/NPc1deuH5ozcUJm9/0Dm4JSDb/v+uS7QgAAaDVr9pTUWFpQGZs6ZfasD5aM+A3g2pHVW399aWJ3u7RJUlRoPwAA2O4Z9txbM0bGiHMTU1pltG3z7aH9tdDkmWXLX+zbQnxvRNusgV3iPzlXehWg6MJFgBR17xMxjgSASwBud1lVFUCMJ9fldFYCAEBilz7NJd1Puj1N+FFbXlEDYPPmFvz4za4TlWCJrrpW8OnC+XiAhVTuvikMce6Bi0+kpfhtJGjf37EA7XUPT8RvkGsB/p4onT9PbkTcfdNfm7Mz97/+eRCu/vqXWX9fu2x261hKt4Vyigvyz1WWAwB0H9a/KVYOUWlK/2Hd4YM8cFbVHDlTxN/dTs2bLL2FpJnzIP6Lh1xEZCNblAWgVjzovqGXdESUy1cd2Lpuz7ETW9es3V14GbvOVS2+SZinyLjk22KhsAxOfv/ljsP3PNIp0YSKun5y6T82Czc069gmDqvoSN7OCgCorTyUveZQNmV4paMNEuuhGpM0RQEtuBmhVLx0vBp8AqQ2hG9YSMVxHG9KGfv+/L3Hxm84dfU/3yya177t8llDpf0USigvveqqrQUAaBwfj7UQNxGe5zkuPr4xea/8xZS2yef6LFJO5AjI557/ee1Lr72bvftwWbVLoUB0E8dxt7UdPGXK0OMLtv5xdNP4foeenDEtKz0OAJyXTmz4fMmOIyUAENtq7Oyn2uIV3bj+BwCAyZp+V9/M9skWaiUeJY3slSydRCkKqxtgY+UXRVsyyoOo5jK/09C4xcDX3nmxYMr8Y5cubXzvnX4DO/eUKSHSGsWZTABuOFVUDJDKiaYZ80PFRacAAGzWiBYpyZJcClLbrLCvpj6X6rE4joPSPc88Ojn7UjlYrGl39np03PSnB7T1XXclZ/jAGWck97rcNVVlVSYXAF9bfv3EytdeWIldYLFG3znsub/9ZUanKDPejKTbWwCcAbO954iZy+c8EKv9+7ECgurNQt9SqByFqJdyZMZzkZ2HTH3vlf+MnLmhuizv/Zc/emNyvAnb10HNaN7mrkZRMedLyuD03h0nK8a29gRPxLLl/HXjptMAAI6ELt3aJRBtIC6W81gKuSrXUI91yuSe2Lw8+1I5ADTpN23zP/56V5Jd1N+LZ7ANOh55ypNbP5i3ekeJ2/b0B+vG3F743qqNV8s8a2Zaz4efHjGw692ZSXZyoW/eumMUnKm6VXn22KEr5QNjHWblqEATKi8FOJqA9sgSxqEJ/cvWaMiUjyb3jTUDFO7/YtnqX5xYhOnzWK16PdFVeHk5vuDVBfkXKtyEE+JdpSd3vvL6susAALbM0RMzk+guKhCPRbRKX26VECwCWB3JTRvb8dzaqtKj+/adwy5Hvw7t2XK1GgCa9s7q1euxF7du25nr1deL5gy+t4dgVUQz7nrgz1ktIgGq87PXrFi3u6JWlOuRu7b0yrnfL5fKdYEa5ODW5hdF/8sT3OKIRFQHFfFE6SgTuTzPc1EpM99dcmLCi9nHr+/dmU29mI9Mm/S3ef8++GzuuZrDm99+7PfdD4+a+vKELO+0XN7y98Urt2746fhlMJnbD3hh/vP9I3keghljUfqrnCtdCoXcNhl9raYtNW64tG3x9Dfss8YOb94oAgAKd615f9X67Tt/KqeV3LbHoNSVv5+rPj1z6H1bB/VsHCm6wGKPHzx6Wp8WdntcY6vZ14yU9vf+efRDee9uun7t6LsThx/IeeaJJ594qHdL78PrPLhl/cZd+fk/byvJevvYJ8+oXNpAhfGRHdC0Lyq9AJeQRd3HktzozPtkRrLdF1xSdt7dN8/u/nRIepJk2EXqO/Wj/WdL/HYBof+dd7D1HbOwsMJzr697FYULx/S1AQA0mbRqH5nL8+7LOcM8G/TtN53Hc8tXTO7hkOtAUo/7eyQBQGLasJzLvjbztRU7ljyfalPuPQyY9tGeU9fFs3Bl09sT0/zcB71f+ZrSBZkzM36vIZDyZ4xA4qj82KbkRpstJiLCClAD9ugYs4nI9VL03ZPe/PzmzWlzl54sBQBHempqlJU45WdJ6zN+65nx21e/+v2+ghXrsksqbqIL4rsOmzSw36AJowa0awKKm3AEIjWOdRC5kTGOKEmPfBAREWMTfegjRyYqOs5kpuXGTFz+Q+aDG77YumHtyn+d96a26vfUiGEPTBg1cu/rmdtyL4uK4rj8zW9Nn7usuBqsjVMHDxvRNkn0qbS8+Jcv1/5YCrB98fT8/IJdG9/rlITC0MRHZ6+49/FxX3yz5Zdd2Zv+96Dv0z047howpH+3tnf2HjHykTspXVA918ry/bUZEC8KPO3zNaqJlwS8eIq7tqaiwnnLxYPFFu+INptkb+fdtc7yspsuADDZ7DHRtgiQCwxdNaVlTpfbZx9mW0xcdKTWoBIAXDXOMme1mwezzR5vt+G5tTerKsorXcBFWKPs9ijJoVd3jdPprL7Jg8lmj7HbpLuPLmdJWbXLDWB2NIqPlJx2c7tqyktQ2AOR0bGOqAgAqHaWOKtdJnNkTKwjwgQAUPXbP4fePz7nbAk06/avf++4v12M1SIqznWr6vKJ7TPHTfkyvxhMrT7YmTOjTzPpBN+qdlY4q7FDEZ5x1movvMZ3Sdn/rpDqxsDf64On7RZrbLxVeq8UOXOEI158YkSuFrM1vpEVtZsaB6h8wTFb7Y2s5Aa98K85whaXYPM2UuoFucjomMhohFKnaIqOi4/2xbNELnCmyNiEBPxeoQHW6Dir52O0Z1e26MCuEzdKASCp9ZB7OjoiJZuWJkuEGVxcDQAA2GIcNjPhAoSLLdboeG/Rvlye5/35daqbJ1IU0IKn4lE9Gmu/1UvrVhkMyiEx6PWMyj673rB5x7tT7I4LpWWXcz7q2//AwyOfGpCRgq3Czl1rFq3NOXjm98tgdQx4dvKD7W6ru1bhE60WiU/OvOTzs/T7tDJSb/eLqBAeU0hQ/Qf8OsbyfZ++ltk8ySp7ehwALAlNWz725rrCq5V12ipe9ewj9MRYvL8Pjfqy1CP+oOgIm4KIEAynGyR0lxQd3pN7+PC+Tf+zPpfYkugwZPLYrA6te2b1aHVbPbRK82Dy4uUAtG/qEzLMrOhHpsBF7gXgZkQgfiVRCu6EAkQjKIjdCQ/kvd+YkfyiaDMeFSR1bngWcWVQkOhJCP0WPqwMEWoN+T1/bUZaqFQKiyNSUOY15DEWEyEdtujHmBqgiMeGIYidiEpn4VsKg/XI6nZauNcNIQbYi/BG9b6f7rFC3oHQIpNUWv2c5+9j8d7AHjC7wxGvgBoYoRTdSBQVQpQOKEPwtzlAIP1bodR6UK4vOvMi1Pv6XdfIREjHYHpOkEqtjzBV/ALp9XgWwzBG9TLhBqTgBnmvqIinoDhOE4JhlkImqfDQSCWKzmMJono23AikGHjTDbIIEn6aIeiV75OOsgEqVE+UqOD2VCLuS+of5ULJhozgjX/UowWNLPJD6F+pc0JFENMQOOIGShhrPaMaD93QEORnXw7ZsRkSQWLoDPUMJppmqW2idBA/3ETFhAw1HPqQKXCxYzMUBbE74YG8NwZF8ou+WAoviLrKgr8HPVhOIuQ+H6UwRIjHKmqQHZuhxFhMhHTYYkB7FWEp4rFhCGInotJZULYVApRup4V73RBigL0Ib1Tv++keK+QdCC0ySaXVz7FjMxSUDihD0LjbzI7NUJCJkI7BZMdmGGpA9WLHZiirORMhPDRSiezYDGXRFxSs2DE8UKvYsRkS5ULJhozgjX/UIzs2Q6IaD93QEORnXw7ZsRkSQWLoDPUMJppmqW2idBA/3ETFhAw1HPqQKXCxYzMUBbE74YG8NwZF8ou+WAoviLrKgr8HPVhOIuQ+H6UwRIjHKmqQHZuhxFhMhHTYYkB7FWEp4rFhCGInotJZULYVApRup4V73RBigL0Ib1Tv++keK+QdCC0ySaXVz7FjMxSUDihD0LjbzI7NUJCJkI7BZMdmGGpA9WLHZiirORMhPDRSiezYDGXRFxSs2DE8UKvYsRkS5ULJhozgjX/UIzs2Q6IaD93QEORnXw7ZsRkSQWLoDPUMJppmqW2idBA/3ETFhAw1HPqQKXCxYzMUBbE74YG8NwZF8ou+WAoviLrKgr8HPVhOIuQ+H6UwRIjHKmqQHZuhxFhMhHTYYkB7FWEp4rFhCGInotJZULYVApRup4V73RBigL0Ib1Tv++keK+QdCC0ySaXVz7FjMxSUDihD0LjbzI7NUJCJkI7BZMdmGGpA9WLHZiirORMhPDRSib5jM2hhwgeaQPxOKuJF6UO8JaFClZ1tOKgjqLDgdxK/8etUGhwEZlUhNynAHBhDwm40DSn9I7Rc0X4xKNL3iAQLjeMnjIM67iI/QgMmHHmvkLXhiFLQXVoRrzG0HguNBkPAxGHrmBqkeyxQHHRlLxVafxMUZApc7NgMRUHsTnggvs6oRN+3QrwgTj58qwcnEVq/hQ8rQ4TIJJCvUUZRQfhFROkNauEwyIpsNMSNzC/6lkLAQjYOk9+CAPOWgSNRMkMjoM8PiaNwBTRRS6GiXDiPoyajJhCVFvLnUhoJNHDEB0olUmIsZSlPT3go5MYdBuj5CI1SkRfhvUIImOSQp+1LaUK/VdQDcpJdmAaOIFm4/KKJSOU4nw/DEY07XisqKIj2DphChUSPGCJ7UI//B/OH4t1VT7FQAAAAAElFTkSuQmCC', 0, 0);

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
  `hidden` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_item_images` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `image_hash` varchar(20) NOT NULL,
  `item_hash` varchar(29) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Stores encrypted/plaintext messages.' ;

CREATE TABLE IF NOT EXISTS `bw_orders` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `created_time` varchar(20) NOT NULL,
  `currency` int(2) NOT NULL,
  `items` text NOT NULL,
  `price` decimal(20,8) NOT NULL,
  `time` varchar(20) NOT NULL,
  `progress` int(1) NOT NULL,
  `buyer_id` int(9) NOT NULL,
  `vendor_hash` varchar(20) NOT NULL,
  `finalized` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_page_authorization` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `auth_level` varchar(15) NOT NULL,
  `system` enum('0','1') NOT NULL,
  `timeout` int(3) NOT NULL,
  `URI` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_pgp_keys` (
  `id` int(9) NOT NULL,
  `fingerprint` varchar(128) NOT NULL,
  `public_key` blob NOT NULL,
  `user_id` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bw_registration_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(100) NOT NULL,
  `user_type` enum('1','2','3') NOT NULL,
  `token_content` varchar(128) NOT NULL,
  `entry_payment` decimal(20,8),
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

CREATE TABLE IF NOT EXISTS `bw_two_factor_tokens` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) NOT NULL,
  `solution` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
