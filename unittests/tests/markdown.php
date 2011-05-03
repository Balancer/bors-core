<?php

class markdown_unittest extends PHPUnit_Framework_TestCase
{
    public function test_markdown_storage()
    {
		$x = bors_load_uri('http://localhost/markdown/');
		$this->assertNotNull($x);
		$this->assertEquals('Root test', $x->title());
	}
}
