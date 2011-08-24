<?php

// Уровень h1 — это обычно заголовок самой страницы

class bors_lcml_tags_pairs_h1 extends bors_lcml_tags_pair
{
	static function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return "\n\n<h1>".lcml($title)."</h1>\n";
	}

	static function text($title, &$params)
	{
		return "\n\n$title\n".str_repeat("=", bors_strlen($title))."\n\n";
	}
}
