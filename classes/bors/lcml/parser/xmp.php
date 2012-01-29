<?php

require_once('inc/strings.php');

class bors_lcml_parser_xmp extends bors_lcml_parser
{
	function html($text)
	{
		$text = str_replace('<?php', '&lt;?php', $text);

		// term обязательно идёт первым! Потом — xmp.
		foreach(explode(' ', 'term xmp') as $tag)
		{
			$text = preg_replace("!(\[$tag\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $text);
			$text = preg_replace("!(\[$tag [^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $text);
			$text = preg_replace("!(\[$tag\|[^]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $text);
		}

		return $text;
	}
}
