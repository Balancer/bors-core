<?php

require_once('inc/strings.php');

function lcml_save_tags_format($txt)
{
	$txt = str_replace('<?php', '&lt;?php', $txt);

	foreach(explode(' ', 'code delayed graph graphviz html javascript math music php') as $tag)
	{
		$txt = preg_replace_callback("!(\[$tag\])(.+?)(\[/$tag\])!is", function($m) { return $m[1] . save_format(stripq($m[2])).$m[3];}, $txt);
		$txt = preg_replace_callback("!(\[$tag [^]]+\])(.+?)(\[/$tag\])!is", function($m) { return $m[1].save_format(stripq($m[2])).$m[3];}, $txt);
		$txt = preg_replace_callback("!(\[$tag\|[^]]+\])(.+?)(\[/$tag\])!is", function($m) { return $m[1].save_format(stripq($m[2])).$m[3];}, $txt);
	}

	return $txt;
}
