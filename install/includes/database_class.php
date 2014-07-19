<?php

class Database {

	// Function to the database and tables and fill them with the default data
	function create_database($data)
	{
		// Connect to the database
		$mysqli = new mysqli($data['db_hostname'],$data['db_username'],$data['db_password'],'');

		// Check for errors
		if(mysqli_connect_errno()){
			var_dump($mysqli);
			return false;
		}

		// Create the prepared statement
		$mysqli->query("CREATE DATABASE IF NOT EXISTS ".$data['db_database']);
			var_dump($mysqli);
		// Close the connection
		$mysqli->close();

		return true;
	}

    public function generate_salt() {
        $rounds = '10';
        return '$2a$'.$rounds.'$'.str_replace("+", "o", base64_encode(openssl_random_pseudo_bytes(22)));
    }

    public function password($password) {
        $salt = $this->generate_salt();
        $hash = crypt($password, $salt);
        return array('hash' => $hash,
            'salt' => $salt);
    }

    public function handle_enc_pms($data) {
        if($data['encrypt_private_messages'] == '1') {
            $message_password = $this->password($data['admin_pm_password']);

            $openssl_config = array(	"digest_alg" => 'sha512',
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA);
            $keypair = openssl_pkey_new($openssl_config);

            /* Extract the private key from $res to $private_key */
            openssl_pkey_export($keypair, $private_key, $message_password['hash'], $openssl_config);

            // Extract the public key from $res to $public_key
            $public_key = openssl_pkey_get_details($keypair);
            $public_key = $public_key['key'];
            return array('public_key' => base64_encode($public_key),
                'private_key' => base64_encode($private_key),
                'private_key_salt' => $message_password['salt']);
        } else {
            $salt_only = $this->password('');
            return array('public_key' => '0',
                'private_key' => '0',
                'private_key_salt' => $salt_only['salt']);
        }
    }


    // Function to create the tables and fill them with the default data
	public function create_tables($data)
	{
		// Connect to the database
		$mysqli = new mysqli($data['db_hostname'],$data['db_username'],$data['db_password'],$data['db_database']);

		// Check for errors
		if(mysqli_connect_errno()){
			return false;
		}

		// Open the default SQL file
		$query = file_get_contents('assets/install.sql');

		$pw = $this->password($data['admin_password']);

		$handled_enc = $this->handle_enc_pms($data);

		$new  = str_replace("%ENCRYPT_PRIVATE_MESSAGES%",$data['encrypt_private_messages'],$query);
		$new  = str_replace("%ALLOW_GUESTS%",$data['allow_guests'],$new);
		$new  = str_replace("%FORCE_VENDOR_PGP%",$data['force_vendor_pgp'],$new);
		$new  = str_replace("%ELECTRUM_MPK%",$data['electrum_mpk'],$new);
		$new  = str_replace("%PASSWORD%", $pw['hash'], $new);
		$new  = str_replace("%PUBLIC_KEY%", $handled_enc['public_key'], $new);
		$new  = str_replace("%PRIVATE_KEY%", $handled_enc['private_key'], $new);
        $new  = str_replace("%PRIVATE_KEY_SALT%", $handled_enc['private_key_salt'], $new);
		$new  = str_replace("%REGISTER_TIME%", time(), $new);
		$new  = str_replace("%USER_HASH%", substr(hash('sha512', mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)), 0, 16), $new);
		$new  = str_replace("%SALT%", $pw['salt'], $new);
		
		// Execute a multi query
		$mysqli->multi_query($new);
        if(mysqli_connect_errno()){
            var_dump($mysqli);
            return false;
        }
		// Close the connection
		$mysqli->close();

		return true;
	}
	
	function add_config_entries($data) {
		$mysqli = new mysqli($data['db_hostname'],$data['db_username'],$data['db_password'],$data['db_database']);
		// Check for errors
		if(mysqli_connect_errno()){
			var_dump($mysqli);
			return false;
		}
		$query = "INSERT INTO `bw_config` (`parameter`, `value`) VALUES ('electrum_mpk','{$data['electrum_mpk']}'), ('allow_guests','{$data['allow_guests']}'), ('force_vendor_pgp','{$data['force_vendor_pgp']}'), ('encrypt_private_messages','{$data['encrypt_private_messages']}')";
		
		$mysqli->query($query);
		$mysqli->close();
		
		return true;
	}
	
	function add_admin($data) {
		$mysqli = new mysqli($data['db_hostname'],$data['db_username'],$data['db_password'],$data['db_database']);
		// Check for errors
		if(mysqli_connect_errno()){
			var_dump($mysqli);
			return false;
		}

		$extra_params = '';
		$extra_values = '';

		$handle_enc_pms = function($data) {
			if($data['encrypt_private_messages'] == '1') {
				$openssl_config = array(	"digest_alg" => 'sha512',
								"private_key_bits" => 2048,
								"private_key_type" => OPENSSL_KEYTYPE_RSA);
				$keypair = openssl_pkey_new($openssl_config);

				/* Extract the private key from $res to $private_key */
				openssl_pkey_export($keypair, $private_key, $data['admin_pm_password'], $openssl_config);
				
				// Extract the public key from $res to $public_key
				$public_key = openssl_pkey_get_details($keypair);
				$public_key = $public_key['key'];
				return array('params' => ",public_key,private_key",
							 'values' => ",'".base64_encode($public_key)."','".base64_encode($private_key)."'");
			} else {
				return array('params' => ",public_key,private_key",
							 'values' => ",'0','0'");
			}
		};

		$handled_enc = $handle_enc_pms($data);
		$extra_params.= $handled_enc['params'];
		$extra_values.= $handled_enc['values'];

		$password = function($password) {
			$salt = hash('sha512', mcrypt_create_iv(512, MCRYPT_DEV_URANDOM));
			$hash = $password;
			for($i = 0; $i < 10; $i++) {
				$hash = hash('sha512', $hash);
			}
			
			for($i = 0; $i < 10; $i++) {
				$hash = hash('sha512', $hash.$salt);
			}
			return array('password' => $hash,
						 'salt' => $salt);
		};

		$pw = $password($data['admin_password']);

		$register_time = time();
		$user_hash = substr(hash('sha512', mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)), 0, 16);
		$query = "INSERT INTO `{$data['db_database']}`.`bw_users` (banned, block_non_pgp,entry_paid,force_pgp_messages,location,salt,password,register_time,user_hash,user_name,user_role,local_currency{$extra_params}) VALUES ('0','0','1','0','1','{$pw['salt']}','{$pw['password']}','{$register_time}','{$user_hash}','admin','Admin','0'{$extra_values})";

		$mysqli->query($query);

        // Check for errors
        if(mysqli_connect_errno()){
            var_dump($mysqli);
            return false;
        }

        $mysqli->close();

		return true;
				
	}
}
