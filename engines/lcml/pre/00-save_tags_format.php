<?php

include('inc/strings.php');

function lcml_save_tags_format($txt)
{
		foreach(explode(' ', 'code music') as $tag)
		{
			$txt = preg_replace("!(\[$tag\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
			$txt = preg_replace("!(\[$tag [^\]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripq('$2')).'$3'", $txt);
		}
		
        return $txt;
}
