<?php

$DIR = dirname(__FILE__);

if(file_exists("$DIR/setup-host.php"))
	include_once("$DIR/setup-host.php");

if(!defined('BORS_CORE'))
	define('BORS_CORE', dirname($DIR));

if(!defined('BORS_SITE'))
	define('BORS_SITE', $DIR);

