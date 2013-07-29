<?php

// Уровень h1 — это обычно заголовок самой страницы

class bors_lcml_tag_pair_h1 extends bors_lcml_tag_pair
{
	function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return "\n".save_format("\n\n<h1>".lcml($title)."</h1>\n");
	}

	function text($title, $params)
	{
		return "\n\n$title\n".str_repeat("=", bors_strlen($title))."\n\n";
	}
}
