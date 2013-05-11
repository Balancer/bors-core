<?php

/**
	Тег, отображающий текст на стандартном жёлтом блоке предупреждения
	Пример использования: [notebox]Внимание! Сообщение ещё не сохранено![/notebox]
*/

class bors_lcml_tag_pair_notebox extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div class=\"yellow_box\">".lcml($text)."</div>";
	}

	static function __unit_test($suite)
	{
		$code = '[notebox]тест тега[/notebox]';
		$suite->assertEquals('<div class="yellow_box">тест тега</div>', lcml($code));
	}
}
