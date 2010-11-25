<?php

class bors_tests_xml_array2xml_unittest extends PHPUnit_Framework_TestCase
{
    public function test_array2xml()
    {
		require_once('inc/xml/array2xml.php');

		for($i=1; $i<=3; $i++)
		{
			$base = 'data'.sprintf('%02d', $i);
			require_once(dirname(__FILE__)."/$base.php");
			$xml = file_get_contents(dirname(__FILE__)."/$base.xml");

			$x = array2xml($data);
    	    $this->assertEquals($xml, $x);
		}

    }
}
