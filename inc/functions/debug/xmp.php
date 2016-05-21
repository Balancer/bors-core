<?php

require_once BORS_CORE.'/inc/functions/debug/in_console.php';

function debug_xmp($text, $string = false)
{
	if(debug_in_console())
		$out = $text;
	else
		$out = "<xmp>{$text}</xmp>\n";

	if(!$string)
		echo $out;

	return $out;
}
