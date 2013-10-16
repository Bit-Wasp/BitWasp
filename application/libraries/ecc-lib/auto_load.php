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

/*
 * This file sets up class-loading and the environment
 * also tests whether GMP, BCMATH, or both are defined
 * if the GMP php extension exists it is preffered
 * because it is at least an order of magnitude faster
 */
function __autoload($f) {
    //load interfaces before class files
    $interfaceFile = dirname(__FILE__) . "/classes/interface/" . $f . "Interface.php";
    if (file_exists($interfaceFile)) {
        require_once $interfaceFile;
    }

    //load class files after interfaces
    $classFile = dirname(__FILE__) . "/classes/" . $f . ".php";
    if (file_exists($classFile)) {
        require_once $classFile;
    }

    //if utilities are needed load them last
    $utilFile = dirname(__FILE__) . "/classes/util/" . $f . ".php";
    if (file_exists($utilFile)) {
        require_once $utilFile;
    }
}

$seconds = 7200;
set_time_limit($seconds);

if(extension_loaded('gmp') && !defined('USE_EXT')){
    define ('USE_EXT', 'GMP');
}else if(extension_loaded('bcmath') && !defined('USE_EXT')){
    define ('USE_EXT', 'BCMATH');
}
?>
