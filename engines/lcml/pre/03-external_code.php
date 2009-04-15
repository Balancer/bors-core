<?php

function lcml_external_code($text)
{
	// YouTube код и ссылки
	if(!config('lcml_external_parse_youtube_disable'))
	{
		$text = preg_replace('!http://www\.youtube\.com/watch\?v=(\w+)\S*!', '[youtube]$1[/youtube]', $text);
		$text = preg_replace('!<object.*?http://www\.youtube\.com/v/(\w+).*?</object>!', '[youtube]$1[/youtube]', $text);
	}

	return $text;
}
