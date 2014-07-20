<?php

class Core {

	// Function to validate the post data
	function validate_post($data)
	{

		if(empty($data['db_hostname']))
			return FALSE;
		if(empty($data['db_username']))
			return FALSE;
		if(empty($data['db_password']))
			return FALSE;

		if(empty($data['db_database']))
			return FALSE;
		if(empty($data['btc_ip']))
			return FALSE;
        if(empty($data['btc_port']))
			return FALSE;
		if(empty($data['btc_username']))
			return FALSE;
		if(empty($data['btc_password']))
			return FALSE;
        echo 'a';
		if(empty($data['admin_password']))
			return FALSE;
		if(!isset($data['allow_guests']) OR !in_array($data['allow_guests'], array('0','1')))
			return FALSE;
		if(!isset($data['tidy_urls']) OR !in_array($data['tidy_urls'], array('0','1')))
			return FALSE;
        echo 'b';
		if(empty($data['electrum_mpk']))
			return FALSE;
		if(!isset($data['force_vendor_pgp']) OR !in_array($data['force_vendor_pgp'], array('0','1')))
			return FALSE;
        echo 'c';
		if(!isset($data['encrypt_private_messages']) OR !in_array($data['encrypt_private_messages'], array('0','1')))
			return FALSE;
		if($data['encrypt_private_messages'] == '1' AND empty($data['admin_pm_password']))
				return FALSE;
		return TRUE;
	}
	
	function random_key_string() {
		$source = bin2hex(openssl_random_pseudo_bytes(128));
		$string = '';
		$c = 0;
		while(strlen($string) < 32) { 
			$dec = gmp_strval(gmp_init(substr($source, $c*2, 2), 16),10);
			if($dec > 33 && $dec < 127 && chr($dec) !== "'")
				$string.=chr($dec);
			$c++;
		}
		return $string;
	}

	// Function to show an error
	function show_message($type,$message) {
		return $message;
	}

	// Function to write the config file
	function write_database_config($data) {

		// Config path
		$template_path 	= 'config/database.php';
		$output_path 	= '../application/config/database.php';

		// Open the file
		$database_file = file_get_contents($template_path);

		$new  = str_replace("%HOSTNAME%",$data['db_hostname'],$database_file);
		$new  = str_replace("%USERNAME%",$data['db_username'],$new);
		$new  = str_replace("%PASSWORD%",$data['db_password'],$new);
		$new  = str_replace("%DATABASE%",$data['db_database'],$new);

		// Write the new database.php file
		$handle = fopen($output_path,'w+');

		// Chmod the file, in case the user forgot
		@chmod($output_path,0777);

		// Verify file permissions
		if(is_writable($output_path)) {

			// Write the file
			if(fwrite($handle,$new)) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
	
	// Function to write the config file
	function write_bitcoin_config($data) {

		// Config path
		$template_path 	= 'config/bitcoin.php';
		$output_path 	= '../application/config/bitcoin.php';

		// Open the file
		$bitcoin_file = file_get_contents($template_path);

		$new  = str_replace("%BTC_IP%",$data['btc_ip'],$bitcoin_file);
		$new  = str_replace("%BTC_PORT%",$data['btc_port'],$new);
		$new  = str_replace("%BTC_USERNAME%",$data['btc_username'],$new);
		$new  = str_replace("%BTC_PASSWORD%",$data['btc_password'],$new);
		$new  = str_replace("%BTC_SSL%",((isset($data['btc_ssl']) && $data['btc_ssl'] == '1') ? 'TRUE' : 'FALSE'),$new);

		// Write the new database.php file
		$handle = fopen($output_path,'w+');

		// Chmod the file, in case the user forgot
		@chmod($output_path,0777);

		// Verify file permissions
		if(is_writable($output_path)) {

			// Write the file
			if(fwrite($handle,$new)) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
	
	// Function to write the config file
	function write_config_config($data) {

		// Config path
		$template_path 	= 'config/config.php';
		$output_path 	= '../application/config/config.php';

		// Open the file
		$config_file = file_get_contents($template_path);

		$new = str_replace("%ENCRYPTION_KEY%",$data['encryption_key'],$config_file);
		$new = str_replace("%INDEX_PAGE%", (($data['tidy_urls'] == '1')?'':'index.php'), $new);
		// Write the new database.php file
		$handle = fopen($output_path,'w+');

		// Chmod the file, in case the user forgot
		@chmod($output_path,0777);

		// Verify file permissions
		if(is_writable($output_path)) {

			// Write the file
			if(fwrite($handle,$new)) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
	
}
