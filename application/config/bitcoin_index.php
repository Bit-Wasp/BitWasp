<?php
$config = array();

// proxy['type'] : CURLPROXY_HTTP ? CURLPROXY_SOCKS5;

$config['Disabled'] = array('disabled' => true);
$config['CoinDesk'] = array('url' => 'http://api.coindesk.com/v1/bpi/currentprice.json');
$config['BitcoinAverage'] = array('url' => 'https://api.bitcoinaverage.com/all');							
