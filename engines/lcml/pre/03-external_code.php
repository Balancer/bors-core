<?php

function lcml_external_code($text)
{
	// YouTube код и ссылки
	if(!config('lcml_external_parse_youtube_disable'))
	{
		$text = preg_replace('!(^|\s)http://www\.youtube\.com/watch\?v=(\S+)(\s|$)!m', "\n[youtube]$2[/youtube]\n", $text);
		$text = preg_replace('!<object.*?http://www\.youtube\.com/v/([^&]+).*?</object>!s', "\n[youtube]$1[/youtube]\n", $text);
	}

	return $text;
}
