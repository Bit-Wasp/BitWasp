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
 * This is a contract for the PrivaetKey portion of ECDSA.
 *
 * @author Matej Danter
 */
interface PrivateKeyInterface {
    
    public function __construct(PublicKey $public_key, $secret_multiplier);

    public function sign($hash, $random_k);

    public static function int_to_string($x);

    public static function string_to_int($s);

    public static function digest_integer($m);

    public static function point_is_valid(Point $generator, $x, $y);
}
?>
