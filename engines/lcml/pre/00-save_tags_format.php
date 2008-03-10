<?php
	//TODO: обновить lcml-кэши с [code, созданные до 10.10.2007 10:56

    function lcml_save_tags_format($txt)
    {
		foreach(split(' ', 'code music') as $tag)
		{
			$txt = preg_replace("!(\[$tag\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripslashes('$2')).'$3'", $txt);
			$txt = preg_replace("!(\[$tag [^\]]+\])(.+?)(\[/$tag\])!ise", "'$1'.save_format(stripslashes('$2')).'$3'", $txt);
		}
		
        return $txt;
    }
