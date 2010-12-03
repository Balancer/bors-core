<?php

define('THREADS', 10);

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

	print_r($pids);

	echo "\ncreated for ".(microtime(true) - $start)." ms\n"; 

	usleep(10000); // 0,1sec

	$start = microtime(true);
	foreach($pids as $pid)
		posix_kill($pid, SIGHUP);

	echo "\nstarted for ".(microtime(true) - $start)." ms\n";

	echo "wait for child finihed\n";
	usleep(10000); // 0,1sec
	$status = 0;
	pcntl_wait($status);

	$memx->lock();
	$timing = unserialize($memx->get("timing"));
 	$memx->unlock();

	print_r($timing);
	echo "tasks count = ".count($timing)."\n";
}

function do_test()
{
	$s = ".";
	for($i=0; $i<100000; $i++)
		$s .= ".";
}
