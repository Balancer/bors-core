<?php

define('THREADS', 10);

define('BORS_LOCAL', dirname(__FILE__));
require '../tools/config.php';

require_once('sharedMemoryStore.php');
main();

function main()
{

	$pids = array();
	$memx = new sharedMemoryStore(__FILE__);
	$memx->set("timing", serialize(array()));

	echo THREADS." threads\n";

	$start = microtime(true);
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
			$begin = microtime(true);
			do_test();
			$time = microtime(true) - $begin;

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

	echo "Running thread PIDs:\n";
//	print_r($pids);

	echo "\nCreated for ".(microtime(true) - $start)." ms\n"; 

	usleep(1000000); // 0,01sec

	echo "Begin startin process…\n";
	$start = microtime(true);
	foreach($pids as $pid)
		posix_kill($pid, SIGHUP);

	echo "\nStarted for ".(microtime(true) - $start)." ms\n";

	usleep(100000); // 0,1sec

	echo "Wait for child finihed…\n";
	$status = 0;
//	pcntl_wait($status);
	foreach($pids as $pid)
		pcntl_waitpid($pid, $status);

	echo "All children stopped…\n";
	usleep(100000); // 0,1sec

	$memx->lock();
	$timing = unserialize($memx->get("timing"));
 	$memx->unlock();

	print_r($timing);
	echo "Result tasks count = ".count($timing)."\n";

	$values = array_values($timing);

	sort($values);
	$sum = array_sum($values);
	$count = count($values);
	$min = min($values);
	$max = max($values);
	$median = array_pop(array_slice($values, intval($count/2), 1));
	echo "avg=".($sum/$count)."; median=".$median."; min=$min; max=$max\n";
}

function do_test()
{
	sleep(1);

	bors_benchmarks_cache_zendfile::test_like_bors();
//	$s = ".";
//	for($i=0; $i<2000000; $i++)
//		$s .= ".";
}
