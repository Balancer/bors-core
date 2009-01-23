<?php

function lcml_old_forums_patches($txt)
{
	$txt = preg_replace('!<a href=\"([^\"]+)\">\\1\[/URL\]!ie', "save_format('<a href=\"$1\">'.stripq('$1').'</a>')", $txt);
	$txt = preg_replace('!<a href=([^>]+)>\\1\[/URL\]!ie', "save_format('<a href=\"$1\">'.stripq('$1').'</a>')", $txt);
	$txt = preg_replace("!<a href=\"([^\"]+)\">(.+?)\[/URL\]!ise", "save_format('<a href=\"$1\">'.lcml(stripq('$2')).'</a>')", $txt);

	return $txt;
}
