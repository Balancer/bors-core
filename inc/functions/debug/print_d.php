<?php

bors_function_include('debug/xmp');

function print_d($data, $string=false) { return debug_xmp(print_r($data, true), $string); }
