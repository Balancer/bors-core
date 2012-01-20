<?php

// Уровень h3 — это обычно подзаголовок второго уровня
// Заголовок подразделов

class bors_lcml_tag_pair_h3 extends bors_lcml_tag_pair
{
	static function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return "\n\n<h3>".lcml($title)."</h3>\n";
	}

	static function text($title, $params)
	{
		return "\n\n*** $title ***\n";
	}
}
