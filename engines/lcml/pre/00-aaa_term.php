<?php

require_once('inc/strings.php');

function lcml_aaa_term($txt)
{
	$txt = str_replace('<?php', '&lt;?php', $txt);

	// term обязательно идёт первым! Потом — xmp.
	foreach(explode(' ', 'term xmp') as $tag)
	{
		$txt = preg_replace("!(\[$tag\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
		$txt = preg_replace("!(\[$tag [^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
		$txt = preg_replace("!(\[$tag\|[^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
	}

	return $txt;
}
