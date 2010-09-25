<?php

class bors_tests_texts_unittest extends PHPUnit_Framework_TestCase
{
    public function test_bors_close_tags()
    {
		$text = "<p><i>Test</i></p>";
        $this->assertEquals(bors_close_tags($text), $text);

		$text = "<p><i>Test</i>";
        $this->assertEquals(bors_close_tags($text), $text.'</p>');

		$text = "<p><i>Test";
        $this->assertEquals(bors_close_tags($text), $text.'</i></p>');

		$text = "<p><i>Test</div></table>";
        $this->assertEquals(bors_close_tags($text), '<table><div>'.$text.'</i></p>');
    }
}
