<?php

function smarty_modifier_autolinks($text)
{
	$text = preg_replace('!(^|\s+)(www\.\S+)(\s+|$)!m', "$1<a href=\"http://$2\">$2</a>$3", $text);
	$text = preg_replace('!(^|\s+)(\S+@\S+)(\s+|$)!', "$1<a href=\"mailto:$2\">$2</a>$3", $text);
	return $text;
}
