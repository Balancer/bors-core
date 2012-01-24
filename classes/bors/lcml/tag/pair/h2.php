<?php

// Уровень h2 — это обычно подзаголовок первого уровня.
// Заголовок разделов.

class bors_lcml_tag_pair_h2 extends bors_lcml_tag_pair
{
	function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return save_format("\n\n<h2>".lcml($title)."</h2>\n");
	}

	function text($title, $params)
	{
		$params['skip_around_cr'] = true;

		return "\n\n$title\n".str_repeat("-", bors_strlen($title))."\n";
	}

	static function __unit_test($suite)
	{
		$code = '[h2]Здравствуй, мир![/h2]';
		$suite->assertEquals("<h2>Здравствуй, мир!</h2>", trim(lcml($code)));
		$suite->assertEquals("Здравствуй, мир!\n----------------", lcml($code, array('output_type' => 'text')));
	}
}
