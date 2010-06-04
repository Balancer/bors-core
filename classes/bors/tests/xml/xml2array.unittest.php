<?php

class bors_tests_xml_xml2array_unittest extends PHPUnit_Framework_TestCase
{
    public function test_xml2array()
    {
		require_once('inc/xml/xml2array.php');
		require_once('inc/php.php');

		for($i=1; $i<=1; $i++)
		{
			$base = 'x2a'.sprintf('%02d', $i);
			require_once(dirname(__FILE__)."/$base.php");
			$xml = file_get_contents(dirname(__FILE__)."/$base.xml");

			$a = xml2array($xml);
    	    $this->assertEquals($data, $a);
		}

    }
}
