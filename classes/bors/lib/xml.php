<?php

class bors_lib_xml
{
	static function xml2array($xml)
	{
		require_once('classes/inc/BorsXml.php');
		$parser = new BorsXml();
		$parser->parse($xml);
		return $parser->dom;
	}
}
