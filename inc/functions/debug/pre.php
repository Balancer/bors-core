<?php

bors_function_include('debug/in_console');

function debug_pre($text)
{
	if(debug_in_console())
		echo $text;
	else
		echo "<xmp>{$text}</xmp>\n";
}
