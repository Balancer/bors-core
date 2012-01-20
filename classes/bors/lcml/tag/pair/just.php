<?php

/**
	Выравнивание по ширине
	Пример использования: [just]Выравнивание по ширине[/just]
*/

class bors_lcml_tag_pair_just extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div align=\"justify\">".lcml($text)."</div>";
	}

	static function __unit_test($suite)
	{
		$code = '[just]Выравнивание по ширине[/just]';
		$suite->assertEquals('<div align="justify">Выравнивание по ширине</div>', lcml($code));
	}
}
