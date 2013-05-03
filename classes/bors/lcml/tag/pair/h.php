<?php

// Уровень h — это обычно заголовок внутри текстов
// Заголовок разделов. Транслируется в <h3> (так как <h1> — заголовок страниц, <h2> — разделов)

class bors_lcml_tag_pair_h extends bors_lcml_tag_pair
{
	function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return save_format("\n\n<h3>".lcml($title)."</h3>\n");
	}

	function text($title, $params)
	{
		return "\n\n$title\n".str_repeat("-", bors_strlen($title))."\n";
	}
}
