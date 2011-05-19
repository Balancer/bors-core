<?php

define('BORS_LOCAL', dirname(__FILE__));
require '../tools/config.php';

$start = microtime(true);
bors_benchmarks_cache_zendfile::test_like_bors();
$time = microtime(true) - $start;
echo "Total time=$time\n";
