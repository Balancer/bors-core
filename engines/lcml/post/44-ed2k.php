<?php

function lcml_ed2k($txt)
{
	include_once("inc/filesystem.php");
	$txt = preg_replace("!(ed2k://\|file\|([^\|]+)\|(\d+)\|\w+\|?/?)!ie", "'<a href=\"$1\">ed2k: '.urldecode('$2').'</a> ['.smart_size($3).']'", $txt);

	return $txt;
}
