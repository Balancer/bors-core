<?php

bors_function_include('debug/xmp');

function print_dl($data) { return str_replace("\n", " ", print_r($data, true)); }
