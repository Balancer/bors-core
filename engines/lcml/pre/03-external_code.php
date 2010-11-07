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
	$text = preg_replace('!http://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)(\s+)!s', "\n[picasa]$2[/picasa]$3\n", $text);
	$text = preg_replace('!http://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)\?\S+!s', "\n[picasa]$2[/picasa]\n", $text);
	$text = preg_replace('!http://picasaweb.google.(com|ru)/lh/photo/([\w\-]+)(\s+|$)!s', "\n[picasa]$2[/picasa]$2\n", $text);

	$text = preg_replace('!(<script type="text/javascript" src="http://googlepage.googlepages.com/player.js"></script>)!ise', 'save_format("\1")', $text);

	return $text;
}
