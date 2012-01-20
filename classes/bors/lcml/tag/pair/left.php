<?php

/**
	Выравнивание по левому краю
	Пример использования: [left]Выравнивание по левому краю[/left]
*/

class bors_lcml_tag_pair_left extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div align=\"left\">".lcml($text)."</div>";
	}

	static function __unit_test($suite)
	{
		$code = '[left]Выравнивание по левому краю[/left]';
		$suite->assertEquals('<div align="left">Выравнивание по левому краю</div>', lcml($code));
	}
}
