<?php

include_once('config.php');
include_once(BORS_CORE.'/init.php');

$file = $_SERVER['argv'][1];
xml_dump($file);


function xml_dump($file)
{
	require_once('classes/inc/BorsXml.php');
	$xml = new BorsXml;
	$xml->parse(file_get_contents($file));

	print_d($xml->dom);
}
