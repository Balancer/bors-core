<?php

$argv = $_SERVER['argv'];
if(empty($argv[1]))
	exit("Use loop.sh BORS_CORE=...,BORS_HOST=...,....\n");

foreach(explode(',', $argv[1]) as $pair)
{
	list($name, $value) = explode('=', $pair);
	define($name, $value);
}
