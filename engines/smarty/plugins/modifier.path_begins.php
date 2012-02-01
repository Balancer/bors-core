<?php

function smarty_modifier_path_begins($url, $test)
{
	$ud = @parse_url($test);
	return preg_match("/^".preg_quote($text)."/i", $url);
}
