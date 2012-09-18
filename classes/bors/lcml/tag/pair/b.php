<?php

// Просто жирный текст.

class bors_lcml_tag_pair_b extends bors_lcml_tag_pair
{
	function html($text, &$params)
	{
		return "<strong>".$this->lcml($text)."</strong>";
	}

	function text($text, &$params)
	{
		return "*".$this->lcml($text)."*";
	}

	static function __unit_test($suite)
	{
		$code = '[b]Здравствуй, мир![/b]';
		$suite->assertEquals('<strong>Здравствуй, мир!</strong>', lcml($code));
		$suite->assertEquals('*Здравствуй, мир!*', lcml($code, array('output_type' => 'text')));
	}
}
