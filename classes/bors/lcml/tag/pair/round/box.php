<?php

/**
	Вывод очерченного блока с округлыми краями. Аналогично тегу [box]
	Пример использования: [round_box]Обратитие внимание![/round_box]
*/

class bors_lcml_tag_pair_round_box extends bors_lcml_tag_pair
{
	function html($text)
	{
//		var_dump($text);
		return save_format("<div class=\"round_box shadow8 mtop8\">\n".lcml($text)."\n<div class=\"clear\">&nbsp;</div></div>\n");
	}

	static function __unit_test($suite)
	{
		$code = '[round_box]Обратите внимание[/round_box]';
		$suite->assertRegexp('!<div class="[^"]+">\s*Обратите внимание\s*<div class="clear">&nbsp;</div></div>!', lcml($code));

		$code = "[round_box][b]Обратите внимание[/b][/round_box]";
		$suite->assertRegexp('!<div class="[^"]+">\s*<strong>Обратите внимание</strong>\s*<div class="clear">&nbsp;</div></div>!', lcml_bb($code));
	}
}
