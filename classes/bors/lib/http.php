<?php

class bors_lib_http
{
	function get($url, $raw = false, $max_length = false)
	{
		require_once('inc/http.php');
		return http_get_content($url, $raw, $max_length);
	}

	// Работает с объектами не более одного мегабайта. Можно настроить новым параметром max_length
	function get_cached($url, $ttl = 86400, $raw = false, $force = false, $max_length = 1000000)
	{
		$cache = new Cache();
		if($cache->get('bors_lib_http.get_cached-v1', $url) && !$force)
			return $cache->last();

		$content = self::get($url, $raw);

		// Запоминаем не более одного мегабайта, а то по max_allowed_packet можно влететь.
		if(strlen($content) > $max_length)
			$content = substr($content, 0, $max_length);

		return $cache->set($content, $ttl);
	}

	static function url_unshort($url, $type, $loop = 0)
	{
		if($loop > 3)
			return $url;

		switch($type)
		{
			case 'youtu.be':
				if(preg_match('!http://youtu.be/([^\?]+)\?a!', $url, $m))
					return 'http://www.youtube.com/watch?v='.$m[1];
		}

		$head = self::get_header($url);
		if(empty($head['Location']))
			return self::url_encode_lite($url);

		return self::url_unshort($head['Location'], $type, $loop+1);
	}

	function get_header($url)
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 1,		// return header
			CURLOPT_NOBODY => 1,		// no body return. it will faster
			CURLOPT_TIMEOUT => defval($curl_options, 'timeout', 5),
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
			CURLOPT_REFERER => defval($curl_options, 'referer', $url),
			CURLOPT_AUTOREFERER => true,
		));

		$headers = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		$parsed_headers = array();
		foreach(explode("\n", $headers) as $x)
		{
			if(preg_match('/^(\S+):\s+(.+)$/', trim($x), $m))
				$parsed_headers[$m[1]] = $m[2];
		}

		return $parsed_headers;
	}

	/**
		Возвращает бинарные данные со ссылки с вспомогательными
		данными, такими, как тип MIME, имя файла и расширение, если
		есть и т.д. По умолчанию использует кеширование в течении
		1 часа.
	*/
	static function get_bin($url, $curl_options = array())
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_TIMEOUT => defval($curl_options, 'timeout', 15),
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_ENCODING => 'gzip, deflate',
			CURLOPT_REFERER => defval($curl_options, 'referer', $original_url),
			CURLOPT_AUTOREFERER => true,
//			CURLOPT_HTTPHEADER => $header,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
			CURLOPT_RETURNTRANSFER => true,
		));

//	if(preg_match("!lenta\.ru!", $url))
//		curl_setopt($ch, CURLOPT_PROXY, 'home.balancer.ru:3128');

		$content = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		switch($info['content_type'])
		{
			case 'image/png':
				$ext = 'png';
				break;
			default:
				$ext = NULL;
				break;
		}
		$info['ext'] = $ext;

		$info['content'] = $content;

		return $info;
	}

	static function get_bin_content($url, $curl_options = array())
	{
		$data = self::get_bin($url, $curl_options);
		return @$data['content'];
	}

	// Спёрто с http://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php
	function parse_raw_http_request($data, $content_type)
	{
		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $content_type, $matches);
		$boundary = $matches[1];

		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+".preg_quote($boundary)."/", $data);
		array_pop($a_blocks);

		// loop data blocks
		foreach ($a_blocks as $id => $block)
		{
			if (empty($block))
				continue;

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== FALSE)
			{
				// match "name", then everything after "stream" (optional) except for prepending newlines 
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			}
			// parse all other fields
			else
			{
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}

			$a_data[$matches[1]] = $matches[2];
		}

		return $a_data;
	}

	static function url_encode_lite($url)
	{
		$url = str_replace(' ', '+', $url);
		return $url;
	}

	function get_ex($url, $params = array())
	{
		$raw = popval($params, 'is_raw');

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

		if(preg_match("/\.(pdf|zip|rar|djvu|mp3|avi|mkv|mov|mvi|qt)$/i", $pure_url) && empty($params['blobs_enabled']))
			return "";

		$charset = popval($params, 'charset');

		$header = array();
		if(($request_charset = $charset ? $charset : config('lcml_request_charset_default')))
			$header[] = "Accept-Charset: ".$request_charset;

		$header[] = "Accept-Language: ru, en";

		$timeout = defval($params, 'timeout', 15);
		if(preg_match('/(livejournal.com|imageshack.us|upload.wikimedia.org|www.defencetalk.com|radikal.ru)/', $url))
			$timeout *= 3;

		if(preg_match('/\.gif$/i', $url)) // Возможно — большая анимация
			$timeout *= 2;

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_ENCODING => 'gzip,deflate',
//			CURLOPT_RANGE => '0-4095',
			CURLOPT_REFERER => defval($params, 'referer', $original_url),
			CURLOPT_AUTOREFERER => true,
			CURLOPT_HTTPHEADER => $header,
//TODO: сделать перебор разных UA при ошибке
//			CURLOPT_USERAGENT => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.94 Safari/534.13',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
		));

		if(config('proxy.force_regexp') && preg_match(config('proxy.force_regexp'), $url))
			curl_setopt($ch, CURLOPT_PROXY, 'balancer.endofinternet.net:3128');

		$data = curl_exec($ch);
		if($data === false)
		{
			//TODO: оформить хорошо. Например, отправить отложенную задачу по пересчёту
			//И выше есть такой же блок.
			echo '[214] Curl error: ' . curl_error($ch);
			return '';
		}


		if(!$raw)
			$data = trim($data);

		$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
//		echo "<xmp>"; print_r($data); echo "</xmp>";

		if(!$charset && !$raw && preg_match("!charset\s*=\s*(\S+)!i", $content_type, $m))
    	    $charset = $m[1];

		curl_close($ch);

		if(!$raw)
		{
			if(empty($charset))
			{
    	    	if(preg_match("!<meta\s+http\-equiv\s*=\s*\"Content\-Type\"[^>]+charset\s*=\s*(.+?)\"!i", $data, $m))
	    	    	$charset = $m[1];
				elseif(preg_match("!<meta[^>]+charset\s*=\s*(.+?)\"!i", $data, $m))
			        $charset = $m[1];
			}

	    	if(!$charset)
				$charset = config('lcml_request_charset_default');

			if($charset)
				$data = iconv($charset, config('internal_charset').'//IGNORE', $data);
		}

	    return array('content' => $data, 'content_type' => $content_type);
	}
}

if(!function_exists('curl_setopt_array'))
{
	function curl_setopt_array($curl, $options)
	{
		foreach($options as $key => $value)
			curl_setopt($curl, $key, $value);
	}
}

if(!function_exists('defval'))
{
	function defval(&$data, $name, $default=NULL, $must_be_set = false)
	{
		if($data && array_key_exists($name, $data))
			return $data[$name];

		//TODO: вынести в отдельную функцию
		if($must_be_set)
			$data[$name] = $default;

		return $default;
	}
}
