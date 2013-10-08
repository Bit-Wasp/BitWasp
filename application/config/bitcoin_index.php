<?php
$config = array();

// proxy['type'] : CURLPROXY_HTTP ? CURLPROXY_SOCKS5;

$config['Disabled'] = array('disabled' => true);
$config['CoinDesk'] = array('url' => 'http://api.coindesk.com/v1/bpi/currentprice.json');
$config['CoinDesk(tor)'] = array('url' => 'http://api.coindesk.com/v1/bpi/currentprice.json',
								 'proxy' => array(	'type' => CURLPROXY_SOCKS5,
													'url' => 'localhost:9050')
								);
$config['BitcoinAverage'] = array('url' => 'https://api.bitcoinaverage.com/all');							

$config['BitcoinAverage(tor)'] = array('url' => 'https://api.bitcoinaverage.com/all',
								 'proxy' => array(	'type' => CURLPROXY_SOCKS5,
													'url' => 'localhost:9050')
								);
