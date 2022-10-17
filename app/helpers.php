<?php
/**
 * This file contain general helper functions for the application
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



/**
 * pad a given number with zeros to a given lenght
 */
function padWithZeros($number, $length): string
{
    $numberLength = strlen("$number");
    $zeroString = "";
    if ($length > $numberLength) {
        $numZeros = $length - $numberLength;
        for ($i = 1; $i <= $numZeros; $i++) {
            $zeroString .= "0";
        }
    }
    return "$zeroString$number";
}

function generateSlug(): string
{
    return date('his').rand(1000000000,9999999999).date('ymd');
}


