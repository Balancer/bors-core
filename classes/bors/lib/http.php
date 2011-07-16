<?php

class bors_lib_http
{
	function get($url, $raw = false)
	{
		require_once('inc/http.php');
		return http_get_content($url, $raw);
	}

	function get_cached($url, $ttl = 86400, $raw = false)
	{
		$cache = new Cache();
		if($cache->get('bors_lib_http.get_cached', $url))
			return $cache->last();

		return $cache->set(self::get($url, $raw), $ttl);
	}

	static function url_unshort($url, $type, $loop = 0)
	{
		if($loop > 2)
			return $url;

		switch($type)
		{
			case 'youtu.be':
				if(preg_match('!http://youtu.be/([^\?]+)\?a!', $url, $m))
					return 'http://www.youtube.com/watch?v='.$m[1];
		}

		$head = self::get_header($url);
		if(empty($head['Location']))
			return $url;
		return self::url_unshort($head['Location'], $type, $loop+1);
	}

	function get_header($url)
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 1,		// return header
			CURLOPT_NOBODY => 1,		// no body return. it will faster  
		));

		$headers = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

//		echo "info: "; var_dump($info);
		$parsed_headers = array();
		foreach(explode("\n", $headers) as $x)
		{
//			var_dump(trim($x));
			if(preg_match('/^(\S+):\s+(.+)$/', trim($x), $m))
				$parsed_headers[$m[1]] = $m[2];
		}

//		echo "Headers:"; var_dump($parsed_headers);
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
//			CURLOPT_REFERER => defval($curl_options, 'referer'),
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
}
