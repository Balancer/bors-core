<?php

class bors_tests_xml_array2xmlwp_unittest extends PHPUnit_Framework_TestCase
{
    public function test_array2xmlwp()
    {
		require_once('inc/xml/array2xml_wp.php');

		for($i=1; $i<=2; $i++)
		{
			$base = 'a2xwp'.sprintf('%02d', $i);
			require_once(dirname(__FILE__)."/$base.php");
			$xml = file_get_contents(dirname(__FILE__)."/$base.xml");

			$x = array2xml_wp($data);
    	    $this->assertEquals($xml, $x);
		}

    }
}
