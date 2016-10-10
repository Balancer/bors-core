<?php

use B2\Cfg;

require_once('engines/bors.php');

$ce = Cfg::get('cache_engine', 'bors_cache_base');
//eval('class Cache extends '.$ce.'{}');
eval('class bors_cache extends '.$ce.'{}');
