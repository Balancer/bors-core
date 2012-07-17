<?php

class blib_xml
{
	function parse($xml)
	{
		require_once('inc/xml/xml2array.php');
		return xml2array($xml);
	}
}
