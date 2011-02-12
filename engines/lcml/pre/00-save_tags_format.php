<?php

require_once('inc/strings.php');

function lcml_save_tags_format($txt)
{
	$txt = str_replace('<?php', '&lt;?php', $txt);

	foreach(explode(' ', 'code delayed graphviz html javascript math music php pre term xmp') as $tag)
	{
		$txt = preg_replace("!(\[$tag\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
		$txt = preg_replace("!(\[$tag [^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
		$txt = preg_replace("!(\[$tag\|[^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
	}

	return $txt;
}
