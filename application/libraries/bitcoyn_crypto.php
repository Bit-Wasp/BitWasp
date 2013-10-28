<?php


require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');
// configure the ECC lib
if (!defined('USE_EXT')) {
    if (extension_loaded('gmp')) {
        define('USE_EXT', 'GMP');
    } else if(extension_loaded('bcmath')) {
        define('USE_EXT', 'BCMATH');
    } else {
        die('GMP or bcmath required. (GMP is faster).');
        // TODO I shouldn't be depending on bcmath for bcmath_Utils since GMP is faster...
    }
}
define('MAX_BASE', 256); // so we can use bcmath_Utils::bin2bc with "base256"

class Bitcoyn_Crypto {

	public function generate(){
		$secp256k1 = new CurveFp(
			'115792089237316195423570985008687907853269984665640564039457584007908834671663',
			'0', '7');
		$secp256k1_G = new Point($secp256k1,
			'55066263022277343669578718895168534326250603453777594175500187360389116729240',
			'32670510020758816978083085130507043184471273380659243275938904335757337482424',
			'115792089237316195423570985008687907852837564279074904382605163141518161494337');


		// generate a keypair and output in base58/WIF
		$keyPair = $this->create_key_pair();

		echo 'Public: ', $this->base58check_encode(0x00, $keyPair['public']), "\n";
		echo 'Private: ', $this->base58check_encode(0x80, $keyPair['private']), "\n";
		echo "\n";
		echo 'Public (compressed): ', $this->base58check_encode(0x00, $keyPair['public_compressed']), "\n";
		echo 'Private (compressed): ', $this->base58check_encode(0x80, $keyPair['private'], 0x01), "\n";
	}
	
	
	
	function create_key_pair() {
		global $secp256k1, $secp256k1_G;

		// generate 256 random bits into a binary string
		// ** WARNING ** For critical use, make sure the random function you use is sufficiently pseudo-random! ** WARNING **
		$privBin = '';
		for ($i = 0; $i < 32; $i++) {
			// "it is inefficient to use larger values than the order of G in the ec group."
			// - https://bitcointalk.org/index.php?topic=91706.msg1010185#msg1010185
			// I am not sure how important this is, but I make the first byte less than 0xff
			// as an easy way to be on the safe side. Thanks to Tobias Wiersch for the suggestion.
			$privBin .= chr(openssl_random_pseudo_bytes(1));
		}

		$point = Point::mul(bcmath_Utils::bin2bc("\x00" . $privBin), $secp256k1_G);
		var_dump(bcmath_Utils::bin2bc("\x00".$privBin));
		
		$pubBinStr = "\x04" . str_pad(bcmath_Utils::bc2bin($point->getX()), 32, "\x00", STR_PAD_LEFT) .
							  str_pad(bcmath_Utils::bc2bin($point->getY()), 32, "\x00", STR_PAD_LEFT);

		$pubBinStrCompressed = (intval(substr($point->getY(), -1, 1)) % 2 == 0 ? "\x02" : "\x03") .
							  str_pad(bcmath_Utils::bc2bin($point->getX()), 32, "\x00", STR_PAD_LEFT);

		return array('public' => hash('ripemd160', hash('sha256', $pubBinStr, true), true), 'private' => $privBin,
			'public_compressed' => hash('ripemd160', hash('sha256', $pubBinStrCompressed, true), true));
	}
	
	public function bin2bc($input, $base = MAX_BASE){
		$input = gmp_init($input);
		$result = array();
		for($i=0; $i<1 || gmp_sign($input) == 1; $i++){
			$result[] = gmp_intval(gmp_mod($input, $base));
			$input = gmp_div_q($input, $base);
		
		}
		$result = array_reverse($result);
		return $result;
	}

	// https://en.bitcoin.it/wiki/Base58Check_encoding
	function base58check_encode($leadingByte, $bin, $trailingByte = null) {
		$bin = chr($leadingByte) . $bin;
		if ($trailingByte !== null) {
			$bin .= chr($trailingByte);
		}

		$checkSum = substr(hash('sha256', hash('sha256', $bin, true), true), 0, 4);
		$bin .= $checkSum;

		$base58 = $this->base58_encode(bcmath_Utils::bin2bc($bin));

		// for each leading zero-byte, pad the base58 with a "1"
		for ($i = 0; $i < strlen($bin); $i++) {
			if ($bin[$i] != "\x00") {
				break; // <-- exit;
			}
			$base58 = '1' . $base58;
		}

		return $base58;
	}

  public static function base58_encode($hex) {
    if (strlen($hex) % 2 != 0) {
      throw new Exception("encodeBase58: uneven number of hex characters");
    }
    $orighex = $hex;

    $hex = self::decodeHex($hex);
    $return = "";
    while (bccomp($hex, 0) == 1) {
      $dv = (string) bcdiv($hex, "58", 0);
      $rem = (integer) bcmod($hex, "58");
      $hex = $dv;
      $return = $return . self::$base58chars[$rem];
    }
    $return = strrev($return);

    //leading zeros
    for ($i = 0; $i < strlen($orighex) && substr($orighex, $i, 2) == "00"; $i += 2) {
      $return = "1" . $return;
    }

    return $return;
  }
}
