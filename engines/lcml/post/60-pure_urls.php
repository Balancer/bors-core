<?
    function lcml_pure_strip_url($url)
    {
            return strlen($url)>77?substr($url,0,50).' [ ... ] '.substr($url,-20):$url;
    }

    function lcml_pure_urls_title($url)
    {
        return "<a href=\"$url\" class=\"external\">".lcml_pure_strip_url($url)."</a>";
    }

    function lcml_pure_urls($txt)
    {
		if(!empty($GLOBALS['lcml']['params']['noautouri']))
			return $txt;
	
        $txt=preg_replace("!\[(http://[^\s\|\]]+?)\]!ie","lcml_pure_urls_title('$1')",$txt);
        $txt=preg_replace("!\[(www\.[^\s\|\]]+?)\]!ie","lcml_pure_urls_title('http://$1')",$txt);
        $txt=preg_replace("!\[(ftp://[^\s\|\]]+?)\]!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!\[(ftp\.[^\s\|\]]+?)\]!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);

        $txt=preg_replace("!(?<=\s|^|\()(http://[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!ie","lcml_pure_urls_title('$1').'$2'",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(http://[^\s<>\|\[\]\<\>]+)(?=\s|$)!ie","lcml_pure_urls_title('$1')",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(www\.[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!ie","lcml_pure_urls_title('http://$1').'$2'",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(www\.[^\s<>\|\[\]\<\>]+)(?=\s|$)!ie","lcml_pure_urls_title('http://$1')",$txt);

        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s<>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s<>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);

        return $txt;
    }
?>
