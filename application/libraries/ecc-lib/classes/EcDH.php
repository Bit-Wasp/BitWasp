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
 * This class is the implementation of ECDH.
 * EcDH is safe key exchange and achieves
 * that a key is transported securely between two parties.
 * The key then can be hashed and used as a basis in
 * a dual encryption scheme, along with AES for faster
 * two- way encryption.
 *
 * @author Matej Danter
 */
class EcDH implements EcDHInterface {

    private $generator;
    private $pubPoint;
    private $receivedPubPoint;
    private $secret;
    private $agreed_key;

    public function __construct(Point $g) {
        $this->generator = $g;
    }

    public function calculateKey() {

        $this->agreed_key = Point::mul($this->secret, $this->receivedPubPoint)->getX();
    }

    public function getPublicPoint() {
        if (extension_loaded('gmp') && USE_EXT == 'GMP') {
            //alice selects a random number between 1 and the order of the generator point(private)
            $n = $this->generator->getOrder();

            $this->secret = gmp_Utils::gmp_random($n);

            //Alice computes da * generator Qa is public, da is private
            $this->pubPoint = Point::mul($this->secret, $this->generator);

            return $this->pubPoint;
        } else if (extension_loaded('bcmath') && USE_EXT == 'BCMATH') {
            //alice selects a random number between 1 and the order of the generator point(private)
            $n = $this->generator->getOrder();

            $this->secret = bcmath_Utils::bcrand($n);

            //Alice computes da * generator Qa is public, da is private
            $this->pubPoint = Point::mul($this->secret, $this->generator);

            return $this->pubPoint;
        } else {
            throw new ErrorException("Please Install BCMATH or GMP.");
        }
    }

    public function setPublicPoint(Point $q) {
        $this->receivedPubPoint = $q;
    }

    public function encrypt($string) {
        $key = hash("sha256", $this->agreed_key, true);

        $cypherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, base64_encode($string), MCRYPT_MODE_CBC, $key);

        return $cypherText;
    }

    public function decrypt($string) {
        $key = hash("sha256", $this->agreed_key, true);

        $clearText = base64_decode(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, $key));
        return $clearText;
    }

    public function encryptFile($path) {

        if (file_exists($path)) {
            $string = file_get_contents($path);

            $key = hash("sha256", $this->agreed_key, true);

            $cypherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, base64_encode($string), MCRYPT_MODE_CBC, $key);

            return $cypherText;
        }
    }

    public function decryptFile($path) {

        if (file_exists($path)) {
            $string = file_get_contents($path);

            $key = hash("sha256", $this->agreed_key, true);

            $clearText = base64_decode(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, $key));

            return $clearText;
        }
    }

}

?>
