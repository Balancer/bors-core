<?php

/**
	Выравнивание по центру
	Пример использования: [center]Выводы[/center]
*/

class bors_lcml_tag_pair_center extends bors_lcml_tag_pair
{
	function html($text, &$params = array())
	{
		return "<div style=\"text-align: center !important\">".lcml($text)."</div>";
	}

	function text($text, &$params = array())
	{
		$width = 75;
		$result = array();
		foreach(explode("\n", wordwrap($text, $width)) as $s)
		{
			$pad = floor(($width-bors_strlen($s))/2);
			$result[] = str_repeat(' ', $pad).$s;
		}

		return join("\n", $result);
	}

	static function __unit_test($suite)
	{
		$code = '[center]Выводы[/center]';
		$suite->assertEquals('<div style="text-align: center !important">Выводы</div>', lcml($code));

		$code = '[center]Однажды, в студёную зимнюю пору я из лесу вышел, был сильный мороз. Гляжу, поднимается медленно в гору лошадка, везущая хворосту воз[/center]';
		$suite->assertEquals(
'                 Однажды, в студёную зимнюю пору я из лесу
                     вышел, был сильный мороз. Гляжу,
                   поднимается медленно в гору лошадка,
                           везущая хворосту воз'
		, lcml($code, array('output_type' => 'text')));
	}
}
