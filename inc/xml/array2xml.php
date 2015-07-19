<?php

if(class_exists('XMLWriter'))
{
	function array2xml($data, $root = NULL)
	{
		require_once('classes/inc/array2xml-unknown.php');
		$converter = new Array2XMLXW();
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
//		require_once('classes/inc/array2xml-lalit-patel.php');
		if(!class_exists('Array2XML'))
			throw new Exception('Not found Array2XML. use ```composer require openlss/lib-array2xml=*```');
		$xml = Array2XML::createXML($root, $data);
		return $xml->saveXML();
	}
}
