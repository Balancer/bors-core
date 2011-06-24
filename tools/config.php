<?php

$argv = $_SERVER['argv'];
if(1 || empty($argv[1]))
	require_once('config-local.php');
else
	foreach(explode(',', $argv[1]) as $pair)
	{
		list($name, $value) = explode('=', $pair);
		define($name, $value);
	}

#	exit("Use loop.sh BORS_CORE=...,BORS_HOST=...,....\n");

include_once(BORS_CORE.'/init.php');
config_set('system.use_sessions', false);


