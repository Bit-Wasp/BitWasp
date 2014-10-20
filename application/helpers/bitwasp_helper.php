<?php
/**
 *
 */

function format_short_description($string, $max_length = 70)
{
    $string = strip_tags($string);
    $length = strlen($string);

    return ($length > $max_length)
        ? substr($string, 0, $max_length) . "..."
        : $string;

}