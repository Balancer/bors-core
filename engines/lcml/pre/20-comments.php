<?
    function lcml_comments($txt)
    {
		if(empty($GLOBALS['lcml']['sharp_not_comment']))
	        $txt=preg_replace("!^# .+$!m","",$txt);

        return $txt;
    }
