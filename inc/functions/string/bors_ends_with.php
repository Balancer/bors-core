<?php

function bors_ends_with($string, $char)
{
    $length = strlen($char);
    return (substr($string, -$length, $length) === $char);
}
