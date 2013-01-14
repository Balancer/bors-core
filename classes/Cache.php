<?php

require_once('engines/bors.php');

$ce = config('cache_engine', 'bors_cache_base');
eval('class Cache extends '.$ce.'{}');
eval('class bors_cache extends '.$ce.'{}');
