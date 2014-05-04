<?php

require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');
require_once(dirname(__FILE__).'/BitcoinLib.php');

/**
 * Raw Transaction Library
 * 
 * This library contains functions used to decode hex-encoded raw transactions 
 * into an array mirroring bitcoind's decoderawtransaction format.
 * 
 * Highest level functions are:
 *  - decode	: decodes a raw transaction hex to a bitcoind-like array
 *  - encode	: encodes a bitcoind-like transaction array to a raw transaction hex.
 *  - validate_signed_transaction : takes a raw transaction hex, it's json inputs, 
 * 				  and an optional input-specifier, and validates the signature(s).
 *  - create_multisig : creates a multisig address from m, and the public keys.
 *  - create_redeem_script - takes a set of public keys, and the number of signatures
 *                required to redeem funds.
 *  - decode_redeem_script - decodes a redeemScript to obtain the pubkeys, m, and n.
 */

class Raw_transaction {

	/**
	 * Some of the defined OP CODES available in Bitcoins script.
	 * None of these are interpetted, apart from PUSHDATA and OP_2-OP_16.
	 */
	public static $op_code = array(	
			'00' => 'OP_FALSE', 	'61' => 'OP_NOP',			'6a' => 'OP_RETURN',
			'76' => 'OP_DUP',		'87' => 'OP_EQUAL',			'88' => 'OP_EQUALVERIFY',
			'51' => 'OP_TRUE',		'a6' => 'OP_RIPEMD160',		'a7' => 'OP_SHA1',
			'a8' => 'OP_SHA256',	'a9' => 'OP_HASH160',		'aa' => 'OP_HASH256',
			'ac' => 'OP_CHECKSIG',	'ae' => 'OP_CHECKMULTISIG'	);

	/**
	 * Flip Byte Order
	 * 
	 * This function is used to swap the byte ordering from little to big
	 * endian, and vice-versa. A byte string, not a reference, is supplied,
	 * the byte order reversed, and the string returned.
	 * 
	 * @param	string	$bytes
	 * @return	string
	 */
	public static function _flip_byte_order($bytes) {
		return implode('', array_reverse(str_split($bytes, 2)));
	}
	
	/**
	 * Return Bytes
	 * 
	 * This function accepts $string as a reference, and takes the first
	 * $byte_count bytes of hex (twice the number when dealing with hex
	 * characters in a string), and returns it to the user. 
	 * Setting the third parameter to TRUE will cause the byte order to flip.
	 * Note: Because $string is a reference to the original copy, this
	 * function actually removes the data from the string.
	 * 
	 * @param	string	$string
	 * @param	int	$byte_count
	 * @param	boolean $reverse
	 * @return	string
	 */
	public static function _return_bytes(&$string, $byte_count, $reverse = FALSE) {
		$requested_bytes = substr($string, 0, $byte_count*2);
		
		// Overwrite $string, starting $byte_count bytes from the start.
		$string = substr($string, $byte_count*2);
		// Flip byte order if requested.
		return ($reverse == FALSE) ? $requested_bytes : self::_flip_byte_order($requested_bytes);
	}

	/**
	 * Decimal to Bytes
	 * 
	 * This function encodes a $decimal number as a $bytes byte long hex string.
	 * Byte order can be flipped by setting $reverse to TRUE.
	 * 
	 * @param	int	$decimal
	 * @param	int	$bytes
	 * @param	boolean	$reverse
	 * @return	string
	 */
	public static function _dec_to_bytes($decimal, $bytes, $reverse = FALSE) {
		$hex = base_convert($decimal, 10, 16);
		if(strlen($hex) %2 != 0) 
			$hex = "0".$hex;

		$hex = str_pad($hex, $bytes*2, "0", STR_PAD_LEFT);
		
		return ($reverse == TRUE) ? self::_flip_byte_order($hex) : $hex;
	}	
	
	/**
	 * Get VarInt
	 * 
	 * https://en.bitcoin.it/wiki/Protocol_specification#Variable_length_integer
	 * 
	 * This function is used when dealing with a varint. Because their size
	 * is variable, the first byte must be checked to learn the true length 
	 * of the encoded number.
	 * $tx is passed by reference, and the first byte is popped. 
	 * It is compared against a list of bytes, which are used to infer the
	 * following numbers length. 
	 * 
	 * @param	string	$string
	 * @return	int
	 */
	public static function _get_vint(&$string) {
		// Load the next byte, convert to decimal.
		$decimal = hexdec(self::_return_bytes($string, 1));
		// Less than 253: Not encoding extra bytes.
		// More than 253, work out the $number of bytes using the 2^(offset)
		$num_bytes = ( $decimal < 253 ) ? 0 : 2^($decimal-253);
		// Num_bytes is 0: Just return the decimal
		// Otherwise, return $num_bytes bytes (order flipped) and converted to decimal
		return ($num_bytes == 0) ? $decimal : hexdec(self::_return_bytes($string, $num_bytes, TRUE));

	}

	/**
	 * Encode VarInt
	 * Accepts a $decimal number and attempts to encode it to a VarInt.
	 * https://en.bitcoin.it/wiki/Protocol_specification#Variable_length_integer
	 * 
	 * If the number is less than 0xFD/253, then the varint returned
	 *  is the decimal number, encoded as one hex byte.
	 * If larger than this number, then the numbers magnitude determines
	 * a prefix, out of FD, FE, and FF, depending on the number size.
	 * Returns FALSE if the number is bigger than 64bit.
	 * 
	 * @param	int	$decimal
	 * @return	string/FALSE
	 */
	public static function _encode_vint($decimal) {
		$hex = dechex($decimal);
		if($hex < 0xFD) {
			$hint = self::_dec_to_bytes($decimal, 1);
			$num_bytes = 0;
		} else if($hex < 0xFFFF) {
			$hint = 'FD';
			$num_bytes = 2;
		} else if($hex == 0xFFFFFFFF) {
			$hint = 'FE';
			$num_bytes = 4;
		} else if($hex == 0xFFFFFFFFFFFFFFFF) {
			$hint = 'FF';
			$num_bytes = 8;
		} else {
			return FALSE;
		}
		// If the number needs no extra bytes, just return the 1-byte number.
		// If it needs to indicate a larger integer size (16bit, 32bit, 64bit)
		// then it returns the size hint and the 64bit number. 
		return ($num_bytes == 0) ? $hint : $hint.self::_dec_to_bytes($decimal, TRUE);
	}
		
	/**
	 * Decode Script
	 * 
	 * This function accepts a $script (such as scriptSig) and converts it
	 * into an assembled version. Written based on the pybitcointools
	 * transaction.deserialize_script() function.
	 * 
	 * @param	string	$script
	 * @return	string
	 */
	public static function _decode_script($script) {
		$pos = 0;
		$data = array();
		while( $pos < strlen($script) ) {
			$code = hexdec(substr($script, $pos, 2));	 // hex opcode.
			$pos += 2;

			if($code < 1) {
				// OP_FALSE
				$push = '0';
			} else if($code <= 75) {
				// $code bytes will be pushed to the stack. 
				$push = substr($script, $pos, ($code*2));
				$pos += $code*2;
			} else if($code <= 78) {
				// In this range, 2^($code-76) is the number of bytes to take for the *next* number onto the stack.
				$szsz = 2^($code-76); 								// decimal number of bytes.
				$sz = hexdec( substr($script, $pos, ($szsz*2)));	// decimal number of bytes to load and push.
				$pos += $szsz;
				$push = substr($script, $pos, ($pos+$sz*2));		// Load the data starting from the new position.
				$pos += $sz*2;
			} else if($code <= 96) {
				// OP_x, where x = $code-80
				$push = ($code-80);
			} else {
				$push = $code;
			}
			$data[] = $push;
		}
		return implode(" ",$data);
	}
	
	/**
	 * Decode Inputs
	 * 
	 * This function accepts a $raw_transaction by reference, and $input_count,
	 * a decimal number of inputs to extract (learned by calling the get_vint()
	 * function). Returns an array of the construction [vin] if successful,
	 * returns FALSE if an error was encountered.
	 * 
	 * @param	string	$raw_transaction
	 * @param	int	$input_count
	 * @return	array
	 */
	public static function _decode_inputs(&$raw_transaction, $input_count) {
		$inputs = array();
		
		// Loop until $input count is reached, sequentially removing the
		// leading data from $raw_transaction reference.
		for($i = 0; $i < $input_count; $i++) {
			// Check that the variable has at least 36 bytes, and that the 
			// required length for this input is less than the length of the raw_transaction string.
			if( strlen($raw_transaction) < 74
			||	!((hexdec(substr($raw_transaction, 72, 2))+74+8) < strlen($raw_transaction))
				)
				return FALSE;

			// Load the TxID (32bytes) and vout (4bytes)
			$txid = self::_return_bytes($raw_transaction, 32, TRUE);
			$vout = self::_return_bytes($raw_transaction, 4, TRUE);

			// Script is prefixed with a varint that must be decoded.
			$script_length = self::_get_vint($raw_transaction); 		// decimal number of bytes.
			$script = self::_return_bytes($raw_transaction, $script_length);
			// Build input body depending on whether the TxIn is coinbase.
			$input_body = array();
			if($txid == '0000000000000000000000000000000000000000000000000000000000000000') 	// coinbase
				$input_body = array(	'coinbase' => $script);
			else 
				$input_body = array('txid' => $txid,
									'vout' => hexdec($vout),
									'scriptSig' => array('asm' => self::_decode_script($script),
														 'hex' => $script) );
			
			// Append a sequence number, and finally add the input to the array.
			$input_body['sequence'] = hexdec(self::_return_bytes($raw_transaction, 4));
			
			$inputs[$i] = $input_body;
		}
		
		return $inputs;
	}

	/**
	 * Encode Inputs
	 * 
	 * Accepts a decoded $transaction['vin'] array as input: $vin. Also
	 * requires $input count.
	 * This function encodes the txid, vout, and script into hex format.
	 * 
	 * @param	array	$vin
	 * @param	int	$input_count
	 * @return	string
	 */
	public static function _encode_inputs($vin, $input_count) {
		$inputs = '';
		for($i = 0; $i < $input_count; $i++) {
			
			if(isset($vin[$i]['txid'])) {
				// Regular transaction
				$txid = self::_flip_byte_order($vin[$i]['txid']);
				$vout = self::_dec_to_bytes($vin[$i]['vout'], 4, TRUE);
				
				$script_size = strlen($vin[$i]['scriptSig']['hex'])/2;  // decimal number of bytes
				$script_varint = self::_encode_vint($script_size);		// Create the varint encoding scripts length
				$scriptSig = $script_varint.$vin[$i]['scriptSig']['hex'];
				
			} else if( isset($vin[$i]['coinbase'])) {
				// Coinbase
				$txid = '0000000000000000000000000000000000000000000000000000000000000000';
				$vout = 'ffffffff';
				$script_size = strlen($vin[$i]['coinbase'])/2;			// Decimal number of bytes
				$script_varint = self::_encode_vint($script_size);		// Varint
				$scriptSig = $script_varint.$vin[$i]['coinbase'];
			}
			// Add the sequence number.
			$sequence = self::_dec_to_bytes($vin[$i]['sequence'], TRUE);
			
			// Append this encoded input to the byte string.
			$inputs.= $txid.$vout.$scriptSig.$sequence;
		}
		return $inputs;
	}

	/**
	 * Decode scriptPubKey
	 * 
	 * This function takes $script (hex) as an argument, and decodes an 
	 * script hex into an assembled human readable string.
	 * 
	 * @param	string
	 * @return	string
	 */
	public static function _decode_scriptPubKey($script) {
		$data = array();
		while(strlen($script) !== 0) {			
			$byte = self::_return_bytes($script, 1);
			if(isset(self::$op_code[$byte])) {
				// This checks if the OPCODE is defined from the list of constants.
				$data[] = self::$op_code[$byte];
				
			} else if( $byte >= 0x01 && $byte <= 0x4b ) {
				// This checks if the OPCODE falls in the PUSHDATA range
				$data[] = self::_return_bytes($script, hexdec($byte));

			} else if( $byte >= 0x52 && $byte <= 0x60 ) {	
				// This checks if the CODE falls in the OP_X range
				$data[] = 'OP_'.($byte-0x52);
			}
		}
		return implode(" ",$data);
	}

	/**
	 * Get Transaction Type
	 * 
	 * This function takes a $data string, from a decoded scriptPubKey, 
	 * explodes it into an array of the operations/data. Returns FALSE if
	 * the decoded scriptPubKey does not match against the definition of
	 * any type of transaction.
	 * Currently identifies pay-to-pubkey-hash and pay-to-script-hash.
	 * 
	 * Transaction types are defined using the $define array, and
	 * corresponding rules are build using the $rule array. The function
	 * will attempt to create the address based on the transaction type
	 * and $address_version byte.
	 * 
	 * 
	 * @param	string	$data
	 * @param	string	$address_version
	 * @return	array/FALSE
	 */
	public static function _get_transaction_type($data, $address_version = '00') {
		$data = explode(" ", $data);
		
		// Define information about eventual transactions cases, and 
		// the position of the hash160 address in the stack. 
		$define = array();
		$rule = array();
		
		// Standard: pay to pubkey hash
		$define['p2ph'] = array('type' => 'pubkeyhash',
								'reqSigs' => 1,
								'data_index_for_hash' => 2);		
		$rule['p2ph'] = array(
				'0' => '/^OP_DUP/',
				'1' => '/^OP_HASH160/',
				'2' => '/^[0-9a-f]{40}$/i', // 2
				'3' => '/^OP_EQUALVERIFY/',
				'4' => '/^OP_CHECKSIG/' );
								
		// Pay to script hash
		$define['p2sh'] = array('type' => 'scripthash',
								'reqSigs' => 1,
								'data_index_for_hash' => 1);		
		$rule['p2sh'] = array(
				'0' => '/^OP_HASH160/',
				'1' => '/^[0-9a-f]{40}$/i', // pos 1
				'2' => '/^OP_EQUAL/' );
		
		// Work out how many rules are applied in each case
		$valid = array();
		foreach($rule as $tx_type => $def) {
			$valid[$tx_type] = count($def);
		}
		
		// Attempt to validate against each of these rules. 
		foreach($data as $index => $test) {
			foreach($rule as $tx_type => $def) {
				$matches[$tx_type] = array();
				if(isset($def[$index])) {
					preg_match($def[$index], $test, $matches[$tx_type]);
					if(count($matches[$tx_type]) == 1) {
						$valid[$tx_type]--;
						break;
					}
				}
			}	
		}
		
		// Loop through rules, check if any transaction is a match.
		foreach($rule as $tx_type => $def) {
			if($valid[$tx_type] == 0) {
				// Load predefined info for this transaction type if detected.
				$return = $define[$tx_type];
				$return['hash160'] = $data[$define[$tx_type]['data_index_for_hash']];
				if(class_exists('BitcoinLib')) {
					$magic_byte = ($return['type'] == 'scripthash') ? str_pad(gmp_strval(gmp_init($address_version+0x05),16), 2, '0', STR_PAD_LEFT) : $address_version;
					$return['addresses'][0] = BitcoinLib::hash160_to_address($return['hash160'], $magic_byte);
				}
				unset($return['data_index_for_hash']);
			}
		}
		
		return (!isset($return)) ? FALSE : $return;
	}

	/**
	 * Decode Outputs
	 * 
	 * This function accepts $tx - a reference to the raw transaction being
	 * decoded, and $output_count. Also accepts $address_version for when
	 * dealing with networks besides bitcoin.
	 * Returns FALSE if 
	 * @param	string	$tx
	 * @param	int		$output_count
	 * @param	string	$address_version
	 * @return	array/FALSE
	 */
	public static function _decode_outputs(&$tx, $output_count, $address_version = '00') {
			
		$outputs = array();
		for($i = 0; $i < $output_count; $i++) { 
			// Check the $tx has sufficient length to cover this input.
			if( strlen($tx) < 8
			||	!((hexdec(substr($tx, 8, 2))+8+2) < strlen($tx))
				)
				return FALSE;
			
			// Pop 8 bytes (flipped) from the $tx string, convert to decimal,
			// and then convert to Satoshis.
			$satoshis = base_convert(self::_return_bytes($tx, 8, TRUE), 16, 10);
			$amount = number_format($satoshis/1e8, 8);
			
			// Decode the varint for the length of the scriptPubKey
			$script_length = self::_get_vint($tx); // decimal number of bytes
			$script = self::_return_bytes($tx, $script_length);
			
			// Begin building scriptPubKey
			$scriptPubKey = array(	'asm'=> self::_decode_scriptPubKey($script),
									'hex' => $script	);
			
			// Try to decode the scriptPubKey['asm'] to learn the transaction type.
			$txn_info = self::_get_transaction_type($scriptPubKey['asm'], $address_version);
			if($txn_info !== FALSE)
				$scriptPubKey = array_merge($scriptPubKey, $txn_info);
			else 
				$scriptPubKey['message'] = 'unable to decode tx type!';

			$outputs[$i] = array('value' => $amount,
								'vout' => $i, 
								'scriptPubKey' => $scriptPubKey);
			
		}
		return $outputs;
	}
	
	/**
	 * Encode Outputs
	 * 
	 * This function encodes $tx['vin'] array into hex format. Requires
	 * the $vout_arr, and also $output_count - the number of outputs
	 * this transaction has.
	 * 
	 * @param	array	
	 * @param	int
	 * @return	string/FALSE
	 */
	public static function _encode_outputs($vout_arr, $output_count) {
		// If $vout_arr is empty, check if it's MEANT to be before failing.
		if(count($vout_arr) == 0) 
			return ($output_count == 0) ? '' : FALSE;		
		
		$outputs = '';
		for($i = 0; $i < $output_count; $i++) {
			$satoshis = $vout_arr[$i]['value']*1e8;
			$amount = self::_dec_to_bytes($satoshis, 8);
			$amount = self::_flip_byte_order($amount);
			if($amount > 0xFFFFFFFFFFFFFFFF)
				return FALSE;
				
			$script_size = strlen($vout_arr[$i]['scriptPubKey']['hex'])/2; // number of bytes
			$script_varint = self::_encode_vint($script_size);
			$scriptPubKey = $vout_arr[$i]['scriptPubKey']['hex'];
			
			$outputs .= $amount.$script_varint.$scriptPubKey;
		}
		return $outputs;
	}
	
	/**
	 * Decode
	 * 
	 * A high-level function which takes $raw_transaction hex, and decodes
	 * it into an array similar to that returned by bitcoind. 
	 * Accepts an optional $address_version for creating the addresses 
	 * - defaults to bitcoins version byte.
	 * 
	 * @param	string	$raw_tranasction
	 * @param	string	$address_version
	 */
	public static function decode($raw_transaction, $address_version = '00') {
		$raw_transaction = trim($raw_transaction);
		if( ((bool)preg_match('/^[0-9a-fA-F]{2,}$/i', $raw_transaction) !== TRUE)
		||	(strlen($raw_transaction))%2 !== 0)
			return false;
			
		$txid = hash('sha256', hash('sha256', pack("H*",trim($raw_transaction)), TRUE));
		
		$info = array();
		$info['txid'] = $txid;
		$info['version'] = gmp_strval(gmp_init(self::_return_bytes($raw_transaction, 4, TRUE), 16), 10);
		if(!in_array($info['version'], array('1')))
			return FALSE;
		
		$input_count = self::_get_vint($raw_transaction);
		if( !($input_count >= 0 && $input_count <= 4294967296))
			return FALSE;
		
		$info['vin'] = self::_decode_inputs($raw_transaction, $input_count);
		if($info['vin'] == FALSE)
			return FALSE;

		$output_count = self::_get_vint($raw_transaction);
		if( !($output_count >= 0 && $output_count <= 4294967296))
			return FALSE;
			
		$info['vout'] = self::_decode_outputs($raw_transaction, $output_count, $address_version);
			
		$info['locktime'] = hexdec(self::_return_bytes($raw_transaction, 4));
		return $info;
	}
 
	/**
	 * Encode
	 * 
	 * This function takes an array in a format similar to bitcoind's 
	 * (and compatible with the output of debug above) and re-encodes it
	 * into a raw transaction hex string.
	 * 
	 * @param	array	$raw_transaction_array
	 * @return	string
	 */
	public static function encode($raw_transaction_array) {
		// $encoded_version
		$encoded_version = $bytes = self::_dec_to_bytes($raw_transaction_array['version'], 4, TRUE); // TRUE - get little endian

		// $encoded_inputs - set the encoded varint, then work out if any input hex is to be displayed.
		$decimal_inputs_count = count($raw_transaction_array['vin']);
		$encoded_inputs = self::_encode_vint($decimal_inputs_count) . (($decimal_inputs_count > 0) ? self::_encode_inputs($raw_transaction_array['vin'], $decimal_inputs_count) : '');
		
		// $encoded_outputs - set varint, then work out if output hex is required.
		$decimal_outputs_count = count($raw_transaction_array['vout']);
		$encoded_outputs = self::_encode_vint($decimal_outputs_count) . (($decimal_inputs_count > 0) ? self::_encode_outputs($raw_transaction_array['vout'], $decimal_outputs_count) : '');
		
		// Transaction locktime
		$encoded_locktime = self::_dec_to_bytes($raw_transaction_array['locktime'], 4, TRUE);
		
		return $encoded_version.$encoded_inputs.$encoded_outputs.$encoded_locktime;

	}
	
	/**
	 * Create Signature Hash
	 * 
	 * This function accepts a $raw_transaction hex, and generates a hash
	 * for each input, against which a signature and public key can be 
	 * verified. 
	 * See https://en.bitcoin.it/w/images/en/7/70/Bitcoin_OpCheckSig_InDetail.png
	 * 
	 * If $specific_input is not set, then a hash will be generated for
	 * each input, and these values returned as an array for comparison
	 * during another script.
	 * 
	 * @param	string	$raw_transaction
	 * @return	string
	 */
	public static function _create_txin_signature_hash($raw_transaction, $json_inputs, $specific_input = -1, $e = NULL) {
		$decode = ($e == NULL) ? self::decode($raw_transaction) : $e;
		$inputs = (array)json_decode($json_inputs);
		if($specific_input !== -1 && !is_numeric($specific_input))
			return FALSE;
			
		// Check that $raw_transaction and $json_inputs correspond to the right inputs
		for( $i = 0; $i < count($decode['vin']); $i++ ) {
			if(!isset($inputs[$i]))
				return FALSE;
			if( $decode['vin'][$i]['txid'] !== $inputs[$i]->txid ||
				$decode['vin'][$i]['vout'] !== $inputs[$i]->vout )
				return FALSE;
		}

		$sighashcode = '01000000';

		if( $specific_input == -1) {
			// Return a hash for each input.
			$hash = array();
			foreach($decode['vin'] as $vin => $input) {
				$copy = $decode;
				// Substitute the input script with the outpoints script.
				$script = $copy['vin'][$vin]['scriptSig']['hex'];
				unset($copy['vin'][$vin]['scriptSig']['asm']);
				
				$copy['vin'][$vin]['scriptSig']['hex'] = (isset($inputs[$vin]->redeemScript)) ? $inputs[$vin]->redeemScript : $inputs[$vin]->scriptPubKey;
				
				// Encode the transaction, convert to a raw byte sting, 
				// and calculate a double sha256 hash for this input.
				$hash[] = hash('sha256', hash('sha256', pack("H*", self::encode($copy).$sighashcode), TRUE));
			}
		} else {
			$hash = '';
			// Return a message hash for the specified output.
			$copy = $decode;
			$copy['vin'][$specific_input]['scriptSig']['hex'] = (isset($inputs[$specific_input]->redeemScript)) ? $inputs[$specific_input]->redeemScript : $inputs[$vin]->scriptPubKey;
			$hash = hash('sha256', hash('sha256', pack("H*", self::encode($copy).$sighashcode), TRUE));
		}
		return $hash;
	}

	/**
	 * Check Sig
	 * 
	 * This function will check a provided DER encoded $sig, a digest of
	 * the message to be signed - $hash (the output of _create_txin_signature_hash()),
	 * and the $key for the signature to be tested against. 
	 * Returns TRUE if the signature is valid for this $hash and $key, 
	 * otherwise returns FALSE.
	 * 
	 * @param	string	$sig
	 * @param	string	$hash
	 * @param	string	$key
	 * @return	boolean
	 */
	public static function _check_sig($sig, $hash, $key) {
		$signature = self::decode_signature($sig);
		$test_signature = new Signature(gmp_init($signature['r'],16), gmp_init($signature['s'],16));
		$generator = SECcurve::generator_secp256k1();
		$curve = $generator->getCurve();
			
		if(strlen($key) == '66') {
			$decompress = BitcoinLib::decompress_public_key($key);
			$public_key_point = $decompress['point'];
		} else {
			$x = gmp_strval(gmp_init(substr($key, 2, 64), 16), 10);
			$y = gmp_strval(gmp_init(substr($key, 66, 64), 16), 10);
			$public_key_point = new Point($curve, $x, $y, $generator->getOrder());
		}
		$public_key = new PublicKey($generator, $public_key_point);
		$hash = gmp_init($hash, 16);
		
		return ($public_key->verifies($hash, $test_signature) == TRUE) ? TRUE : FALSE;

	}
	
	/**
	 * Decode Redeem Script
	 * 
	 * This recursive function extracts the m and n values for the 
	 * multisignature address, as well as the public keys.
	 * 
	 * @param	string	$redeem_script
	 * @param	array	$data(should not be set!)
	 * @return	array
	 */
	public static function decode_redeem_script($redeem_script, $data = array()) {
		// If there is no more work to be done (script is fully parsed, 
		// return the array)
		if(strlen($redeem_script) == 0)
			return $data;
			
		// Fail if the redeem_script has an uneven number of characters.
		if(strlen($redeem_script) % 2 !== 0)
			return FALSE;
			
		// First step is to get m, the required number of signatures
		if(!isset($data['m']) || count($data) == 0) {
			$data['m'] = gmp_strval(gmp_sub(gmp_init(substr($redeem_script, 0, 2),16),gmp_init('50',16)),10);							
			$data['keys'] = array();
			$redeem_script = substr($redeem_script, 2);
			
		} else if(count($data['keys']) == 0 && !isset($data['next_key_charlen'])) {
			// Next is to find out the length of the following public key.
			$hex = substr($redeem_script, 0, 2);
			// Set up the length of the following key.
			$data['next_key_charlen'] = gmp_strval(gmp_mul(gmp_init('2',10),gmp_init($hex, 16)),10);
			$redeem_script = substr($redeem_script, 2);
			
		} else if(isset($data['next_key_charlen'])) {
			// Extract the key, and work out the next step for the code.
			$data['keys'][] = substr($redeem_script, 0, $data['next_key_charlen']);
			$next_op = substr($redeem_script, $data['next_key_charlen'], 2);
			$redeem_script = substr($redeem_script, ($data['next_key_charlen']+2));
			unset($data['next_key_charlen']);
			
			// If 1 <= $next_op >= 4b
			if( in_array(gmp_cmp(gmp_init($next_op, 16),gmp_init('1',16)),array('0','1')) 
			 && in_array(gmp_cmp(gmp_init($next_op, 16),gmp_init('4b', 16)),array('-1','0'))) {
				// Set the next key character length
				$data['next_key_charlen'] = gmp_strval(gmp_mul(gmp_init('2',10),gmp_init($next_op, 16)),10);
			
			// If 52 <= $next_op >= 60
			} else if( in_array(gmp_cmp(gmp_init($next_op, 16),gmp_init('52',16)),array('0','1')) 
					&& in_array(gmp_cmp(gmp_init($next_op, 16),gmp_init('60', 16)),array('-1','0'))) {
				// Finish the script.
				$data['n'] = gmp_strval(gmp_sub(gmp_init($next_op, 16),gmp_init('50',16)),10);
				$redeem_script = '';
			} else {
				// Something weird, malformed redeemScript.
				return FALSE;
			}
		} 
		return self::decode_redeem_script($redeem_script, $data);
	}

	/**
	 * Create Redeem Script
	 * 
	 * This function creates a hex encoded redeemScript, by setting $m, 
	 * the number of signatures required for redemption, and the public
	 * keys involved in the address. Returns FALSE if no public keys are 
	 * supplied, or $m is zero. Otherwise returns a string containing
	 * the redeemScript.
	 * 
	 * @param	int	$m
	 * @param	array	$public_keys
	 * @return	string/FALSE
	 */
	public static function create_redeem_script($m, $public_keys = array()) {
		if(count($public_keys) == 0)
			return FALSE;
		if($m == 0)
			return FALSE;
			
		$redeemScript = dechex(0x50+$m);
		foreach($public_keys as $public_key) {
			$redeemScript .= dechex(strlen($public_key)/2).$public_key;
		}
		$redeemScript .= dechex(0x50+(count($public_keys))).'ae';
		return $redeemScript;
	}

	/**
	 * Create Multisig
	 * 
	 * This function mirrors that of Bitcoind's. It creates a redeemScript
	 * out of keys given in the given order, creates a redeemScript, and
	 * creates the address from this. $m must be greater than zero, and 
	 * public keys are required. 
	 * 
	 * @param	int	$m
	 * @param	array	$public_keys
	 */
	public static function create_multisig($m, $public_keys = array()) {
		if($m == 0)
			return FALSE;
		if(count($public_keys) == 0)
			return FALSE;
			
		$redeem_script = self::create_redeem_script($m, $public_keys);
		if($redeem_script == FALSE)
			return FALSE;
			
		return array('redeemScript' => $redeem_script,
					 'address' => BitcoinLib::public_key_to_address($redeem_script, '05'));
	}


	/**
	 * Validate Signed Transaction
	 * 
	 * Pass a decoded $transaction, the $raw_tx as a reference and to
	 * process into a hash when needed, the $json_inputs for the created
	 * transaction (stored), and the $address_version ('00' for bitcoin 
	 * default)
	 * 
	 * @param	string	$raw_tx
	 * @param	string	$json_string
	 * @param	string	$address_version.
	 * @param	boolean
	 */
	public static function validate_signed_transaction( $raw_tx, $json_string, $address_version = '00' ) {
		$decode = self::decode($raw_tx, $address_version);
		if($decode == FALSE)
			return FALSE;
			
		$json_arr = (array) json_decode($json_string);

		$message_hash = self::_create_txin_signature_hash($raw_tx, $json_string);		
		$outcome = TRUE;
		foreach($decode['vin'] as $i => $vin) {
			// Decode previous scriptPubKey to learn trasaction type.
			$type_info = self::_get_transaction_type(self::_decode_scriptPubKey($json_arr[$i]->scriptPubKey));
			
			if($type_info['type'] == 'pubkeyhash') {
				// Pay-to-pubkey-hash. Check one <sig> <pubkey> 
				$scripts = explode(" ", $vin['scriptSig']['asm']);
				$signature = $scripts[0];
			
				$public_key = $scripts[1];
				$o = self::_check_sig($signature, $message_hash[$i], $public_key);
				$outcome = $outcome && $o;
				
			} else if($type_info['type'] == 'scripthash') {
				// Pay-to-script-hash. Check OP_FALSE <sig> ... <redeemScript>
				$redeem_script_found = FALSE;
				$pubkey_found = FALSE;
				
				$scripts = explode(" ", $vin['scriptSig']['asm']);

				// Store the redeemScript, then remove OP_FALSE + the redeemScript from the array.
				$redeemScript = self::decode_redeem_script($scripts[(count($scripts)-1)]);
				if($redeemScript !== FALSE)				// Die if we fail to decode a redeemScript from a P2SH
					$redeem_script_found = TRUE;
					
				unset($scripts[(count($scripts)-1)]);	// Unset redeemScript
				unset($scripts[0]); 					// Unset '0';

				// Extract signatures, remove the "0" byte, and redeemScript.
				// Loop through the remaining values - the signatures
				$pubkey_found = FALSE;
				foreach($scripts as $signature) {
					// Test each signature with the public keys in the redeemScript.
					foreach($redeemScript['keys'] as $public_key) {
						if(self::_check_sig($signature, $message_hash[$i], $public_key) == TRUE)
							$pubkey_found = TRUE;
					}
				}
				$outcome = $outcome && ( $redeem_script_found && $pubkey_found );
			}
		}
		return $outcome;
	}

	/**
	 * Create
	 * 
	 * This function creates a raw transaction from an array of inputs, 
	 * and an array of outputs. It takes essentially the same data is
	 * bitcoind's createrawtransaction function.
	 * 
	 * Inputs: Each input is a child array of [txid, vout, and optionally a sequence number.]
	 * Outputs: Each output is a key in the array: address => $value.
	 * 
	 * @param	array	$inputs
	 * @param	array	$outputs
	 * @return	string/FALSE
	 */
	public static function create($inputs, $outputs, $magic_byte = '00') {

		// Generate the p2sh byte from the regular byte.
		$regular_byte = $magic_byte;
		$p2sh_byte = (string)($magic_byte+0x5); 		$p2sh_byte = ((strlen($p2sh_byte) == '1') ? '0' : '').$p2sh_byte;
		
		$tx_array = array('version' => '1');
		
		// Inputs is the set of [txid/vout/scriptPubKey]
		$tx_array['vin'] = array();
		foreach($inputs as $input) {
			if(!isset($input['txid']) || strlen($input['txid']) !== 64
			|| !isset($input['vout']) || !is_numeric($input['vout']))
				return FALSE;
			
			$tx_array['vin'][] = array(	'txid' => $input['txid'],
								'vout' => $input['vout'],
								'sequence' => (isset($input['sequence'])) ? $input['sequence'] : 4294967295,
								'scriptSig' => array('hex' => '')
							);
		}
		
		// Outputs is the set of [address/amount]
		$tx_array['vout'] = array();
		foreach($outputs as $address => $value) {
			if(BitcoinLib::validate_address($address, $regular_byte) == FALSE
			&& BitcoinLib::validate_address($address, $p2sh_byte) == FALSE)
				return FALSE;
				
			$decode_address = BitcoinLib::base58_decode($address);
			$version = substr($decode_address, 0, 2);
			$hash = substr($decode_address, 2, 40);
			
			if($version == $regular_byte) {
				// OP_DUP OP_HASH160 <pubKeyHash> OP_EQUALVERIFY OP_CHECKSIG 
				$scriptPubKey['hex'] = "76a914{$hash}88ac"; 
			} else if($version == $p2sh_byte) {
				// OP_HASH160 <scriptHash> OP_EQUAL
				$scriptPubKey['hex'] = "a914{$hash}87";
			} 
			
			$tx_array['vout'][] = array('value' => $value,
										'scriptPubKey' => array('hex' => $scriptPubKey['hex'])
										);
		}
		
		$tx_array['locktime'] = 0;
		return self::encode($tx_array);
		
	}
	
	/**
	 * Sign
	 * 
	 * This function accepts the same parameters as signrawtransaction.
	 * $raw_transaction is a hex encoded string for an unsigned/partially 
	 * signed transaction. $inputs is an array, containing the txid/vout/
	 * scriptPubKey/redeemscript. $priv_keys contains WIF keys. 
	 * 
	 * The function looks at each TxIn and tries to sign, if the hash160
	 * belongs to a key specified in the wallet.
	 * 
	 * @param	string	$raw_transaction
	 * @param	array	$inputs
	 * @param	array	$priv_keys
	 * @param	string	$magic_byte
	 * @return	array
	 */
	public static function sign($raw_transaction, $inputs, array $priv_keys = array(), $magic_byte = '00')
	{
		// Generate digests of inputs to sign.
		$message_hash = self::_create_txin_signature_hash($raw_transaction, $inputs);
	
		$inputs_arr = (array)json_decode($inputs);
		
		// Generate an association of expected hash160's and related information.
		$wallet = BitcoinLib::private_keys_to_receive($priv_keys);
		$decode = self::decode($raw_transaction);	
		
		$sign_count = 0;
		foreach($decode['vin'] as $vin => $input ) {
			
			$scriptPubKey = self::_decode_scriptPubKey($inputs_arr[$vin]->scriptPubKey);
			$tx_info = self::_get_transaction_type($scriptPubKey, $magic_byte);

			if(isset($wallet[$tx_info['hash160']])) {	
				$key_info = $wallet[$tx_info['hash160']];
				$generator = SECcurve::generator_secp256k1();
				$point = new Point($generator->getCurve(), gmp_init(substr($key_info['uncompressed_key'], 2, 64), 16), gmp_init(substr($key_info['uncompressed_key'], 66, 64), 16), $generator->getOrder());
				
				// Create Signature
				$_public_key = new PublicKey($generator, $point);
				$_private_key = new PrivateKey($_public_key, gmp_init($key_info['private_key'], 16));
				$sign = $_private_key->sign(gmp_init($message_hash[$vin],16),  gmp_init((string)bin2hex(openssl_random_pseudo_bytes(32)), 16));
				if($sign !== FALSE) {
					$sign_count++;
					$decode['vin'][$vin]['scriptSig']['hex'] = self::encode_signature($sign, $tx_info, $key_info);
				}
			}
		}
		$new_raw = self::encode($decode);
		// If the transaction isn't fully signed, return false.
		// If it's fully signed, perform signature verification, return true if valid, or invalid if signatures are incorrect.
		$complete = (((count($decode['vin'])-$sign_count) == '0') ? ((self::validate_signed_transaction($new_raw, $inputs, $magic_byte) == TRUE) ? 'true' : 'invalid') : 'false');
			
		return array('hex' => $new_raw,
					 'complete' => $complete);
	}


	/**
	 * Encode Signature
	 * 
	 * This function accepts a signature object, and information about 
	 * the txout being spent, and the relevant key for signing, and
	 * encodes the signature in DER format.
	 * 
	 * @param	Signature	$signature
	 * @param	array	$tx_info
	 * @param	array	$key_info
	 * @return	string
	 */
	public static function encode_signature(Signature $signature, $tx_info, $key_info) {
		
		// Pad r and s to 64 characters.
		$rh = str_pad(BitcoinLib::hex_encode($signature->getR()),64,'0', STR_PAD_LEFT);
		$sh = str_pad(BitcoinLib::hex_encode($signature->getS()),64,'0', STR_PAD_LEFT);
		
		// Check if the first byte of each has its highest bit set, 
		$t1 = unpack( "H*", (pack( 'H*',substr($rh, 0, 2)) & pack('H*', '80')));
		$t2 = unpack( "H*", (pack( 'H*',substr($sh, 0, 2)) & pack('H*', '80')));
		// if so, the result != 00, and must be padded.
		$r = ($t1[1] !== '00') ? '00'.$rh : $rh;
		$s = ($t2[1] !== '00') ? '00'.$sh : $sh;
		
		// Create the signature.
		$der_sig =  '30'
					. self::_dec_to_bytes( (4+((strlen($r)+strlen($s))/2)), 1) //((strlen($r)+strlen($s)+16)/2),1)
					.'02'
					. self::_dec_to_bytes(strlen($r)/2,1)
					. $r
					. '02'
					. self::_dec_to_bytes(strlen($s)/2,1)
					. $s
					. '01';
		// Append the length of the signature.
		$der_sig =  self::_dec_to_bytes( strlen($der_sig)/2, 1)
					. $der_sig;

		if($tx_info['type'] == 'pubkeyhash') {
			// Need to append the public key. 
			$scriptSig = $der_sig
					. self::_dec_to_bytes( strlen($key_info['public_key'])/2, 1)
					. $key_info['public_key'];
		} else if($tx_info['type'] == 'scripthash') {
			// Reorder signatures and return.
			// not done yet!
			$redeemScript = self::_dec_to_bytes( strlen($inputs_arr[$vin]->redeemScript)/2, 1);
			$scriptSig = '';
		}
		
		return $scriptSig;
	}

	/**
	 * Decode Signature
	 * 
	 * This function extracts the r and s parameters from a DER encoded
	 * signature. No checking on the validity of the numbers. 
	 * 
	 * @param	string	$signature
	 * @return	array
	 */
	public static function decode_signature($signature) {
		$r_start = 8;
		$r_length = hexdec(substr($signature, 6, 2))*2;
		$r_end = $r_start+$r_length;
		$r = substr($signature, $r_start, $r_length);
		
		$s_start = $r_end+4;
		$s_length = hexdec(substr($signature, ($r_end+2), 2))*2;
		$s = substr($signature, $s_start, $s_length);
		return array('r' => $r, 
					 's' => $s,
					 'hash_type' => substr($signature, -2),
					 'last_byte_s' => substr($s, -2));
	}
	
};

