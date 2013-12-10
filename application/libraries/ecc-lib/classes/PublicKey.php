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
 * This class serves as public- private key exchange for signature verification
 */
class PublicKey implements PublicKeyInterface {

    protected $curve;
    protected $generator;
    protected $point;

    public function __construct(Point $generator, Point $point) {
        $this->curve = $generator->getCurve();
        $this->generator = $generator;
        $this->point = $point;

        $n = $generator->getOrder();

        if ($n == null) {
            throw new ErrorExcpetion("Generator Must have order.");
        }
        if (Point::cmp(Point::mul($n, $point), Point::$infinity) != 0) {
            throw new ErrorException("Generator Point order is bad.");
        }

        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            if (gmp_cmp($point->getX(), 0) < 0 || gmp_cmp($n, $point->getX()) <= 0 || gmp_cmp($point->getY(), 0) < 0 || gmp_cmp($n, $point->getY()) <= 0) {
                throw new ErrorException("Generator Point has x and y out of range.");
            }
        } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') {
            if (bccomp($point->getX(), 0) == -1 || bccomp($n, $point->getX()) != 1 || bccomp($point->getY(), 0) == -1 || bccomp($n, $point->getY()) != 1) {
                throw new ErrorException("Generator Point has x and y out of range.");
            }
        } else {
            throw new ErrorException("Please install BCMATH or GMP");
        }
    }

    public function verifies($hash, Signature $signature) {
        if (extension_loaded('gmp') && USE_EXT=='GMP') {
            $G = $this->generator;
            $n = $this->generator->getOrder();
            $point = $this->point;
            $r = $signature->getR();
            $s = $signature->getS();

            if (gmp_cmp($r, 1) < 0 || gmp_cmp($r, gmp_sub($n, 1)) > 0) {
                return false;
            }
            if (gmp_cmp($s, 1) < 0 || gmp_cmp($s, gmp_sub($n, 1)) > 0) {
                return false;
            }
            $c = NumberTheory::inverse_mod($s, $n);
            $u1 = gmp_Utils::gmp_mod2(gmp_mul($hash, $c), $n);
            $u2 = gmp_Utils::gmp_mod2(gmp_mul($r, $c), $n);
            $xy = Point::add(Point::mul($u1, $G), Point::mul($u2, $point));
            $v = gmp_Utils::gmp_mod2($xy->getX(), $n);

            if (gmp_cmp($v, $r) == 0)
                return true;
            else {
                return false;
            }
        } else if (extension_loaded('bcmath') && USE_EXT=='BCMATH') {
            $G = $this->generator;
            $n = $this->generator->getOrder();
            $point = $this->point;
            $r = $signature->getR();
            $s = $signature->getS();

            if (bccomp($r, 1) == -1 || bccomp($r, bcsub($n, 1)) == 1) {
                return false;
            }
            if (bccomp($s, 1) == -1 || bccomp($s, bcsub($n, 1)) == 1) {
                return false;
            }
            $c = NumberTheory::inverse_mod($s, $n);
            $u1 = bcmod(bcmul($hash, $c), $n);
            $u2 = bcmod(bcmul($r, $c), $n);
            $xy = Point::add(Point::mul($u1, $G), Point::mul($u2, $point));
            $v = bcmod($xy->getX(), $n);

            if (bccomp($v, $r) == 0)
                return true;
            else {
                return false;
            }
        } else {
            throw new ErrorException("Please install BCMATH or GMP");
        }
    }

    public function getCurve() {
        return $this->curve;
    }

    public function getGenerator() {
        return $this->generator;
    }

    public function getPoint() {
        return $this->point;
    }

    public function getPublicKey() {
        print_r($this);
        return $this;
    }

}
?>
