<?php

require_once BORS_CORE.'/inc/functions/debug/in_console.php';

function debug_pre($text)
{
	if(debug_in_console())
		echo $text;
	else
		echo "<xmp>{$text}</xmp>\n";
}
