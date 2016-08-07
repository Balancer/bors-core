<?php

require_once BORS_CORE.'/inc/functions/debug/xmp.php';

function print_dl($data) { return str_replace("\n", " ", print_r($data, true)); }
