<?php

bors_function_include('debug/in_console');

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
