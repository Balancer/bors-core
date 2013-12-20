<?php

function debug_execute_trace($message)
{
	if(!config('debug.execute_trace'))
		return;

	static $timestamp;
	static $mem;
	$now = microtime(true);

	bors_function_include('debug/hidden_log');
	$time = sprintf("%2.3f",  $now - $GLOBALS['stat']['start_microtime']);
	if($timestamp)
	{
		$delta = sprintf("%1.3f", $now - $timestamp);
		$delta_mem = memory_get_usage() - $mem;
		$mem = memory_get_usage();
	}
	else
	{
		debug_hidden_log('execute_trace', "--------------------------------------------------", false);
		$delta = sprintf("%1.3f", $now - $GLOBALS['stat']['start_microtime']);
		$mem = memory_get_usage();
		$delta_mem = $mem;
	}

	debug_hidden_log('execute_trace', "+$delta = $time ["
		.($delta_mem > 0 ? '+' : '')
		.sprintf('%1.2f', $delta_mem/1048576).'Mb = '
		.sprintf('%1.2f', $mem/1048576)."Mb]: $message", false);
	$timestamp = $now;
}
