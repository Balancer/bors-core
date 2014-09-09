<?php

class bors_lcml_unittest extends PHPUnit_Framework_TestCase
{
	public function test_lcml_markdown()
	{
		config_set('lcml_markdown', true);
		require_once('engines/lcml/main.php');

		$markdown = "Header1
======

Header2
-------

Header3
~~~~~~~
";

		$this->assertEquals('<h1>Header1</h1><h2>Header2</h2><h3>Header3</h3>', str_replace("\n", "", lcml($markdown)));
	}
}
