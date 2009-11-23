<?php
function lst_news($txt)
{
//	#news http://url|title|text
	list($url, $title, $text) = explode('|',$txt.'||');
	if($url)
		$title="<a href=\"$url\">$title</a>";
	return "<dl class=\"box\"><dt>$title</dt><dd>$text<div align=\"right\"><small><a href=\"$url\">дальше...</a></small></div></dd></dl>\n";
}
