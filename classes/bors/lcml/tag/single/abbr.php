<?php

/**
	Старый формат тега аббревиатур.
	Пример использования: [abbr РХБЗ|Радиационная, химическая и биологическая защита]
*/

class bors_lcml_tag_single_abbr extends bors_lcml_tag_single
{
	function html($text, &$params)
	{
		if(empty($params['abbr']))
			return "<abbr title=\"{$params['description']}\">{$params['orig']}</abbr>";

		return "<abbr title=\"{$params['tail']}\">{$params['abbr']}</abbr>";
	}

	static function __unit_test($suite)
	{
		require_once('engines/lcml.php');
		$code = '[abbr РХБЗ|Радиационная, химическая и биологическая защита]';
		$suite->assertEquals('<abbr title="Радиационная, химическая и биологическая защита">РХБЗ</abbr>', lcml($code));
		$suite->assertEquals(
			'<abbr title="Радиационная, химическая и биологическая защита">РХБЗ</abbr>',
			lcml('[abbr=РХБЗ Радиационная, химическая и биологическая защита]'));
	}
}
