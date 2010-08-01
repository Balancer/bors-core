<?php

function http_get($url)
{
	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_TIMEOUT => 5,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_ENCODING => 'gzip, deflate',
//		CURLOPT_RANGE => '0-4095',
//		CURLOPT_REFERER => $original_url,
		CURLOPT_AUTOREFERER => true,
//		CURLOPT_HTTPHEADER => $header,
		CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
		CURLOPT_RETURNTRANSFER => true,
	));

	if(preg_match("!lenta\.ru!", $url))
		curl_setopt($ch, CURLOPT_PROXY, 'home.balancer.ru:3128');

	$data = curl_exec($ch);

//	print_r($data);

	curl_close($ch);

	return $data;
}

function http_get_content($url, $raw = false)
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

	if(preg_match("/\.(pdf|zip|rar|djvu|mp3|avi|mkv|mov|mvi|qt)$/i", $pure_url))
		return "";

	$header = array();
	if(($cs = config('lcml_request_charset_default')))
		$header[] = "Accept-Charset: ".$cs;
	$header[] = "Accept-Language: ru, en";

	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_TIMEOUT => 10,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_ENCODING => 'gzip,deflate',
		CURLOPT_RANGE => '0-4095',
		CURLOPT_REFERER => $original_url,
		CURLOPT_AUTOREFERER => true,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_USERAGENT => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
		CURLOPT_RETURNTRANSFER => true,
	));

//    if(preg_match("!lenta\.ru!", $url))
//		curl_setopt($ch, CURLOPT_PROXY, 'balancer.endofinternet.net:3128');

	$data = trim(curl_exec($ch));
//	$data = trim(curl_redir_exec($ch));

	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
//	echo "<xmp>"; print_r($data); echo "</xmp>";

    if(preg_match("!charset=(\S+)!i", $content_type, $m))
        $charset = $m[1];
    else
        $charset = '';

	curl_close($ch);

	if($raw)
		return $data;

	if(empty($charset))
	{
        if(preg_match("!<meta http\-equiv=\"Content\-Type\"[^>]+charset=(.+?)\"!i", $data, $m))
	        $charset = $m[1];
		elseif(preg_match("!<meta[^>]+charset=(.+?)\"!i", $data, $m))
	        $charset = $m[1];
	}

    if(!$charset)
		$charset = config('lcml_request_charset_default');

	if($charset)
		$data = iconv($charset, config('internal_charset').'//IGNORE', $data);

    return $data;
}

function query_explode($query_string)
{
	$data = array();
	foreach(explode('&', $query_string) as $pair)
	{
		if(preg_match('/^(.+)=(.+)$/', $pair, $m))
		{
			if(preg_match('/^(\w+)\[\]$/', $var, $mm))
				$data[urldecode($mm[1])][] = urldecode($m[2]);
			else
				$data[urldecode($m[1])] = urldecode($m[2]);
		}
		else
			$data[urldecode($pair)] = NULL;
	}

	return $data;
}
