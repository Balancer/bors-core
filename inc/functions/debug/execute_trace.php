<?php

function debug_execute_trace($message)
{
	if(!config('debug.execute_trace'))
		return;

	static $timestamp;
	$now = microtime(true);

	bors_function_include('debug/hidden_log');
	$time = sprintf("%2.3f",  $now - $GLOBALS['stat']['start_microtime']);
	if($timestamp)
		$delta = sprintf("%1.3f", $now - $timestamp);
	else
	{
		debug_hidden_log('execute_trace', "--------------------------------------------------", false);
		$delta = sprintf("%1.3f", $now - $GLOBALS['stat']['start_microtime']);
	}

	debug_hidden_log('execute_trace', "+$delta = $time: $message", false);
	$timestamp = $now;
}
