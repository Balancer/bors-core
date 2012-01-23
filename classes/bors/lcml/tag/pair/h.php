<?php

// Уровень h — это обычно заголовок первого уровня.
// Заголовок разделов. Транслируется в <h2> (так как <h1> — заголовок страниц)

class bors_lcml_tag_pair_h extends bors_lcml_tag_pair
{
	function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return save_format("\n\n<h2>".lcml($title)."</h2>\n");
	}

	function text($title, $params)
	{
		return "\n\n$title\n".str_repeat("-", bors_strlen($title))."\n";
	}
}
