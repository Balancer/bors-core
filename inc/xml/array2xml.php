<?php

function array2xml($data, $rootNodeName = 'data', $xml=null, $input_charset = 'utf-8')
{
	// turn off compatibility mode as simple xml throws a wobbly if you don't.
	if(ini_get('zend.ze1_compatibility_mode') == 1)
		ini_set ('zend.ze1_compatibility_mode', 0);

	if($xml == null)
		$xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName></$rootNodeName>");

	// loop through the data passed in.
	foreach($data as $key => $value)
	{
		if($input_charset != 'utf-8' && !is_array($value))
			$value = iconv($input_charset, 'utf-8', $value);

		// no numeric keys in our xml please!
		if (is_numeric($key))
			// make string key...
			$key = "unknownNode_". (string) $key;

		// replace anything not alpha numeric
		$key = preg_replace('/[^a-z]/i', '', $key);

		// if there is another array found recrusively call this function
		if(is_array($value))
		{
			if(array_key_exists('0', $value))
			foreach($value as $x)
				$xml->addChild($key, $x);
			else
			{
				$node = $xml->addChild($key);
				// recrusive call.
				array2xml($value, $rootNodeName, $node, $input_charset);
			}
		}
		else 
		{
			// add single node.
//            $value = htmlentities($value);
			$xml->addChild($key, $value);
		}

	}

	// pass back as string. or simple xml object if you want!
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->formatOutput = true;
	$domnode = dom_import_simplexml($xml);
	$domnode = $doc->importNode($domnode, true);
	$domnode = $doc->appendChild($domnode);
	return $doc->saveXML(); 
}
/*
print_r(array2xml(array(
	'title' => 'test',
	'image' => array('image1', 'image2', 'image3'),
	'author' => array(
		'first_name' => 'Lev',
		'last_name'  => 'Tolstoy',
		'logos' => array('logo' => array('l1', 'l2', 'l3')),
		'place' => array('title' => 'Yasnaya Polyana: тест'),
	),
)));
*/
