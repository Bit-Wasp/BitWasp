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
 * This is the contract for implementing CurveFp (EC prime finite-field).
 *
 * @author Matej Danter
 */
interface CurveFpInterface {
        //constructor that sets up the instance variables
        public function  __construct($prime, $a, $b);

        public function contains($x,$y);

        public function getA();

        public function getB();

        public function getPrime();

        public static function cmp(CurveFp $cp1, CurveFp $cp2);

}
?>
