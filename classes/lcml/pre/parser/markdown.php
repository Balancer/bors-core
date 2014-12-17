<?php

//	Элементы поддержки markdown в lcml
//	http://www.aviaport.ru/services/

class lcml_parser_pre_markdown extends bors_lcml_parser
{
	function parse($text)
	{
		$text = preg_replace('/^###\s+(.+)\s+###\s*$/m', '<h3>$1</h3>', $text);
		$text = preg_replace('/^###\s+(.+)\s+$/m', '<h3>$1</h3>', $text);

		return $text;
	}

	function __unit_test($suite)
	{
		$suite->assertEquals('<h3>Test</h3>', lcml::parse("### Test"));
		$suite->assertEquals('<h3>Test</h3>', lcml::parse("###  Test ### "));
	}
}
