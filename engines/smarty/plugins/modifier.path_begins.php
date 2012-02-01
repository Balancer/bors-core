<?php

function smarty_modifier_path_begins($url, $test)
{
	$ud = @parse_url($url);
	return preg_match("/^".preg_quote($test, '/')."/i", $ud['path']);
}
