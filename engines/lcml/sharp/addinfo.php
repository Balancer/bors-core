<?php

function lst_addinfo($info)
{
	@list($time, $link, $name, $text) = explode('|', $info);
	
	if($link)
		$name = "<a href=\"$link\">{$name}</a>";

	return "<dl class=\"box\"><dt>{$time} {$name}</dt><dd>".lcml($text)."</dd></dl>\n";
}
