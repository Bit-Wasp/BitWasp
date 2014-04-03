<?php

require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');
require_once(dirname(__FILE__).'/BitcoinLib.php');

/**
 * BIP32
 * 
 * This library contains function which implement BIP32.
 * More information on this implementation can be found here:
 * https://github.com/sipa/bips/blob/bip32update/bip-0032.mediawiki
 * The library supports Bitcoin and Dogecoin mainnet/testnet keys.
 * 
 * - Master keys can be generated from a hex seed. 
 * - A child key derivation function is defined which when supplied with
 *   a parent extended key and a tuple of address bytes, a 32bit number 
 *   treated as a hex string.
 * - A function to generate the tuple of address bytes given a parent 
 *   extended key and a string describing the desired definition.
 * - A master function used to derive an extended key from a parent
 *   extended key and a string describing the desired definition.
 * - A master function used to derive an address given an extended key 
 *   and a string describing the desired definition.
 * - A function to encode an array of the key's properties as base58check
 *   encoded key.
 * - A function to decode a base58check encoded key into an array of
 *   properties. 
 * - A function to convert an extended key to it's address.
 * - A function to convert an extended private to public key.
 * - A function which takes an extended keys magic bytes and returns an 
 *   array of information, if it's supported.
 * - A function to calculate the address bytes for a given number, and 
 *   if the number is to have the MSB set.
 * - A function to check if the address bytes calls for a prime derivation.
 * - A function which checks if the generated private key, given as a 
 *   hex string, is a valid private key.
 * - A function to extract the decimal number encoded in the hex bytes.
 * 
 * Thomas Kerin
 */

class BIP32 {
	
	// Bitcoin
	public static $bitcoin_mainnet_public = '0488b21e';
	public static $bitcoin_mainnet_private = '0488ade4';
	public static $bitcoin_mainnet_version = '00';
	public static $bitcoin_testnet_public = '043587cf';
	public static $bitcoin_testnet_private = '04358394';
	public static $bitcoin_testnet_version = '6f';
	// Dogecoin
	public static $dogecoin_mainnet_public = '02facafd';
	public static $dogecoin_mainnet_private = '02fac398';
	public static $dogecoin_mainnet_version = '1e';
	public static $dogecoin_testnet_public = '0432a9a8';
	public static $dogecoin_testnet_private = '0432a243';
	public static $dogecoin_testnet_version = '71';
	// Litecoin
	public static $litecoin_mainnet_public = '019da462';
	public static $litecoin_mainnet_private = '019d9cfe';
	public static $litecoin_mainnet_version = '30';
	public static $litecoin_testnet_public = '0436f6e1';
	public static $litecoin_testnet_private = '0436ef7d';
	public static $litecoin_testnet_version = '6f';
	
	/**
	 * Master Key
	 * 
	 * This function accepts a hex string as a $seed, and allows you to 
	 * select which network/coin you want to generate, as well as testnet
	 * extended keys. 
	 * 
	 * Returns false if the key is invalid, or 'm' - the extended master private key.
	 * @param	string	$seed
	 * @param	string(opt)	$network
	 * @param	boolean(opt) $testnet
	 * @return	string
	 */
	public static function master_key($seed, $network = 'bitcoin', $testnet = FALSE) {
		// Generate HMAC hash, and the key/chaincode.
		$I = hash_hmac('sha512', pack("H*", $seed), "Bitcoin seed");
		$I_l = substr($I, 0, 64);
		$I_r = substr($I, 64, 64);
		
		// Error checking!
		if(self::check_valid_hmac_key($I_l) == FALSE)
			return FALSE;

		$data = array(
					'network' => $network,
					'testnet' => $testnet,
					'type' => 'private',
					'depth' => '0',
					'fingerprint' => '00000000',
					'i' => '00000000',
					'chain_code' => $I_r,
					'key' => $I_l,
					);

		return array(self::encode($data), 'm');
	}

	/**
	 * CKD
	 * 
	 * This recursive function accepts $master, a parent extended key, 
	 * and an array of address bytes (the $address_definition tuple). It 
	 * pop's the next value from the $address_definition tuple and 
	 * generates the desired key. If the $address_definition tuple is 
	 * empty, then it returns the key. If not, then it calls itself again 
	 * with the new key and the tuple with the remaining key indexes to 
	 * generate, but will terminate with an array containing the desired
	 * key at index 0, and it's human readable definition in the second.
	 * 
	 * @param	string	$master
	 * @param	array	$address_definition
	 * @return	array
	 */
	public static function CKD($master, $address_definition, $generated = array()) {
		$previous = self::import($master);
		
		if($previous['type'] == 'private') {
			$private_key = $previous['key'];
			$public_key = BitcoinLib::private_key_to_public_key($private_key, TRUE);
		} else if($previous['type'] == 'public') { 
			$public_key = $previous['key'];
		} else {
			return FALSE;
		}
				
		$fingerprint = substr(hash('ripemd160', hash('sha256', pack("H*", $public_key), TRUE)), 0, 8);
		
		$i = array_pop($address_definition);
		
		$is_prime = self::check_is_prime_hex($i);
		if($is_prime == 1) {
			if($previous['type'] == 'public')
				return FALSE; // Cannot derive private from public key.
			$data = '00'.$private_key.$i;
		} else if($is_prime == 0) {
			$data = $public_key.$i;
		}

		if(!isset($data))
			return FALSE;
		
		$I = hash_hmac('sha512', pack("H*", $data), pack("H*", $previous['chain_code']));
		$I_l = substr($I, 0, 64);
		$I_r = substr($I, 64, 64);
			
		if(self::check_valid_hmac_key($I_l) == FALSE) {
			// calculate the next i in the sequence, and start over with that. 
			$new_i = self::calc_address_bytes(self::get_address_number($i)+1, $is_prime);
			array_push($address_definition, $new_i);
			return self::CKD($master, $address_definition, $generated);
		}
		
		// Keep a record of the address being built. Done after error 
		// checking so only valid keys get to this point.
		if(count($generated) == 0 && $previous['depth'] == 0)
			array_push($generated, (($previous['type'] == 'private') ? 'm' : 'M'));
			
		array_push($generated, (self::get_address_number($i, $is_prime).(($is_prime == 1) ? "'" : NULL)));
		
		$g = SECcurve::generator_secp256k1();
		$n = $g->getOrder();
		
		if($previous['type'] == 'private') {
			// (Il + kpar) mod n
			$key = str_pad( 
						gmp_strval(
							gmp_Utils::gmp_mod2(
								gmp_add(
									gmp_init($I_l, 16),
									gmp_init($private_key, 16)
								),
								$n	
							),
							16
						), 
					64, '0', STR_PAD_LEFT);
		} else if($previous['type'] == 'public') {
			// newPoint + parentPubkeyPoint
			$decompressed = BitcoinLib::decompress_public_key($public_key);
			$curve = SECcurve::curve_secp256k1();
			
			// Prepare offset, by multiplying Il by g, and adding this to the previous public key point.
			// Create a new point by adding the two.
			$new_point = Point::add(
										Point::mul(
											gmp_init($I_l, 16), 
											$g
										), 
										$decompressed['point']
								);
			$new_x = str_pad(gmp_strval($new_point->getX(), 16), 32, '0', STR_PAD_LEFT);
			$new_y = str_pad(gmp_strval($new_point->getY(), 16), 32, '0', STR_PAD_LEFT);
			$key = BitcoinLib::compress_public_key('04'.$new_x.$new_y);
			
		}
		if(!isset($key)) return FALSE;

		$data = array(
					 'network' => $previous['network'],
					 'testnet' => $previous['testnet'],
					 'magic_bytes' => $previous['magic_bytes'],
					 'type' => $previous['type'],
					 'depth' => $previous['depth']+1,
					 'fingerprint' => $fingerprint,
					 'i' => $i, 
					 'address_number' => self::get_address_number($i),
					 'chain_code' => $I_r,
					 'key' => $key
				);
				
		return (count($address_definition) > 0) ? self::CKD(self::encode($data), $address_definition, $generated) : array(self::encode($data), implode('/', $generated));
	}

	/**
	 * Get Definition Tuple
	 * 
	 * This function accepts a '/' separated string of numbers, and generates
	 * an array of 32-bit numbers (in hex) which are address child number
	 * for the derivation in CKD. It needs $parent, an extended key, in 
	 * order to generate the correct hex bytes for the address.
	 * 
	 * @param	string	$parent
	 * @param	string	$string_def
	 * @return	array
	 */
	public static function get_definition_tuple($parent, $string_def) {
		// Extract the child numbers.
		$address_definition = explode("/", $string_def);
		
		// Load the depth of the parent key.
		$import = self::import($parent);
		$depth = $import['depth']; 
		
		// Start building the address bytes tuple.
		foreach($address_definition as &$def) {
			
			// Check if we want the prime derivation
			$want_prime = 0;
			if(strpos($def, "'") !== FALSE) {
				// Remove ' from the number, and set $want_prime
				str_replace("'", '', $def);
				$want_prime = 1;
			}

			$def = self::calc_address_bytes($def, $want_prime);
			$depth++;
		}
		
		// Reverse the array (to allow array_pop to work) and return.
		return array_reverse($address_definition);
	}

	/**
	 * Build Key
	 * 
	 * This function accepts a parent extended key, and a string definition
	 * describing the desired derivation '0/0/1' or '0/1'. See get_definition_tuple()
	 * for information on generating the address bytes from this definition.
	 * The address bytes tuple is then passed to the recursive CKD function,
	 * which pops a value from the array, generates that key, and then
	 * decides if it needs to process more ($address_definition array 
	 * still has values) where it will call itself again, or else if its 
	 * work is done it returns the key.
	 * 
	 * @param	string	$input
	 * @param	string	$string_def
	 * @return	string
	 */
	public static function build_key($input, $string_def) {
		if(is_array($input) && count($input) == 2) {
			$parent = $input[0];
			$def = $input[1];
		} else if(is_string($input) == TRUE) {
			$parent = $input;
		} else {
			return FALSE;
		}
		$address_definition = self::get_definition_tuple($parent, $string_def);
		
		if(isset($def)== TRUE) {
			$extended_key = self::CKD($parent, $address_definition, explode("/", $def));
			return $extended_key;
		} else {
			$extended_key = self::CKD($parent, $address_definition);
			return $extended_key;
		}
	}
	
	/**
	 * Build Address
	 * 
	 * This function calls build_key() to generate the desired key, and 
	 * then converts the generated key to it's corresponding address.
	 * 
	 * @param	string	$master
	 * @param	string	$string_def
	 * @param	string	$address_version
	 * @return	string
	 */
	public static function build_address($master, $string_def) {
		$extended_key = self::build_key($master, $string_def);
		return array(self::key_to_address($extended_key[0]), $extended[1]);
	}

	/**
	 * Encode
	 * 
	 * This function accepts an array of information describing the 
	 * extended key. It will determine the magic bytes depending on the
	 * network, testnet, and type indexes. The fingerprint is accepted
	 * as-is, because the CKD() and master_key() functions work that out
	 * themselves. The child number is fixed at '00000000'. Private key's 
	 * are padded with \x00 to ensure they are 33 bytes. This information
	 * is concatenated and converted to base58check encoding.
	 * The input array has the same indexes as the output from the import() 
	 * function to ensure compatibility.
	 * 
	 * @param	array	$data
	 * @return	string
	 */
	public static function encode($data) {
		// Magic Byte - 4 bytes / 8 characters - left out for now
		$magic_byte_var = strtolower($data['network'])."_".(($data['testnet'] == TRUE) ? 'testnet' : 'mainnet')."_{$data['type']}";
		$magic_byte = self::$$magic_byte_var;
		
		$fingerprint = $data['fingerprint'];
		$child_number = $data['i'];
		
		$depth = str_pad(gmp_strval(gmp_init($data['depth'], 10),16), 2, '0', STR_PAD_LEFT);
		$chain_code = $data['chain_code'];
		$key_data = ($data['type'] == 'public') ? $data['key'] : '00'.$data['key'];
		$string = $magic_byte.$depth.$fingerprint.$child_number.$chain_code.$key_data;
		return BitcoinLib::base58_encode_checksum($string);
	}

	/*
	 * Import
	 * 
	 * This function generates an array containing the properties of the 
	 * extended key. It decodes the extended key, and works determines
	 * as much information as possible to allow compatibility with the 
	 * encode function, which accepts a similarly constructed array.
	 * 
	 * @param	string	$ext_public_key
	 * @return	array
	 */
	public function import($ext_key) {
		$hex = BitcoinLib::base58_decode($ext_key);
		$key['magic_bytes'] = substr($hex, 0, 8);
		$magic_byte_info = self::describe_magic_bytes($key['magic_bytes']);
		$key['type'] = $magic_byte_info['type'];
		$key['testnet'] = $magic_byte_info['testnet'];
		$key['network'] = $magic_byte_info['network'];
		$key['version'] = $magic_byte_info['version'];
		$key['depth'] = gmp_strval(gmp_init(substr($hex, 8, 2),16),10);
		$key['fingerprint'] = substr($hex, 10, 8);
		$key['i'] = substr($hex, 18, 8);
		$key['address_number'] = self::get_address_number($key['i']);
		$key['chain_code'] = substr($hex, 26, 64);

		if($key['type'] == 'public') {
			$key_start_position = 90;
			$offset = 66;
		} else {
			$key_start_position = 92;
			$offset = 64;
		}
		$key['key'] = substr($hex, $key_start_position, $offset);
		return $key;
	}

	/**
	 * Extended Private To Public 
	 * 
	 * Converts the encoded private key to a public key, and alters the
	 * properties so it's displayed as a public key.
	 * 
	 * @param	string	$ext_private_key
	 * @return	string
	 */
	public static function extended_private_to_public($input) {
		if(is_array($input) && count($input) == 2){
			$ext_private_key = $input[0];
			$generated = $input[1];
		} else if(is_string($input) == TRUE) {
			$ext_private_key = $input;
			$generated = FALSE;
		} else {
			return FALSE;
		}
		
		$pubkey = self::import($ext_private_key);
		if($pubkey['type'] !== 'private')
			return FALSE;
			
		$pubkey['key'] = BitcoinLib::private_key_to_public_key($pubkey['key'], TRUE);
		$pubkey['type'] = 'public';
		
		if($generated !== FALSE) {
			$generated = str_replace('m', 'M', $generated);
			return array(self::encode($pubkey), $generated);
		} else {
			return self::encode($pubkey);
		}
	}
	
	
	public static function extract_public_key($input) {
		if(is_array($input) && count($input) == 2) {
			$ext_key = $input[0];
			$generated = $input[1];
		} else if(is_string($input) == TRUE) {
			$ext_key = $input;
			$generated = FALSE;
		} else {
			return FALSE;
		}
		
		$import = self::import($ext_key);
		return ($import['type'] == 'private') ? BitcoinLib::private_key_to_public_key($import['key'], TRUE) : $import['key'];
	}
	
	/**
	 * Key To Address
	 * 
	 * This function accepts a bip32 extended key, and converts it to a 
	 * bitcoin address. 
	 * 
	 * @param	string	$extended_key
	 * @param	string	$address_version
	 * return	string/FALSE
	 */
	public static function key_to_address($extended_key) {
		$import = self::import($extended_key);
		if($import['type'] == 'public') {
			$public = $import['key'];
		} else if($import['type'] == 'private') {
			$public = BitcoinLib::private_key_to_public_key($import['key'], TRUE);
		} else {
			return FALSE;
		}
		
		// Convert the public key to the address.
		return BitcoinLib::public_key_to_address($public, $import['version']);
	}
	
	/**
	 * Describe Magic Bytes
	 * 
	 * This function accepts a $magic_bytes string, which is compared to
	 * a predefined list of constants. If the $magic_bytes string is found,
	 * it returns an array of information about the bytes: the key type,
	 * a boolean for whether its a testnet key, and the cryptocoin network.
	 * 
	 * @param	string	$magic_bytes
	 * @return	array/FALSE
	 */
	public function describe_magic_bytes($magic_bytes) {
		$key = array();
		switch($magic_bytes){
			case self::$bitcoin_mainnet_public :
				$key['type'] = 'public';
				$key['testnet'] = false;
				$key['network'] = 'bitcoin';
				$key['version'] = self::$bitcoin_mainnet_version;
				break;
			case self::$bitcoin_mainnet_private :
				$key['type'] = 'private';
				$key['testnet'] = false;
				$key['network'] = 'bitcoin';
				$key['version'] = self::$bitcoin_mainnet_version;
				break;				

			case self::$bitcoin_testnet_public :
				$key['type'] = 'public';
				$key['testnet'] = true;
				$key['network'] = 'bitcoin';
				$key['version'] = self::$bitcoin_testnet_version;
				break;				
			case self::$bitcoin_testnet_private :
				$key['type'] = 'private';
				$key['testnet'] = true;
				$key['network'] = 'bitcoin';
				$key['version'] = self::$bitcoin_testnet_version;
				break;		

			case self::$dogecoin_mainnet_public :
				$key['type'] = 'public';
				$key['testnet'] = false;
				$key['network'] = 'dogecoin';
				$key['version'] = self::$dogecoin_mainnet_version;
				break;
			case self::$dogecoin_mainnet_private :
				$key['type'] = 'private';
				$key['testnet'] = false;
				$key['network'] = 'dogecoin';
				$key['version'] = self::$dogecoin_mainnet_version;
				break;	

			case self::$dogecoin_testnet_public :
				$key['type'] = 'public';
				$key['testnet'] = true;
				$key['network'] = 'dogecoin';
				$key['version'] = self::$dogecoin_testnet_version;
				break;				
			case self::$dogecoin_testnet_private :
				$key['type'] = 'private';
				$key['testnet'] = true;
				$key['network'] = 'dogecoin';
				$key['version'] = self::$dogecoin_testnet_version;
				break;		

			case self::$litecoin_mainnet_public :
				$key['type'] = 'public';
				$key['testnet'] = false;
				$key['network'] = 'litecoin';
				$key['version'] = self::$litecoin_mainnet_version;
				break;
			case self::$litecoin_mainnet_private :
				$key['type'] = 'private';
				$key['testnet'] = false;
				$key['network'] = 'litecoin';
				$key['version'] = self::$litecoin_mainnet_version;
				break;	

			case self::$litecoin_testnet_public :
				$key['type'] = 'public';
				$key['testnet'] = true;
				$key['network'] = 'litecoin';
				$key['version'] = self::$litecoin_testnet_version;
				break;				
			case self::$dogecoin_testnet_private :
				$key['type'] = 'private';
				$key['testnet'] = true;
				$key['network'] = 'litecoin';
				$key['version'] = self::$litecoin_testnet_version;
				break;		

			default:
				return FALSE;
		}
		return $key;
	}
	
	/**
	 * Calc Address Bytes
	 * 
	 * This function is used to convert the $address_number, i, into a 32
	 * bit unsigned integer. If $set_prime = 1, then it will flip the left-most
	 * bit, indicating a prime derivation must be used.
	 * 
	 * @param	int	$address_number
	 * @return	string
	 */
	public function calc_address_bytes($address_number, $set_prime = 0) {
		$and_result = ($set_prime == 1) ? $address_number | 0x80000000 : $address_number;
		$hex = unpack("H*", pack("N", $and_result)); 
		return $hex[1];
	}

	/**
	 * Check Is Prime Hex
	 * 
	 * Checks if the highest most bit is set - that prime derivation must
	 * be used. Test is done by initializing the address $hex as a number,
	 * and checking if it is greater than 0x80000000. Returns 0 if not 
	 * prime, and 1 if the number is prime.
	 * 
	 * @param	string	$hex
	 * @return	int
	 */
	public function check_is_prime_hex($hex) {
		$is_prime = (	gmp_cmp(
							gmp_init($hex, 16),
							gmp_init('80000000', 16) 
						) == -1 ) ? 0 : 1;
		return $is_prime;
	}

	/**
	 * Check Valid HMAC Key
	 * 
	 * This function checks that the generated private keys meet the standard
	 * for private keys, as imposed by the secp256k1 curve. The key can't
	 * be zero, nor can it >= $n, which is the order of the secp256k1 
	 * curve. Returning false trigger an error, or cause the program to
	 * increase the address number and rerun the CKD function.
	 * 
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function check_valid_hmac_key($key) {
		$g = SECcurve::generator_secp256k1();
		$n = $g->getOrder();
		
		// initialize the key as a base 16 number.
		$g_l = gmp_init($key, 16);
		// compare it to zero
		$_equal_zero = gmp_cmp($g_l, gmp_init(0,10));
		// compare it to the order of the curve
		$_GE_n = gmp_cmp($g_l, $n);
		
		if(	$_equal_zero == 0 ||$_GE_n == 1 ||	$_GE_n == 0 )	
			return FALSE; // Check for invalid data

		return TRUE;
	}

	
	/**
	 * Get Address Number
	 * 
	 * Convert the 32 bit integer into a decimal numbe, and perform an &
	 * to unset the byte.
	 * @param	string	$hex
	 * @param	int
	 */
	public function get_address_number($hex, $is_prime = 0) {
		if($is_prime == 1)
			$hex = str_pad(
						gmp_strval(
							gmp_sub(
								gmp_init($hex, 16),
								gmp_init('80000000', 16)
							),
							16
						),
						8, '0', STR_PAD_LEFT);
		$dec = gmp_strval(gmp_init($hex, 16),10);
		$n = $dec & 0x7fffffff;
		return $n;
	}
};
