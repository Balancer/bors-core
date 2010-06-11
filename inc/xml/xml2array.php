<?php

function xml2array($xml)
{
	require_once('classes/inc/BorsXml.php');
	$parser = new BorsXml();
	$parser->parse($xml);
	return $parser->dom;
}
