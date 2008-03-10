<?
    function lcml_quotes($txt)
    {
		$txt = preg_replace("!\[quote=([^\]]+?),([^\]]+?)\]!i","[quote|$1, $2:]", $txt);
		$txt = preg_replace("!\[quote=(.+?)\]!i","[quote|$1:]", $txt);
        return $txt;
    }
