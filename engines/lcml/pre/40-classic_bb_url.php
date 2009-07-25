<?
    function lcml_classic_bb_url($txt)
    {
		$txt = preg_replace("!\[url=(\S+) target=(\w+)\](.+?)\[/url\]!is", "<a href=\"$1\" target=\"$2\">$3</a>", $txt);
		$txt = preg_replace("!\[url=([^]]+)\](.+?)\[/url\]!is", "<a href=\"$1\">$2</a>", $txt);

		return $txt;
	}
