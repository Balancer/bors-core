<?php

class blib_text_lines_unittest extends PHPUnit_Framework_TestCase
{
	public function test_lines()
	{
		$text = "line1\nline2\nline3";
		$this->assertEquals("line1\nline2\nline3", blib_text_lines::move_up($text, 0));
		$this->assertEquals("line2\nline1\nline3", blib_text_lines::move_up($text, 1));
		$this->assertEquals("line1\nline3\nline2", blib_text_lines::move_up($text, 2));

		$this->assertEquals("line2\nline1\nline3", blib_text_lines::move_down($text, 0));
		$this->assertEquals("line1\nline3\nline2", blib_text_lines::move_down($text, 1));
		$this->assertEquals("line1\nline2\nline3", blib_text_lines::move_down($text, 2));
	}
}
