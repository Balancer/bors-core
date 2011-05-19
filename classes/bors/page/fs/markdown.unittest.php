<?php

class bors_page_fs_markdown_unittest extends PHPUnit_Framework_TestCase
{
    public function test_bors_markdown()
    {
		$md = object_load('http://localhost/_unittests/markdown/');
        $this->assertNotNull($md);

//TODO: добавить проверку адресов вида http://host.ru/news/2011-03-16

        $this->assertEquals('Markdown test', $md->title());
    }
}
