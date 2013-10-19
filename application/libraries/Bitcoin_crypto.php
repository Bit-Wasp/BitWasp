<?php
/**
 * Bitcoin Crypto Library
 * 
* Bitcoin utility functions class (extended for sci lib). Edited 
* to detect whether BitWasp is running in the testnet. 
*
* @author theymos (functionality)
* @author Mike Gogulski (http://www.gogulski.com/)
* @author Jacob Bruce (private/public/mini key generation)
* (encapsulation, string abstraction, PHPDoc)
* @author BitWasp
*/
require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');

/** 
 * Determine bitcoin address version
 */
$CI = &get_instance();
$CI->load->library('bw_bitcoin');
$bitcoin_info = $CI->bw_bitcoin->getinfo();
$byte = ($bitcoin_info['testnet'] == TRUE) ? "6F" : "00";
define("BITCOIN_ADDRESS_VERSION", $byte);// this is a hex byte

class Bitcoin_crypto {

  /*
* Bitcoin utility functions by theymos
* hex input must be in uppercase, with no leading 0x
*/
  private static $hexchars = "0123456789ABCDEF";
  private static $base58chars = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
  
  /**
* Remove leading "0x" from a hex value if present.
*
* @param string $string
* @return string
* @access public
*/
  public static function remove0x($string) {
    if (substr($string, 0, 2) == "0x" || substr($string, 0, 2) == "0X") {
      $string = substr($string, 2);
    }
    return $string;
  }
  
  /**
* Generate a random string from base58 alphabet
*
* @param integer $length
* @return string
* @access public
*/
  public static function randomString($length=16) {
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
       $randomString .= self::$base58chars[mt_rand(0, strlen(self::$base58chars)-1)];
    }
    return $randomString;
  }

  /**
* Convert a hex string into a (big) integer
*
* @param string $hex
* @return int
* @access private
*/
  private static function decodeHex($hex) {
    $hex = strtoupper($hex);
    $return = "0";
    for ($i = 0; $i < strlen($hex); $i++) {
      $current = (string) strpos(self::$hexchars, $hex[$i]);
      $return = (string) bcmul($return, "16", 0);
      $return = (string) bcadd($return, $current, 0);
    }
    return $return;
  }

  /**
* Convert an integer into a hex string
*
* @param int $dec
* @return string
* @access private
*/
  private static function encodeHex($dec) {
    $return = "";
    while (bccomp($dec, 0) == 1) {
      $dv = (string) bcdiv($dec, "16", 0);
      $rem = (integer) bcmod($dec, "16");
      $dec = $dv;
      $return = $return . self::$hexchars[$rem];
    }
    return strrev($return);
  }

  /**
* Convert a Base58-encoded integer into the equivalent hex string representation
*
* @param string $base58
* @return string
* @access private
*/
  public static function decodeBase58($base58) {
    $origbase58 = $base58;

    $return = "0";
    for ($i = 0; $i < strlen($base58); $i++) {
      $current = (string) strpos(self::$base58chars, $base58[$i]);
      $return = (string) bcmul($return, "58", 0);
      $return = (string) bcadd($return, $current, 0);
    }

    $return = self::encodeHex($return);

    //leading zeros
    for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
      $return = "00" . $return;
    }

    if (strlen($return) % 2 != 0) {
      $return = "0" . $return;
    }

    return $return;
  }

  /**
* Convert a hex string representation of an integer into the equivalent Base58 representation
*
* @param string $hex
* @return string
* @access private
*/
  public static function encodeBase58($hex) {
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

  /**
* Convert a 160-bit Bitcoin hash to a Bitcoin address
*
* @author theymos
* @param string $hash160
* @param string $addressversion
* @return string Bitcoin address
* @access public
*/
  public static function hash160ToAddress($hash160, $addressversion = BITCOIN_ADDRESS_VERSION) {
    $hash160 = $addressversion . $hash160;
    $check = @pack("H*", $hash160);
    $check = hash("sha256", hash("sha256", $check, true));
    $check = substr($check, 0, 8);
    $hash160 = strtoupper($hash160 . $check);
    return self::encodeBase58($hash160);
  }

  /**
* Convert a Bitcoin address to a 160-bit Bitcoin hash
*
* @author theymos
* @param string $addr
* @return string Bitcoin hash
* @access public
*/
  public static function addressToHash160($addr) {
    $addr = self::decodeBase58($addr);
    $addr = substr($addr, 2, strlen($addr) - 10);
    return $addr;
  }

  /**
* Determine if a string is a valid Bitcoin address
*
* @author theymos
* @param string $addr String to test
* @param string $addressversion
* @return boolean
* @access public
*/
  public static function checkAddress($addr, $addressversion = BITCOIN_ADDRESS_VERSION) {
    $addr = self::decodeBase58($addr);
    if (strlen($addr) != 50) {
      return false;
    }
    $version = substr($addr, 0, 2);
    if (hexdec($version) > hexdec($addressversion)) {
      return false;
    }
    $check = substr($addr, 0, strlen($addr) - 8);
    $check = @pack("H*", $check);
    $check = strtoupper(hash("sha256", hash("sha256", $check, true)));
    $check = substr($check, 0, 8);
    return $check == substr($addr, strlen($addr) - 8);
  }

  /**
* Convert the input to its 160-bit Bitcoin hash
*
* @param string $data
* @return string
* @access private
*/
  public static function hash160($data) {
    $data = @pack("H*", $data);
    return strtoupper(hash("ripemd160", hash("sha256", $data, true)));
  }

  /**
* Convert a Bitcoin public key to a 160-bit Bitcoin hash
*
* @param string $pubkey
* @return string
* @access public
*/
  public static function pubKeyToAddress($pubkey, $addressversion = BITCOIN_ADDRESS_VERSION) {
    return self::hash160ToAddress(self::hash160($pubkey), $addressversion);
  }
  
  /**
* Get public key from a private key
*
* @author Jacob Bruce
* @param string $privKey
* @return string
* @access public
*/
  public static function privKeyToPubKey($privKey) {
	  
    $g = SECcurve::generator_secp256k1();
    
	$privKey = self::decodeHex($privKey);  
    $secretG = Point::mul($privKey, $g);
	
	$xHex = self::encodeHex($secretG->getX());  
	$yHex = self::encodeHex($secretG->getY());

	$xHex = str_pad($xHex, 64, '0', STR_PAD_LEFT);
	$yHex = str_pad($yHex, 64, '0', STR_PAD_LEFT);
	  
	return '04'.$xHex.$yHex;
  }
  
  /**
* Get bitcoin address from a private key
*
* @author Jacob Bruce
* @param string $privKey
* @return string
* @access public
*/
  public static function privKeyToAddress($privKey) {

	$pubKey = self::privKeyToPubKey($privKey);
	$pubAdd = self::pubKeyToAddress($pubKey, BITCOIN_ADDRESS_VERSION);
	  
	if (self::checkAddress($pubAdd)) { 
	  return $pubAdd; 
	} else { 
	  return 'invalid pub address'; 
	}
  }
  
  /**
* Generate a new private key
*
* @author Jacob Bruce
* @return string
* @access public
*/
  public static function getNewPrivKey() {

    $g = SECcurve::generator_secp256k1();
    $n = $g->getOrder();
	
    do {
      if (extension_loaded('gmp') && USE_EXT == 'GMP') {
        $privKey = gmp_Utils::gmp_random($n);
      } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
        $privKey = bcmath_Utils::bcrand(1, $n);
	  }
      $privKeyHex = self::encodeHex($privKey);
	
	} while (($privKey < 2E+11) || strlen($privKeyHex) > 64);
	
	return str_pad($privKeyHex, 64, '0', STR_PAD_LEFT);
  }
  
  /**
* Generate a new pair of public and private keys
*
* @author Jacob Bruce
* @return associative array ('privKey', 'PubKey') 
* @access public
*/
  public static function getNewKeyPair() {
  
	$privKey = self::getNewPrivKey(); 
	$pubKey = self::privKeyToPubKey($privKey);
	
	return array(
	  'privKey' => $privKey,
	  'pubKey' => $pubKey
	);
  }
  
  /**
* Generate a new set of bitcoin keys
*
* @author Jacob Bruce
* @return associative array ('privKey', 'pubKey', 'privWIF', 'pubAdd') 
* @access public
*/
  public static function getNewKeySet($magicbyte = '00') {
    do {
      $keyPair = self::getNewKeyPair();	
	  $privWIF = self::privKeyToWIF($keyPair['privKey'], $magicbyte);
	  $pubAdd = self::pubKeyToAddress($keyPair['pubKey'], $magicbyte);
	
	} while (!self::checkAddress($pubAdd));
	
	return array(
	  'privKey' => $keyPair['privKey'],
	  'pubKey' => $keyPair['pubKey'],
	  'privWIF' => $privWIF,
	  'pubAdd' => $pubAdd
	);
  }
  
  /**
* Convert private key to Wallet Import Format (WIF)
*
* @author Jacob Bruce
* @param string $privKey
* @return string
* @access public
*/
  public static function privKeyToWIF($privKey, $magicbyte = '00') {
    return self::hash160ToAddress($privKey, $magicbyte);
  }
  
  /**
* Convert Wallet Import Format (WIF) to private key
*
* @author Jacob Bruce
* @param string $WIF
* @return string
* @access public
*/
  public static function WIFtoPrivKey($WIF) {
    return self::addressToHash160($WIF);
  }
  
  /**
* Checks for typos in the mini key
*
* @author Jacob Bruce
* @param string $miniKey
* @return boolean
* @access public
*/
  public static function checkMiniKey($miniKey) {
    if (strlen($miniKey) != 22) { return false; }
	$miniHash = hash('sha256', $miniKey.'?');
  	if ($miniHash[0] == 0x00) {
	  return true;
	} else {
	  return false;
	}
  }

  /**
* Generate a new mini private key
*
* @author Jacob Bruce
* @return string
* @access public
*/
  public static function getNewMiniKey() {
    $miniKey = 'S';
	do {
	  $cand = $miniKey.self::randomString(21);
	  if (self::checkMiniKey($cand)) {
	    $miniKey = $cand;
	  }
	} while ($miniKey == 'S');
    return $miniKey;
  }
  
  /**
* Convert mini key to Wallet Import Format (WIF)
*
* @author Jacob Bruce
* @param string $miniKey
* @return string
* @access public
*/
  public static function miniKeyToWIF($miniKey) {
    return self::privKeyToWIF(hash('sha256', $miniKey));
  }
  
  /**
* Get bitcoin address from a mini private key
*
* @author Jacob Bruce
* @param string $miniKey
* @return string
* @access public
*/
  public static function miniKeyToAddress($miniKey) {
  
    if (!self::checkMiniKey($miniKey)) {
	  return 'invalid mini key';
	}
	  
	$privKey = hash('sha256', $miniKey);
    return self::privKeyToAddress($privKey, BITCOIN_ADDRESS_VERSION);
  }
  
}


?>
