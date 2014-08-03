<?php

// Однопоточный тест механизмов кеширования

require_once('_routine.php');

config_set('cache_zend_file_dir', __DIR__.'/cache');

foreach(explode(' ', ENGINES) as $cache_engine)
	do_test($cache_engine, true);
