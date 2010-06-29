<?php

function lcml_external_code($text)
{
	// YouTube код и ссылки
	if(!config('lcml_external_parse_youtube_disable'))
	{
		$text = preg_replace('!(^|\s)http://www\.youtube\.com/watch\?v=(\S+)(\s|$)!m', "\n[youtube]$2[/youtube]\n", $text);
		$text = preg_replace('!<object.*?http://www\.youtube\.com/v/([^&]+).*?</object>!s', "\n[youtube]$1[/youtube]\n", $text);
	}

	// PicasaWeb
	$text = preg_replace('!http://picasaweb.google.com/lh/photo/([\w\-]+)\?\S+!s', "\n[picasa]$1[/picasa]\n", $text);
	$text = preg_replace('!http://picasaweb.google.com/lh/photo/([\w\-]+)(\s+|$)!s', "\n[picasa]$1[/picasa]$2\n", $text);

	return $text;
}
