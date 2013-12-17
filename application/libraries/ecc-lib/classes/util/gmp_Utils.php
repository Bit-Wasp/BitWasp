<?php
/***********************************************************************
Copyright 2010 Matyas Danter

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*************************************************************************/

/**
 * The gmp extension in PHP does not implement certain necessary operations
 * for elliptic curve encryption
 * This class implements all neccessary static methods
 *
 */
class gmp_Utils {

    public static function gmp_mod2($n, $d) {
        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            $res = gmp_div_r($n, $d);
            if (gmp_cmp(0, $res) > 0) {
                $res = gmp_add($d, $res);
            }
            return gmp_strval($res);
        } else {
            throw new Exception("PLEASE INSTALL GMP");
        }
    }

    public static function gmp_random($n) {
        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            $random = gmp_strval(gmp_random());
            $small_rand = mt_rand();
            while (gmp_cmp($random, $n) > 0) {
                $random = gmp_div($random, $small_rand, GMP_ROUND_ZERO);
            }

            return gmp_strval($random);
        } else {
            throw new Exception("PLEASE INSTALL GMP");
        }
    }

    public static function gmp_hexdec($hex) {
        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            $dec = gmp_strval(gmp_init($hex), 10);

            return $dec;
        } else {
            throw new Exception("PLEASE INSTALL GMP");
        }
    }

    public static function gmp_dechex($dec) {
        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            $hex = gmp_strval(gmp_init($hex), 16);

            return $hex;
        } else {
            throw new Exception("PLEASE INSTALL GMP");
        }
    }

}
?>
