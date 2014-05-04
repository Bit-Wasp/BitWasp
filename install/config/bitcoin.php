<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| BITCOIN CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access bitcoind.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['ssl'] 		Indicates whether bitcoind has an SSL certificate configured.
|	['user']		The username for the RPC interface.
|	['password']	The password for the RPC interface.
|	['ip']			The IP of the RPC interface.
| 	['port']		The Port for the RPC interface.
|	['url']			DO NOT CHANGE. Generates the URL to use with the Jsonrpcclient. 
*/

$config['ssl']		= %BTC_SSL%;
$config['user']		= "%BTC_USERNAME%";
$config['password'] = "%BTC_PASSWORD%";
$config['host']		= '%BTC_IP%';
$config['port']		= '%BTC_PORT%';
$config['url']		= (($config['ssl'] == TRUE) ? 'https://' : 'http://').$config['ssl'].$config['user'].':'.$config['password'].'@'.$config['host'].':'.$config['port'].'/';

/* End of file bitcoin.php */
/* Location: ./application/config/bitcoin.php */
