<?php

function lcml_old_forums_patches($txt)
{
	$txt = preg_replace_callback('!<a href=\"([^\"]+)\">\\1\[/URL\]!i', function($m) { return save_format("<a href=\"{$m[1]}\">".stripq($m[1]).'</a>');}, $txt);
	$txt = preg_replace_callback('!<a href=([^>]+)>\\1\[/URL\]!i', function($m) { return save_format("<a href=\"{$m[1]}\">".stripq($m[1]).'</a>');}, $txt);
	$txt = preg_replace_callback("!<a href=\"([^\"]+)\">(.+?)\[/URL\]!is", function($m) { return save_format("<a href=\"{$m[1]}\">".lcml(stripq($m[2])).'</a>');}, $txt);

	$txt = preg_replace("!<font size=([\-\d]+) color=([#\d]+)>(.*?)</font>!is", "[font $1][size $2]$3[/size][/font]", $txt);

	return $txt;
}
