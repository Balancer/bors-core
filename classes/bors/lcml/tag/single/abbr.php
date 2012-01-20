<?php

/**
	Старый формат тэга аббревиатур.
	Пример использования: [abbr РХБЗ|Радиационная, химическая и биологическая защита]
*/

class bors_lcml_tag_single_abbr extends bors_lcml_tag_single
{
	function html($params)
	{
		return "<abbr title=\"{$params['description']}\">{$params['orig']}</abbr>";
	}

	static function __unit_test($suite)
	{
		$code = '[abbr РХБЗ|Радиационная, химическая и биологическая защита]';
		$suite->assertEquals('<abbr title="Радиационная, химическая и биологическая защита">РХБЗ</abbr>', lcml($code));
	}
}
