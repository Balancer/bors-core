<?php

/**
	Выравнивание по центру
	Пример использования: [center]Выводы[/center]
*/

class bors_lcml_tag_pair_center extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div style=\"text-align: center !important\">".lcml($text)."</div>";
	}

//TODO: реализовать свёртку через нарезку по ширине и центровку каждой строки.
/*
	function text($text)
	{
		$screen_width = 80;
		return "<div style=\"text-align: center !important\">".lcml($text)."</div>";
	}
*/
	static function __unit_test($suite)
	{
		$code = '[center]Выводы[/center]';
		$suite->assertEquals('<div style="text-align: center !important">Выводы</div>', lcml($code));
	}
}
