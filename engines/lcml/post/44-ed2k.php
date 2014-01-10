<?php

function lcml_ed2k($txt)
{
	include_once("inc/filesystem.php");
	$txt = preg_replace_callback("!(ed2k://\|file\|([^\|]+)\|(\d+)\|\w+\|?/?)!i",
		function($m) { return "<a href=\"{$m[1]}\">ed2k: ".urldecode($m[2])."</a> [".smart_size($m[3])."]";}, $txt);

	return $txt;
}
