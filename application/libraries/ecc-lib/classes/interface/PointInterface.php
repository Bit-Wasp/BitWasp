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
 * This is the contract for implementing Point, which encapsulates entities
 * and operations over the points on the Elliptic Curve.
 *
 * @author Matej Danter
 */
interface PointInterface {
    public function __construct(CurveFp $curve, $x, $y, $order = null);

    public static function cmp($p1, $p2);

    public static function add($p1, $p2);

    public static function mul($x2, Point $p1);

    public static function leftmost_bit($x);

    public static function rmul(Point $p1, $m);

    public function __toString();

    public static function double(Point $p1);

    public function getX();

    public function getY();

    public function getCurve();

    public function getOrder();

}
?>
