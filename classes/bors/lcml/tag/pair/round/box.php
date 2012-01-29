<?php

/**
	Вывод очерченного блока с округлыми краями. Аналогично тэгу [box]
	Пример использования: [round_box]Обратитие внимание![/round_box]
*/

class bors_lcml_tag_pair_round_box extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<div class=\"round_box shadow8 mtop8\">".lcml($text)."<div class=\"clear\">&nbsp;</div></div>";
	}

	static function __unit_test($suite)
	{
		$code = '[round_box]Обратите внимание[/round_box]';
		$suite->assertEquals('<div class="round_box shadow8">Обратите внимание<div class="clear">&nbsp;</div></div>', lcml($code));
	}
}
