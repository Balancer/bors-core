<?php

/**
	Выравнивание по правому краю
	Пример использования: [right]Выравнивание по правому краю[/right]
*/

class bors_lcml_tag_pair_right extends bors_lcml_tag_pair
{
	function html($text, &$params)
	{
		return "<div align=\"right\">".lcml($text)."</div>";
	}

	static function __unit_test($suite)
	{
		config_set('lcml_cache_disable', true);

		$code = '[right]Выравнивание по правому краю[/right]';
		$suite->assertEquals('<div align="right">Выравнивание по правому краю</div>', lcml($code));
	}
}
