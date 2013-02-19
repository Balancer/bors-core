<?php

if(class_exists('XMLWriter'))
{
	function array2xml($data, $root = NULL)
	{
		require_once('classes/inc/array2xml-unknown.php');
		$converter = new Array2XML();
		if($root)
			$converter->setRootName($root);
		return $converter->convert($data);
	}
}

// Вариант на DOM
elseif(class_exists('DOMDocument'))
{
	function array2xml($data, $root = NULL)
	{
		require_once('classes/inc/array2xml-lalit-patel.php');
		$xml = Array2XML::createXML($root, $data);
		return $xml->saveXML();
	}
}
