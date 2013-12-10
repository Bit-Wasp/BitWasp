<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');

// Determine bitcoin address version. 
// Will be revised altogether when it comes to looking at other
// currencies. 

$CI = &get_instance();
//$CI->load->library('bw_bitcoin');
$bitcoin_info = $CI->bw_bitcoin->getinfo();
$byte = ($bitcoin_info['testnet'] == TRUE) ? "6F" : "00";
define("BITCOIN_ADDRESS_VERSION", $byte);// this is a hex byte

class Mpkgen {

	public function address($mpk, $iteration) {

		// Equation takes place over Fp
		// E: y^2 = x^3 + ax + b
		
		// The elliptic curve domain parameters over Fp associated with a Koblitz curve secp256k1 are specified by the sextuple T = (p,a,b,G,n,h) where the finite field Fp is defined by: 
		// Finite Field (Fp):
		$_p = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);
		$_a = gmp_init('0000000000000000000000000000000000000000000000000000000000000000', 16);
		$_b = gmp_init('0000000000000000000000000000000000000000000000000000000000000007', 16);
		$_Gx = gmp_init('79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', 16);
		$_Gy = gmp_init('483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 16);
		
		// Large prime order 'n' of G. 
		$_n = gmp_init('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
		//h = 1.
		
		$curve = new CurveFp($_p, $_a, $_b);		
		$gen = new Point($curve, $_Gx, $_Gy, $_n);
		
		// prepare the input values
		$x = gmp_init(substr($mpk, 0, 64), 16);
		$y = gmp_init(substr($mpk, 64, 64), 16);
		$z = gmp_init(hash('sha256', hash('sha256', $iteration . ':0:' . pack('H*', $mpk), TRUE)), 16);

		// generate the new public key based off master and sequence points
		$pt = Point::add(new Point($curve, $x, $y), Point::mul($z, $gen));
		$keystr = pack('H*', '04'
				. str_pad(gmp_strval($pt->x, 16), 64, '0', STR_PAD_LEFT)
				. str_pad(gmp_strval($pt->y, 16), 64, '0', STR_PAD_LEFT));

		$vh160 =  BITCOIN_ADDRESS_VERSION . hash('ripemd160', hash('sha256', $keystr, TRUE));
		$addr = $vh160 . substr(hash('sha256', hash('sha256', pack('H*', $vh160), TRUE)), 0, 8);

		$num = gmp_strval(gmp_init($addr, 16), 58);
		$num = strtr($num, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv', '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');

		$pad = ''; $n = 0;
		while ($addr[$n] == '0' && $addr[$n+1] == '0') {
			$pad .= '1';
			$n += 2;
		}

		return $pad . $num;
	}
};

/* End of File: Mpkgen.php */ 
