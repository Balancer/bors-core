<?php

define('BORS_SITE', __DIR__);

require_once('../config.php');

define('LOOPS', 100);
define('ENGINES', 'bors_cache_memcache bors_cache_mysql bors_cache_smart bors_cache_zend_file bors_cache_redis');

function do_test($cache_engine, $verbose = false)
{
	if($verbose)
		echo "Test $cache_engine\n";

	$start = microtime(true);
	$ch = new $cache_engine;
	$uniq = uniqid('cache_test');

	// Заполняем кеш. Должно уйти меньше 2 секунд!
	if($verbose)
		echo "\tCache filling\n";
	for($i=0; $i<LOOPS; $i++)
	{
		$ch->get($uniq, md5($i));
		// Храним чётные числа 2 секунды, нечётные — 6 сек.
		$ch->set(str_repeat(md5($i*2), 10), $i % 2 ? 6 : 2);
	}

	// Проверяем, должны быть все значения.
	if($verbose)
		echo "\tCheck all values\n";

	for($i=0; $i<LOOPS; $i++)
	{
		$res = $ch->get($uniq, md5($i));
		if($res != ($expect = str_repeat(md5($i*2), 10)))
			return error($verbose,$cache_engine, $i, $res, $expect, true);
	}

	// Очищаем кеш в памяти
	global_keys_clean();
	// Ждём три секунды - кеш чётных чисел должен очиститься.
	if($verbose)
		echo "\tWait 3 seconds\n";
	sleep(3);

	// Проверяем, должны быть только нечётные значения.
	if($verbose)
		echo "\tCheck expire values\n";
	for($i=0; $i<LOOPS; $i++)
	{
		$res = $ch->get($uniq, md5($i), $def = 'none'.$i);
		if($i % 2 == 0)
		{
			// Чётные должны отсутствовать, точнее — равняться $def.
			if($res == $def)
				continue;

			return error($verbose, $cache_engine, $i, $res, $def);
		}

		if($i % 2)
		{
			// Нечётные должны быть равны тестовому числу.
			if($res == ($expect = str_repeat(md5($i*2), 10)))
				continue;

			return error($verbose, $cache_engine, $i, $res, $expect);
		}
	}

	$time = microtime(true) - $start - 3;
	if($verbose)
		echo "\tAll right. Test '$cache_engine' done in ".sprintf("%.3f", $time)."!\n";

	return $time;
}

function error($verbose, $cache_engine, $iteration, $result, $expect, $init = false)
{
	if(strlen($expect) > 7)
		$expect = substr($expect,0,3).'…'.substr($expect, -3);

	if($verbose)
		echo "\t *** Cache '$cache_engine' ".($init ? 'init ':'')."error for $iteration: '"
			. $result."' != '$expect'\n";

	return 999999;
}
