<?php

// URLs processing
// Global vars: none
//
// (c) Balancer 2003-2010
// 07.06.04 0.1.2 исправлена обработка ссылок, "упирающихся" в тэг, например, <li> http://www.ru/<li>
// 08.06.04 0.1.3 если сервер теперь не возвращает кодировку, считается, что она - Windows-1251
// 28.06.04 0.1.4 исправления выделения ссылок, заканчивающихся [, |, ] и т.п.
// 01.08.04 0.1.5 * выкидываются тэги, если они включены в заголовок
// 10.08.04 0.1.6 * ограничен размер закачиваемой части (если это поддерживается сервером) первыми четырьмя килобайтами
// 09.09.04 0.1.7 + внешние ссылки - класс external. Внутренние - по имени
// 11.01.05 0.1.8 * обработка редиректов перенесена на модуль HTTP_Request. Введены таймауты.
// 12.01.05 0.1.9 + Введена кодировка по умолчанию и её запрос у сервера.
// 17.01.07 0.1.10 * Исправление нового формата HTTP_Request

    // Константы

    // кодировка, запрашиваемая по умолчанию
    $GLOBALS['lcml_request_charset_default'] = 'windows-1251';

    function lcml_strip_url($url)
    {
		return url_truncate($url, 70);
    }

    function lcml_urls_title($url)
    {
        if(0 && class_exists('Cache'))
        {
            $cache = new Cache();
            if($cache->get('url_titles-v4', $url))
                return $cache->last();
            else
                return $cache->set(lcml_urls_title_nocache($url), 7*86400);
        }
        else
            return lcml_urls_title_nocache($url);
    }

    function lcml_urls_title_nocache($url)
    {
		$original_url = $url;
		$anchor = "";

		if(preg_match("!^(.+)#(.+?)$!", $url, $m))
		{
			$url = $m[1];
			$anchor = $m[2];
		}

		$pure_url = $url;
		$query = "";

		if(preg_match("!^(.+?)\?(.+)$!", $url, $m))
		{
			$pure_url = $m[1];
			$query = $m[2];
		}

		if(config('lcml_balancer'))
		{
			//TODO: придумать хук вместо хардкода
			$data = url_parse($pure_url);
			if(in_array($data['host'], array('airbase.ru', 'balabot.balancer.ru', 'balancer.ru', 'bors.balancer.ru', 'forums.airbase.ru', 'forums.balancer.ru')))
			{
				$anchor = NULL;
				$obj = bors_load_uri($pure_url);
				if($obj)
					return "<a href=\"$original_url\">{$obj->title()}</a>";
			}
		}

		if(preg_match("!/[^/]+\.[^/]+$!", $pure_url) 
				&& !preg_match("!\.(html|htm|phtml|shtml|jsp|pl|php|php4|php5|cgi)$!i", $pure_url)
				&& !preg_match("!^http://[^/]+/?$!i", $pure_url)
		)
			return "<a href=\"{$original_url}\" class=\"external\">".lcml_strip_url($original_url)."</a>";

		if(!$query && class_exists('DataBaseHTS') && config('hts_db'))
        {
            $hts = new DataBaseHTS($url);
            if($title = $hts->get('title'))
                return "<a href=\"{$original_url}\">$title</a>";
        }

		if(!function_exists('curl_init'))
	        return "<a href=\"{$original_url}\" class=\"external\">".lcml_strip_url($original_url)."</a>";

		$header = array();
		$header[] = "Accept-Charset: {$GLOBALS['lcml_request_charset_default']}";
		$header[] = "Accept-Language: ru, en";

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_TIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_ENCODING => 'gzip,deflate',
//			CURLOPT_RANGE => '0-4095',
			CURLOPT_REFERER => $original_url,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_HTTPHEADER => $header,
//			CURLOPT_USERAGENT => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.94 Safari/534.13',
			CURLOPT_RETURNTRANSFER => true,
		));

//        if(preg_match("!lenta\.ru!", $url))
//			curl_setopt($ch, CURLOPT_PROXY, 'balancer.endofinternet.net:3128');

		require_once('inc/urls.php');

		debug_timing_start('http-get[engines/lcml/post/50-urls.php]: '.$url);
		debug_timing_start('http-get-total');
		$data = trim(curl_exec($ch));
		debug_timing_stop('http-get-total');
		debug_timing_stop('http-get[engines/lcml/post/50-urls.php]: '.$url);
//		$data = trim(curl_redir_exec($ch));

		$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
//		echo "$url: <xmp>"; print_r($data); echo "</xmp>"; exit();

        if(preg_match("!charset=(\S+)!i", $content_type, $m))
            $charset = $m[1];
        else
            $charset = '';

		curl_close($ch);

		if(empty($charset))
		{
	        if(preg_match("!<meta http\-equiv=\"Content\-Type\"[^>]+charset=(.+?)\"!i", $data, $m))
    	        $charset = $m[1];
			elseif(preg_match("!<meta[^>]+charset=(.+?)\"!i", $data, $m))
    	        $charset = $m[1];
		}

        if(!$charset)
			$charset = $GLOBALS['lcml_request_charset_default'];

        if(preg_match("!<title>(.+?)</title>!is",$data,$m)) //@file_get_contents($url)
        {
            if($charset)
                $m[1] = iconv($charset, config('internal_charset').'//IGNORE', $m[1]);

			$new_url = bors_substr(trim(preg_replace("!\s+!"," ",str_replace("\n"," ",strip_tags($m[1])))),0,256);
			if(!$new_url)
				$new_url = $url;

            return "<a href=\"{$original_url}\" class=\"external\">{$new_url}</a>";
        }

        return "<a href=\"{$original_url}\" class=\"external\">".lcml_strip_url($original_url)."</a>";
    }

    function lcml_urls($txt)
    {
		if(config('lcml_post_urls_disable'))
			return $txt;

		$taglist = config('lcml_tags_enabled');
		if($taglist && empty($taglist['post_urls']))
			return $txt;

        $txt=preg_replace("!\[(http://[^\s\|\]]+?)\]!ie","lcml_urls_title('$1')",$txt);
        $txt=preg_replace("!\[(www\.[^\s\|\]]+?)\]!ie","lcml_urls_title('http://$1')",$txt);
        $txt=preg_replace("!\[(ftp://[^\s\|\]]+?)\]!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!\[(ftp\.[^\s\|\]]+?)\]!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);
		$txt = preg_replace('!(?<=\s|^)(http://\S+(\S*\(\S*\))+)(?=\s|$)!sme', 'lcml_urls_title("$1")',$txt);
        $txt=preg_replace("!(?<=\s|^|\()(http://[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!ie","lcml_urls_title('$1').'$2'",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(http://[^\s<>\|\[\]\<\>]+)(?=\s|$)!ie","lcml_urls_title('$1')",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(www\.[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-|:)(?=\s|$)!ie","lcml_urls_title('http://$1').'$2'",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(www\.[^\s<>\|\[\]\<\>]+)(?=\s|$)!ie","lcml_urls_title('http://$1')",$txt);

        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp://[^\s<>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"$1\" class=\"external\">$1</a>",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s<>\|\[\]\<\>]+)(\)|\.|,|\!|\-)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>$2",$txt);
        $txt=preg_replace("!(?<=\s|^|\()(ftp\.[^\s<>\|\[\]\<\>]+)(?=\s|$)!i","<a href=\"ftp://$1\" class=\"external\">$1</a>",$txt);

        return $txt;
    }

//    echo lcml_urls("http://lenta.ru/economy/2005/01/11/ibm/");
