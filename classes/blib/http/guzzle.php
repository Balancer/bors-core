<?php

class blib_http_guzzle
{
	static function get($url, $raw = false, $max_length = false)
	{
	}

	// Работает с объектами не более одного мегабайта. Можно настроить новым параметром max_length
	static function get_cached($url, $ttl = 86400, $raw = false, $force = false, $max_length = 1000000)
	{
		//FIXME: Придумать более изящный способ
		$cache_status_save = config('cache_disabled');
		config_set('cache_disabled', false);
		debug_count_inc('bors_lib_url: get_cached('.$url.')');
		$cache = new bors_cache();
		if($cache->get('bors_lib_http.get_cached-v2', $url) && !$force)
		{
			debug_count_inc('bors_lib_url: get_cached('.$url.'):found');
			$res = $cache->last();
			config_set('cache_disabled', $cache_status_save);
			return $res;
		}

		$x = self::get_ex($url, array('is_raw' => $raw));
		$content = $x['content'];

		// Запоминаем не более одного мегабайта, а то по max_allowed_packet можно влететь.
		if(strlen($content) > $max_length)
			$content = substr($content, 0, $max_length);

		debug_count_inc('bors_lib_url: get_cached('.$url.'):store');
		$cache->set($content, $ttl);
		config_set('cache_disabled', $cache_status_save);
		return $content;
	}

	static function get_ex($url, $params = array())
	{
		$raw		= popval($params, 'is_raw');
		$charset	= popval($params, 'charset');
		$timeout	= popval($params, 'timeout', 15);
		$referer	= popval($params, 'referer', $url);
		$save_file	= popval($params, 'file');

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

		if(preg_match(config('urls.skip_load_ext_regexp'), $pure_url) && empty($params['blobs_enabled']))
			return array('content' => NULL, 'content_type' => NULL, 'error' => true);

		$header = array();
		if(($request_charset = $charset ? $charset : config('lcml_request_charset_default')))
			$header[] = "Accept-Charset: ".$request_charset;

		$header[] = "Accept-Language: ru, en";

		if(preg_match('/(livejournal.com|imageshack.us|upload.wikimedia.org|www.defencetalk.com|radikal.ru)/', $url))
			$timeout *= 3;

		if(preg_match('/\.gif$/i', $url)) // Возможно — большая анимация
			$timeout *= 2;
/*
		$curl_options = array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_ENCODING => 'gzip,deflate',
//			CURLOPT_RANGE => '0-4095',
			CURLOPT_REFERER => $referer,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_HTTPHEADER => $header,
//TODO: сделать перебор разных UA при ошибке
//			CURLOPT_USERAGENT => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.94 Safari/534.13',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
		);
*/
		$client = new Guzzle\Http\Client();

		$params = array();

		if($save_file)
			$params['save_to'] = $save_file;

		if(config('proxy.force_regexp') && preg_match(config('proxy.force_regexp'), $url))
			$params['proxy'] = 'http://' . config('proxy.forced');

		$start_time = time();

		$request = $client->get($url, $params);
		$response = $request->send();

		$body = $response->getBody();

//		echo "body=",$body,PHP_EOL;

		// Пытаемся ловить защиту от DDOS, требующую релоада с Cookie.

		if(strlen($body) <  2000 && preg_match('/document.cookie=.*(__DDOS_COOKIE)=(\w+);/', $body, $m) && preg_match('/window.location.reload\(true\)/', $body))
		{
			$request->addCookie($m[1], $m[2]);
			$response = $request->send();
			$body = $response->getBody();
		}

//		echo "size=" . $response->getBody()->getSize() . "\n";

		$time = time() - $start_time;
		if($time > 5 || $response->getBody()->getSize() > 1000000)
			bors_debug::syslog('guzzle-warnings', "Too long or too big download for $original_url; time=$time; info=".print_r($response->getInfo(), true));

		if(preg_match('/balancer\.ru|airbase\.ru/', $original_url))
			bors_debug::syslog('guzzle-warnings', "Try to load $original_url");

		if(!$body)
		{
			//TODO: оформить хорошо. Например, отправить отложенную задачу по пересчёту
			//И выше есть такой же блок.
//			$err_str = curl_error($ch);
//			curl_close($ch);

			if($save_file)
				@unlink($save_file);

			debug_hidden_log('guzzle-error', "Guzzle ($url) error: now unknown yet");
			return array('content' => NULL, 'content_type' => NULL, 'error' => "BORS: Not implemente yet");
		}

		if(!$raw)
			$body = trim(str_replace("\r", "", $body));

		$content_type = $response->getContentType();

		if(!$charset && !$raw && preg_match("!charset\s*=\s*(\S+)!i", $content_type, $m))
    	    $charset = $m[1];

		if(!$raw)
		{
			if(empty($charset))
			{
    	    	if(preg_match("!<meta\s+http\-equiv\s*=\s*\"Content\-Type\"[^>]+charset\s*=\s*(.+?)\"!i", $body, $m))
	    	    	$charset = $m[1];
				elseif(preg_match("!<meta[^>]+charset\s*=\s*(.+?)\"!i", $body, $m))
			        $charset = $m[1];
				// <meta http-equiv="Content-Type" content="text/html;UTF-8">
				elseif(preg_match("!<meta [^>]+Content-Type[^>]+content=\"text/html;([^>]+)\">!i", $body, $m))
			        $charset = $m[1];
				elseif(preg_match("!<meta charset=\"(.+?)\" />\">!i", $body, $m))
			        $charset = $m[1];
			}

	    	if(!$charset)
				$charset = config('lcml_request_charset_default');

			if($charset)
				$body = iconv($charset, config('internal_charset').'//IGNORE', $body);
		}

	    return array('content' => $body, 'content_type' => $content_type, 'error' => false);
	}

	static function __dev()
	{
		$url = "http://dnr-news.com/dnr/10520-eduard-limonov-pribyl-na-donbass.html";
/*
<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		document.cookie="__DDOS_COOKIE=d2002e16ae03f7f4d34aee7850957fe8; max-age=604800; path=/";
		var nc = function() {return document.cookie.indexOf("__DDOS_COOKIE=d2002e16ae03f7f4d34aee7850957fe8")==-1;};
		var w = function() {document.body.innerHTML = document.getElementsByTagName("noscript")[0].textContent;};
		if (!window.opera) {
			if (!nc()) {window.location.reload(true);}
			var r = function() {if (nc()) w();};
		} else {
			var r = function () {
				if (!nc()) {window.location.reload(true);}
				else {w();}
			}
		}
	</script>
	</head>
	<body onload="r()">
	<noscript>You have to turn on javascript and cookies support in browser to visit this site.<br />
	Для доступа к сайту Ваш браузер должен поддерживать javascript и cookie.<br />
	</noscript>
	</body>
	</html>
*/
		print_r(self::get_ex($url));
	}
}
