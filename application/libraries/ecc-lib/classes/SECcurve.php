<?php
/***********************************************************************
Copyright 2012 Jacob Bruce

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR $a PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received $a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*************************************************************************/

/**
 * This class encapsulates the SEC recommended curves
 *
 * @author Jacob Bruce 
 */
class SECcurve {
	
	private static function secp128r1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFF'),
				'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFC'),
				'b' => gmp_Utils::gmp_hexdec('0xE87579C11079F43DD824993C2CEE5ED3'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFE0000000075A30D1B9038A115'),
				'x' => gmp_Utils::gmp_hexdec("0x161FF7528B899B2D0C28607CA52C5B86"),
				'y' => gmp_Utils::gmp_hexdec("0xCF5AC8395BAFEB13C02DA292DDED7A83")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFF'),
				'a' => bcmath_Utils::bchexdec('0xFFFFFFFDFFFFFFFFFFFFFFFFFFFFFFFC'),
				'b' => bcmath_Utils::bchexdec('0xE87579C11079F43DD824993C2CEE5ED3'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFE0000000075A30D1B9038A115'),
				'x' => bcmath_Utils::bchexdec("0x161FF7528B899B2D0C28607CA52C5B86"),
				'y' => bcmath_Utils::bchexdec("0xCF5AC8395BAFEB13C02DA292DDED7A83")
			);
		}
	}
	
	private static function secp160k1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFAC73'),
				'a' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000'),
				'b' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000007'),
				'n' => gmp_Utils::gmp_hexdec('0x0100000000000000000001B8FA16DFAB9ACA16B6B3'),
				'x' => gmp_Utils::gmp_hexdec("0x3B4C382CE37AA192A4019E763036F4F5DD4D7EBB"),
				'y' => gmp_Utils::gmp_hexdec("0x938CF935318FDCED6BC28286531733C3F03C4FEE")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFAC73'),
				'a' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000'),
				'b' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000007'),
				'n' => bcmath_Utils::bchexdec('0x0100000000000000000001B8FA16DFAB9ACA16B6B3'),
				'x' => bcmath_Utils::bchexdec("0x3B4C382CE37AA192A4019E763036F4F5DD4D7EBB"),
				'y' => bcmath_Utils::bchexdec("0x938CF935318FDCED6BC28286531733C3F03C4FEE")
			);
		}
	}
	
	private static function secp160r1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFF'),
				'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFC'),
				'b' => gmp_Utils::gmp_hexdec('0x1C97BEFC54BD7A8B65ACF89F81D4D4ADC565FA45'),
				'n' => gmp_Utils::gmp_hexdec('0x0100000000000000000001F4C8F927AED3CA752257'),
				'x' => gmp_Utils::gmp_hexdec("0x4A96B5688EF573284664698968C38BB913CBFC82"),
				'y' => gmp_Utils::gmp_hexdec("0x23A628553168947D59DCC912042351377AC5FB32")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFF'),
				'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF7FFFFFFC'),
				'b' => bcmath_Utils::bchexdec('0x1C97BEFC54BD7A8B65ACF89F81D4D4ADC565FA45'),
				'n' => bcmath_Utils::bchexdec('0x0100000000000000000001F4C8F927AED3CA752257'),
				'x' => bcmath_Utils::bchexdec("0x4A96B5688EF573284664698968C38BB913CBFC82"),
				'y' => bcmath_Utils::bchexdec("0x23A628553168947D59DCC912042351377AC5FB32")
			);
		}
	}
	
	private static function secp192k1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFEE37'),
				'a' => gmp_Utils::gmp_hexdec('0x000000000000000000000000000000000000000000000000'),
				'b' => gmp_Utils::gmp_hexdec('0x000000000000000000000000000000000000000000000003'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFE26F2FC170F69466A74DEFD8D'),
				'x' => gmp_Utils::gmp_hexdec("0xDB4FF10EC057E9AE26B07D0280B7F4341DA5D1B1EAE06C7D"),
				'y' => gmp_Utils::gmp_hexdec("0x9B2F2F6D9C5628A7844163D015BE86344082AA88D95E2F9D")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFEE37'),
				'a' => bcmath_Utils::bchexdec('0x000000000000000000000000000000000000000000000000'),
				'b' => bcmath_Utils::bchexdec('0x000000000000000000000000000000000000000000000003'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFE26F2FC170F69466A74DEFD8D'),
				'x' => bcmath_Utils::bchexdec("0xDB4FF10EC057E9AE26B07D0280B7F4341DA5D1B1EAE06C7D"),
				'y' => bcmath_Utils::bchexdec("0x9B2F2F6D9C5628A7844163D015BE86344082AA88D95E2F9D")
			);
		}
	}
	
	private static function secp192r1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFF'),
				'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFC'),
				'b' => gmp_Utils::gmp_hexdec('0x64210519E59C80E70FA7E9AB72243049FEB8DEECC146B9B1'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFF99DEF836146BC9B1B4D22831'),
				'x' => gmp_Utils::gmp_hexdec("0x188DA80EB03090F67CBF20EB43A18800F4FF0AFD82FF1012"),
				'y' => gmp_Utils::gmp_hexdec("0x07192B95FFC8DA78631011ED6B24CDD573F977A11E794811")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFF'),
				'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFC'),
				'b' => bcmath_Utils::bchexdec('0x64210519E59C80E70FA7E9AB72243049FEB8DEECC146B9B1'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFF99DEF836146BC9B1B4D22831'),
				'x' => bcmath_Utils::bchexdec("0x188DA80EB03090F67CBF20EB43A18800F4FF0AFD82FF1012"),
				'y' => bcmath_Utils::bchexdec("0x07192B95FFC8DA78631011ED6B24CDD573F977A11E794811")
			);
		}
	}
	
	private static function secp224r1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF000000000000000000000001'),
				'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFE'),
				'b' => gmp_Utils::gmp_hexdec('0xB4050A850C04B3ABF54132565044B0B7D7BFD8BA270B39432355FFB4'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFF16A2E0B8F03E13DD29455C5C2A3D'),
				'x' => gmp_Utils::gmp_hexdec("0xB70E0CBD6BB4BF7F321390B94A03C1D356C21122343280D6115C1D21"),
				'y' => gmp_Utils::gmp_hexdec("0xBD376388B5F723FB4C22DFE6CD4375A05A07476444D5819985007E34")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF000000000000000000000001'),
				'a' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFE'),
				'b' => bcmath_Utils::bchexdec('0xB4050A850C04B3ABF54132565044B0B7D7BFD8BA270B39432355FFB4'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFF16A2E0B8F03E13DD29455C5C2A3D'),
				'x' => bcmath_Utils::bchexdec("0xB70E0CBD6BB4BF7F321390B94A03C1D356C21122343280D6115C1D21"),
				'y' => bcmath_Utils::bchexdec("0xBD376388B5F723FB4C22DFE6CD4375A05A07476444D5819985007E34")
			);
		}
	}
	
	private static function secp256r1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFF'),
				'a' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFC'),
				'b' => gmp_Utils::gmp_hexdec('0x5AC635D8AA3A93E7B3EBBD55769886BC651D06B0CC53B0F63BCE3C3E27D2604B'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFF00000000FFFFFFFFFFFFFFFFBCE6FAADA7179E84F3B9CAC2FC632551'),
				'x' => gmp_Utils::gmp_hexdec("0x6B17D1F2E12C4247F8BCE6E563A440F277037D812DEB33A0F4A13945D898C296"),
				'y' => gmp_Utils::gmp_hexdec("0x4FE342E2FE1A7F9B8EE7EB4A7C0F9E162BCE33576B315ECECBB6406837BF51F5")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFF'),
				'a' => bcmath_Utils::bchexdec('0xFFFFFFFF00000001000000000000000000000000FFFFFFFFFFFFFFFFFFFFFFFC'),
				'b' => bcmath_Utils::bchexdec('0x5AC635D8AA3A93E7B3EBBD55769886BC651D06B0CC53B0F63BCE3C3E27D2604B'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFF00000000FFFFFFFFFFFFFFFFBCE6FAADA7179E84F3B9CAC2FC632551'),
				'x' => bcmath_Utils::bchexdec("0x6B17D1F2E12C4247F8BCE6E563A440F277037D812DEB33A0F4A13945D898C296"),
				'y' => bcmath_Utils::bchexdec("0x4FE342E2FE1A7F9B8EE7EB4A7C0F9E162BCE33576B315ECECBB6406837BF51F5")
			);
		}
	}

	private static function secp256k1_params() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
			return array(
				'p' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F'),
				'a' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000000000000000000000000000'),
				'b' => gmp_Utils::gmp_hexdec('0x0000000000000000000000000000000000000000000000000000000000000007'),
				'n' => gmp_Utils::gmp_hexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141'),
				'x' => gmp_Utils::gmp_hexdec("0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798"),
				'y' => gmp_Utils::gmp_hexdec("0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8")
			);
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
			return array(
				'p' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F'),
				'a' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000000000000000000000000000'),
				'b' => bcmath_Utils::bchexdec('0x0000000000000000000000000000000000000000000000000000000000000007'),
				'n' => bcmath_Utils::bchexdec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141'),
				'x' => bcmath_Utils::bchexdec("0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798"),
				'y' => bcmath_Utils::bchexdec("0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8")
			);
		}
	}
	
    public static function curve_secp128r1() {
        $c_params = self::secp128r1_params();
        $curve_secp128r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp128r1;
    }

    public static function generator_secp128r1() {
        $c_params = self::secp128r1_params();
        $generator_secp128r1 = new Point(self::curve_secp128r1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp128r1;
    }
	
    public static function curve_secp160k1() {
        $c_params = self::secp160k1_params();
        $curve_secp160k1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp160k1;
    }

    public static function generator_secp160k1() {
        $c_params = self::secp160k1_params();
        $generator_secp160k1 = new Point(self::curve_secp160k1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp160k1;
    }
	
    public static function curve_secp160r1() {
        $c_params = self::secp160r1_params();
        $curve_secp160r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp160r1;
    }

    public static function generator_secp160r1() {
        $c_params = self::secp160r1_params();
        $generator_secp160r1 = new Point(self::curve_secp160r1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp160r1;
    }
	
    public static function curve_secp192k1() {
        $c_params = self::secp192k1_params();
        $curve_secp192k1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp192k1;
    }

    public static function generator_secp192k1() {
        $c_params = self::secp192k1_params();
        $generator_secp192k1 = new Point(self::curve_secp192k1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp192k1;
    }
	
    public static function curve_secp192r1() {
        $c_params = self::secp192r1_params();
        $curve_secp192r1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp192r1;
    }

    public static function generator_secp192r1() {
        $c_params = self::secp192r1_params();
        $generator_secp192r1 = new Point(self::curve_secp192r1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp192r1;
    }
	
    public static function curve_secp224r1() {
        $c_params = self::secp224r1_params();
        $curve_secp224r1= new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp224r1;
    }

    public static function generator_secp224r1() {
        $c_params = self::secp224r1_params();
        $generator_secp224r1 = new Point(self::curve_secp224r1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp224r1;
    }
	
    public static function curve_secp256r1() {
        $c_params = self::secp256r1_params();
        $curve_secp256r1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp256r1;
    }

    public static function generator_secp256r1() {	
        $c_params = self::secp256r1_params();
        $generator_secp256r1 = new Point(self::curve_secp256r1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp256r1;
    }

    public static function curve_secp256k1() {
        $c_params = self::secp256k1_params();
        $curve_secp256k1 = new CurveFp($c_params['p'], $c_params['a'], $c_params['b']);

        return $curve_secp256k1;
    }

    public static function generator_secp256k1() {
        $c_params = self::secp256k1_params();
        $generator_secp256k1 = new Point(self::curve_secp256k1(), $c_params['x'], $c_params['y'], $c_params['n']);
		
        return $generator_secp256k1;
    }
}
?>