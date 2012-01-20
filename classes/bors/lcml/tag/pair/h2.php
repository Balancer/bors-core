<?php

// Уровень h2 — это обычно подзаголовок первого уровня.
// Заголовок разделов.

class bors_lcml_tag_pair_h2 extends bors_lcml_tag_pair
{
	static function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return "\n\n<h2>".lcml($title)."</h2>\n";
	}

	static function text($title, $params)
	{
		return "\n\n$title\n".str_repeat("-", bors_strlen($title))."\n";
	}
}
