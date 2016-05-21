<?php

require_once BORS_CORE.'/inc/functions/debug/xmp.php';

function print_d($data, $string=false) { return debug_xmp(print_r($data, true), $string); }
