<?php

if(!function_exists('curl_init'))
	throw new Exception('You have not installed the php-curl extension');

class blib_http_abstract
{
	static function get($url, $raw = false, $max_length = false)
	{
		require_once BORS_CORE.'/inc/http.php';
		$get = self::get_ex($url, ['is_raw' => $raw, 'max_length' => $max_length]);
		return $get['content'];
	}

	// Работает с объектами не более одного мегабайта. Можно настроить новым параметром max_length
	static function get_cached($url, $ttl = 86400, $raw = false, $force = false, $max_length = 1000000)
	{
		//FIXME: Придумать более изящный способ
		$cache_status_save = config('cache_disabled');
		config_set('cache_disabled', false);
		debug_count_inc('bors_lib_url: get_cached('.$url.')');
		$cache = new bors_cache();
		if($cache->get('bors_lib_http.get_cached-v1', $url) && !$force)
		{
			debug_count_inc('bors_lib_url: get_cached('.$url.'):found');
			$res = $cache->last();
			config_set('cache_disabled', $cache_status_save);
			return $res;
		}

		bors_function_include('debug/timing_start');
		debug_timing_start('bors_lib_url: get_cached('.$url.')');

		$x = self::get_ex($url, array('is_raw' => $raw));

		bors_function_include('debug/timing_stop');
		bors_debug::timing_stop('bors_lib_url: get_cached('.$url.')');

		$content = $x['content'];

		// Запоминаем не более одного мегабайта, а то по max_allowed_packet можно влететь.
		if(strlen($content) > $max_length)
			$content = substr($content, 0, $max_length);

		debug_count_inc('bors_lib_url: get_cached('.$url.'):store');
		$cache->set($content, $ttl);
		config_set('cache_disabled', $cache_status_save);
		return $content;
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

	static function get_header($url)
	{
		$curl_options = array(); // Затычка

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 1,		// return header
			CURLOPT_NOBODY => 1,		// no body return. it will faster
			CURLOPT_TIMEOUT => defval($curl_options, 'timeout', 5),
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 21,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
			CURLOPT_REFERER => defval($curl_options, 'referer', $url),
			CURLOPT_AUTOREFERER => true,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_COOKIEJAR => blib_files::tmp('cookie-jar-1'),
			CURLOPT_COOKIEFILE => blib_files::tmp('cookie-file-1'),
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
		$original_url = $url;

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_TIMEOUT => defval($curl_options, 'timeout', 15),
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 22,
			CURLOPT_ENCODING => 'gzip, deflate',
			CURLOPT_REFERER => defval($curl_options, 'referer', $original_url),
			CURLOPT_AUTOREFERER => true,
//			CURLOPT_HTTPHEADER => $header,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_COOKIEJAR => blib_files::tmp('cookie-jar-2'),
			CURLOPT_COOKIEFILE => blib_files::tmp('cookie-file-2'),
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
	static function parse_raw_http_request($data, $content_type)
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

	static function get_ex($url, $params = [])
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

		$re = config('urls.skip_load_ext_regexp');
		if($re && preg_match($re, $pure_url) && empty($params['blobs_enabled']))
			return array('content' => NULL, 'content_type' => NULL, 'error' => true);

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

		$max_download = config('curl.max_download');
		if(!$max_download)
		{
			$max_download = trim(ini_get('memory_limit'));
			$last = strtolower($max_download[strlen($max_download)-1]);
			switch($last)
			{
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
					$max_download *= 1024;
				case 'm':
					$max_download *= 1024;
				case 'k':
					$max_download *= 1024;
			}

			$max_download = $max_download / 4;

			if($max_download <= 0)
				$max_download = 1024*1024;
		}

		global $data;
		$data = '';

		$curl_options = array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => defval($params, 'MAXREDIRS', 23),
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
			CURLOPT_COOKIESESSION => true,
			CURLOPT_COOKIEJAR => blib_files::tmp('cookie-jar-3'),
			CURLOPT_COOKIEFILE => blib_files::tmp('cookie-file-3'),

			CURLOPT_PROGRESSFUNCTION => function($resource, $download_size = 0, $downloaded = 0, $upload_size = 0, $uploaded = 0) use ($url, $max_download) {
				//	CURLOPT_WRITEFUNCTION is good for this but CURLOPT_PROGRESSFUNCTION is the best.
				// If $downloaded exceeds size, returning non-0 breaks the connection!
				if($downloaded > $max_download)
				{
					bors_debug::syslog('warning-curl', "Break curl '$url' progress download with size=".$downloaded);
					return 1;
				}

				return 0;
			},
			CURLOPT_WRITEFUNCTION => function($handle, $chunk) use ($url, $max_download) {
				global $data;
				$data .= $chunk;

				if(strlen($data) > $max_download)
				{
					bors_debug::syslog('warning-curl', "Break curl '$url' write download with size=".strlen($data)." and max_download=".$max_download);
					return 0;
				}
				else
					return strlen($chunk);
			},
		);

		if($opt = defval($params, 'FRESH_CONNECT'))
			$curl_options[CURLOPT_FRESH_CONNECT] = true;

		if($save_file = defval($params, 'file'))
		{
			$fh = fopen($save_file, 'wb');
			$curl_options[CURLOPT_FILE] = $fh;
//			$curl_options[CURLOPT_HEADER] = true;	// Добавить заголовок ответа в файл
//			$curl_options[CURLOPT_RETURNTRANSFER] = false;
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, $curl_options);

		if(config('proxy.force_regexp') && preg_match(config('proxy.force_regexp'), $url))
			curl_setopt($ch, CURLOPT_PROXY, config('proxy.forced'));

		$start_time = time();

		$result = curl_exec($ch);
		if($result !== true)
			$data = $result;

		if(strlen($data) <  2000 && preg_match('/document.cookie=.*(__DDOS_COOKIE|_ddn_intercept_2_)=(\w+);/', $data, $m) && preg_match('/window.location.reload\(true\)/', $data))
		{
			curl_setopt($ch, CURLOPT_COOKIE, "{$m[1]}={$m[2]}");
			$data = curl_exec($ch);
		}

		$info = curl_getinfo($ch);

		$time = time() - $start_time;
		if($time > 5 || @$info['size_download'] > 1000000)
			bors_debug::syslog('warning-curl', "Too long or too big download for $original_url; time=$time; info=".print_r($info, true));

//		if(preg_match('/balancer\.ru|airbase\.ru/', $original_url))
//			bors_debug::syslog('curl-warnings', "Try to load $original_url");

/*
array (size=22)
  'url' => string 'http://www.palal.net/blogposts/20130601-favelas/dona%20marta/IMG_9624.JPG' (length=73)
  'content_type' => string 'image/jpeg' (length=10)
  'http_code' => int 200
  'header_size' => int 219
  'request_size' => int 376
  'filetime' => int -1
  'ssl_verify_result' => int 0
  'redirect_count' => int 0
  'total_time' => float 2.306583
  'namelookup_time' => float 0.010812
  'connect_time' => float 0.21499
  'pretransfer_time' => float 0.215042
  'size_upload' => float 0
  'size_download' => float 764927
  'speed_download' => float 331627
  'speed_upload' => float 0
  'download_content_length' => float 764927
  'upload_content_length' => float 0
  'starttransfer_time' => float 0.421616
  'redirect_time' => float 0
  'certinfo' => 
	array (size=0)
	  empty
  'redirect_url' => string '' (length=0)
*/

		if(!empty($curl_options[CURLOPT_FILE]))
			fclose($curl_options[CURLOPT_FILE]);

		if($data === false)
		{
			//TODO: оформить хорошо. Например, отправить отложенную задачу по пересчёту
			//И выше есть такой же блок.
			$err_str = curl_error($ch);
			curl_close($ch);

			if($save_file)
				@unlink($save_file);

			bors_debug::syslog('warning-curl', "Curl ($url) error: ".$err_str);
			return array('content' => NULL, 'content_type' => NULL, 'error' => $err_str);
		}

		if(!$raw)
			$data = trim($data);

		$content_type = $info['content_type'];

		if(!$raw)
		{
			if(empty($charset))
			{
				// <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
				if(preg_match("!<meta\s+http\-equiv\s*=\s*\"Content\-Type\"[^>]+charset\s*=\s*(.+?)\"!i", $data, $m))
					$charset = $m[1];
				// <meta charset="utf-8" />
				// http://portnews.ru/news/184445/
				elseif(preg_match("!<meta[^>]+charset\s*=\s*\"(.+?)\"!i", $data, $m))
					$charset = $m[1];
				elseif(preg_match("!<meta[^>]+charset\s*=\s*(.+?)\"!i", $data, $m))
					$charset = $m[1];
				// <meta http-equiv="Content-Type" content="text/html;UTF-8">
				elseif(preg_match("!<meta [^>]+Content-Type[^>]+content=\"text/html;([^>]+)\">!i", $data, $m))
					$charset = $m[1];
				elseif(preg_match("!<meta charset=\"(.+?)\" />\">!i", $data, $m))
					$charset = $m[1];
			}

			//  [content_type] => text/html; charset=UTF-8
			if(preg_match("!charset\s*=\s*(\S+)!i", $content_type, $m))
				$header_charset = $m[1];
			else
				$header_charset = $charset;

			if(!$charset && $header_charset)
				$charset = $header_charset;

			if($charset != $header_charset)
			{
				// Если кодировка в ответе сервера не совпадает с кодировкой в мета-теге, то
				// проверяем, нормально ли конвертируется с кодировкой сервера. Если всё ок,
				// то так и оставляем.
				$cvtd = iconv($header_charset, config('internal_charset').'//IGNORE', $data);
				if($data == iconv(config('internal_charset'), $header_charset.'//IGNORE', $cvtd))
				{
					$charset = config('internal_charset');
					$data = $cvtd;
				}
			}

			if(!$charset)
				$charset = config('lcml_request_charset_default');

			// Фикс кривой настройки, типа http://www.garant.ru/products/ipo/prime/doc/70625926/
			if($charset == 'cp-1251')
				$charset = 'windows-1251';

			// Совать в лоб iconv нельзя. Оказывается, //IGNORE или //TRANSLIT нынче, порой, не работают.
			if(strtolower($charset) == 'utf-8')
				$data = blib_str_charset::utf8_fix($data);

			// модификатор TRANSLIT не использовать, в ряде случаев ломается. Так что — только IGNORE
			if($charset && strtolower(config('internal_charset')) != strtolower($charset))
				$data = iconv($charset, config('internal_charset').'//IGNORE', $data);
		}

		curl_close($ch);

		return array('content' => $data, 'content_type' => $content_type, 'error' => false);
	}

	static function exists($url)
	{
		$timeout = 15; //timeout seconds

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// don't download content
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);

		return (curl_exec($ch)!==FALSE);
	}

	static function __dev()
	{
		config_set('is_debug', true);
//		config_set('proxy.force_regexp', '/novorossia\.su/');
//		config_set('proxy.forced', '192.168.1.3:8118');

		$url = "https://www.facebook.com/nastya.stanko?fref=nf";
		print_r(self::get_ex($url, array('timeout' => 3)));
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
