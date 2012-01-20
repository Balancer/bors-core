<?php

// Просто жирный текст.

class bors_lcml_tag_pair_b extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<strong>".$this->lcml($text)."</strong>";
	}

	function text($text)
	{
		return "*".$this->lcml($text)."*";
	}

	static function __unit_test($suite)
	{
		$code = '[b]Здавствуй, мир![/b]';
		$suite->assertEquals('<strong>Здавствуй, мир!</strong>', lcml($code));
		$suite->assertEquals('*Здавствуй, мир!*', lcml($code, array('output_type' => 'text')));
	}
}
