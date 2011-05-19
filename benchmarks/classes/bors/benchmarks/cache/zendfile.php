<?php

define('CYCLES', 500);

class bors_benchmarks_cache_zendfile // extends bors_benchmarks_meta
{
	static function test_like_bors()
	{
		for($i=1; $i<=CYCLES; $i++)
		{
			$ch = new bors_cache_zend_file;
			if($ch->get('bors_benchmarks_cache_zendfile', $i))
				$ch->last();
			else
				$ch->set($i, 10);
		}

		$sum = 0;
		for($i=1; $i<=CYCLES; $i++)
		{
			$ch = new bors_cache_zend_file;
			if($ch->get('bors_benchmarks_cache_zendfile', $i))
				$sum += $ch->last();
			else
				echo "test error: $i\n";
		}

		$expect = CYCLES*(CYCLES+1)/2;
		if($sum != $expect)
			echo "Error: $sum != $expect\n";
	}
}
