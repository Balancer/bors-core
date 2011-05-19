<?php

// Многопоточный тест механизмов кеширования

require_once('_routine.php');
require_once('../../benchmarks/sharedMemoryStore.php');

define('THREADS', 5);

config_set('cache_zend_file_dir', __DIR__.'/cache');

foreach(explode(' ', ENGINES) as $cache_engine)
{
	echo "$cache_engine:\n";
	$pids = array();
	$memx = new sharedMemoryStore(__FILE__);
	$memx->set("timing", serialize(array()));

	for($i=0; $i<THREADS; $i++)
	{
		if($pid = pcntl_fork()) // 0,3 сек на 100 циклов. 3мс на форк.
		{
			// Это основной процесс
			$pids[] = $pid;
		}
		else
		{
			// Это - потомок
			$info = array();
			pcntl_sigprocmask(SIG_BLOCK, array(SIGHUP));
			pcntl_sigwaitinfo(array(SIGHUP), $info);
			$time = do_test($cache_engine);

			// Сохраним полученный результат
			$x = new sharedMemoryStore(__FILE__);
			$x->lock();
			$a = unserialize($x->get("timing"));
			$a[posix_getpid()] = $time;
			$x->set("timing", serialize($a));
			$x->unlock();

			exit();
		}

	}

	usleep(1000000); // 0,01sec

//	echo "Begin starting process…\n";
	$start = microtime(true);
	foreach($pids as $pid)
		posix_kill($pid, SIGHUP);

	$status = null;
	for($i=0; $i<THREADS; $i++)
		pcntl_wait($status);

	$memx->lock();
	$timing = unserialize($memx->get("timing"));
 	$memx->unlock();

//	print_r($timing);

	$values = array_filter(array_values($timing), function($x) { return $x != 999999;});
	echo "    success: ".count($values)."/".THREADS."\n";

	sort($values);
	$sum = array_sum($values);
	$count = count($values);
	$min = sprintf('%.3f', min($values));
	$max = sprintf('%.3f', max($values));
	$median = array_pop(array_slice($values, intval($count/2), 1));
	echo "    avg=".sprintf('%.3f', $sum/$count)."; median=".sprintf('%.3f', $median)."; min=$min; max=$max\n\n";

//	echo "$cache_engine tests done\n";
}
