<?php

class bors_lib_http
{
	function get($url, $raw = false)
	{
		require_once('inc/http.php');
		return http_get_content($url, $raw);
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
}
