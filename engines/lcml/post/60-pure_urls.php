<?php
    function lcml_pure_strip_url($url)
    {
            return strlen($url)>77?substr($url,0,50).' [ ... ] '.substr($url,-20):$url;
    }

    function lcml_pure_urls_title($url)
    {
    	$url_data = parse_url($url);
    	if(!empty($url_data['host']) && ($skip_domains = config('lcml.urls.skip_domains')))
    	{
			$host = str_replace('www.', '', $url_data['host']);
			if(in_array($host, $skip_domains))
				return $url;
		}

		$data = url_parse($url);
		$external = @$data['local'] ? '' : ' class="external foo-a2"';
		$blacklist = $external;
		if($wl = config('seo_domains_whitelist_regexp', @$_SERVER['HTTP_HOST']))
			if(preg_match('!'.$wl.'!', $data['host']))
				$blacklist = false;

        return "<a href=\"$url\"{$external} ".($blacklist ? 'rel="nofollow" ' : '').">".url_truncate($url, 80)."</a>";
    }

    function lcml_pure_urls($txt)
    {
		if(config('is_debug'))
			file_put_contents('/tmp/111.txt', $txt);

		if(!empty($GLOBALS['lcml']['params']['noautouri']))
			return $txt;

        $txt=preg_replace_callback("!\[(https?://[^\s\|\]]+?)\]!i", function($m) { return lcml_pure_urls_title($m[1]);}, $txt);
        $txt=preg_replace_callback("!\[(www\.[^\s\|\]]+?)\]!i", function($m) { return lcml_pure_urls_title('http://'.$m[1]);}, $txt);
        $txt=preg_replace("!\[(ftp://[^\s\|\]]+?)\]!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!\[(ftp\.[^\s\|\]]+?)\]!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);

		// (http://....)
        $txt=preg_replace_callback("!(?<=\()(https?://[^\s<>\|\<\>\)]+)!i", function($m) { return lcml_pure_urls_title($m[1]);}, $txt);

        $txt=preg_replace_callback("!(?<=\s| |^|\()(https?://[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!i", function($m) { return lcml_pure_urls_title($m[1]).$m[2];}, $txt);
        $txt=preg_replace_callback("!(?<=\s| |^|\()(https?://[^\s<>\|\[\]\<\>]+)(?=\s|$)!i", function($m) { return lcml_pure_urls_title($m[1]);}, $txt);
        $txt=preg_replace_callback("!(?<=\s| |^| \()(www\.[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!i",function($m) { return lcml_pure_urls_title('http://'.$m[1]).$m[2];}, $txt);
        $txt=preg_replace_callback("!(?<=\s| |^| \()(www\.[^\s<>\|\[\]\<\>]+)(?=\s|$)!i", function($m) { return lcml_pure_urls_title('http://'.$m[1]);}, $txt);

        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s <>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s <>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s <>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s <>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);

        return $txt;
    }
