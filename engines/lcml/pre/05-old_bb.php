<?
    function lcml_old_bb($txt)
    {
		$txt = preg_replace("!\[size=(\d+)\](.+?)\[/size\]!is", "[html_font size=$1]$2[/html_font]", $txt);
		
		return $txt;
	}
