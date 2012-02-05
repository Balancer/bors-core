<?php

function smarty_modifier_autolink($text)
{
	$text = preg_replace('!^(www\.\S+)$!', "<a href=\"http://$1\">$1</a>", $text);
	$text = preg_replace('!^(\S+@\S+)$!', "<a href=\"mailto:$1\">$1</a>", $text);
	return $text;
}
