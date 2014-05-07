<?php

// Уровень h3 — это обычно подзаголовок второго уровня
// Заголовок подразделов

class bors_lcml_tag_pair_h3 extends bors_lcml_tag_pair
{
	function html($title, &$params)
	{
		$params['skip_around_cr'] = true;

		return "\n".save_format("\n\n<h3>".lcml($title)."</h3>\n")."\n";
	}

	function text($title, &$params)
	{
		return "\n\n*** $title ***\n";
	}
}
