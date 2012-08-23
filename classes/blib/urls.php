<?php

class blib_urls
{
	function local_file($url, $base)
	{
		$data = parse_url($url);
		$host = preg_replace('/^www\./', '', $data['host']);
		$dpath = array();
		$first = true;
		foreach(array_reverse(explode('.', $host)) as $d)
		{
			if($first)
				$first = false;
			else
				$d = substr($d, 0, 2).'/'.$d;

			$dpath[] = $d;
		}

		$path = translite_path($data['path']);

		if(!empty($data['query']))
		{
			if(!preg_match('!/$!',$path))
				$path .= '/';

			$path .= '='.str_replace('&','/', $data['query']);
		}

		if(preg_match('!/$!',$path))
			$path .= 'index';

		return $base.'/'.join('/', $dpath).$path;
	}

	static function parse_query_string($query)
	{
		if(!is_array($query))
		{
			$result = array();
			parse_str($query, $result);
			return $reult;
		}

		// Анализ готовых пар, используется для всяких explode('/', '.../mod/class=value/...');
		$result = array();
		foreach($query as $pair)
			if(preg_match('!^(.+?)=(.+)$!', $pair, $m) && $m[2])
				$result[] = $pair;

		return $result;
	}

	static function check_nofollow($url)
	{
		$url_data = url_parse($url);
		$external = empty($url_data['local']);
		$blacklist = $external && !preg_match('!'.config('seo_domains_whitelist_regexp', @$_SERVER['HTTP_HOST']).'!', $url_data['host']);
		return $blacklist ? ' rel="nofollow"' : '';
	}

	static function check_external($url)
	{
		$url_data = url_parse($url);
		return empty($url_data['local']) ? ' class="external"' : '';
	}

	static function replace_query($url, $param_name, $value=NULL)
	{
		$params = array();
		$url_info = parse_url($url);

		if($query = @$url_info['query'])
			parse_str($query, $params);

		if($value)
			$params[$param_name] = $value;
		else
			unset($params[$param_name]);

		$url_info['query'] = http_build_query($params);

		return self::build_url($url_info);
	}

	static function build_url($url_info)
	{
		$scheme	= defval($url_info, 'scheme', 'http');
		$host 	= defval($url_info, 'host');
		$path 	= defval($url_info, 'path');
		$query 	= defval($url_info, 'query');

		$url = "$scheme://$host$path";
		if($query)
			$url .= '?'.$query;

		return $url;
	}

	static function host($url)
	{
		$data = parse_url($url);
		return $data['host'];
	}

	static function path($url)
	{
		$data = parse_url($url);
		return $data['path'];
	}

	// Сравнение двух URL. Если один из URL не содержит хоста,
	// то сравниваются только пути
	//TODO: сделать корректным сравнение с GET-запросами с разным порядком параметров
	static function eq($url1, $url2)
	{
		if(preg_match('!^\w+://!', $url1) && preg_match('!^\w+://!', $url2))
			return $url1 == $url2;

		return self::path($url1) == self::path($url2);
	}

	static function in_array($test_url, $urls_array)
	{
		foreach($urls_array as $url)
			if(self::eq($test_url, $url))
				return true;

		return false;
	}

	static function __unit_test($suite)
	{
		$url1 = "http://balancer.ru/blog/";
		$suite->assertTrue(self::eq($url1, '/blog/'));
		$suite->assertTrue(self::eq($url1, 'http://balancer.ru/blog/'));
		$suite->assertFalse(self::eq($url1, '/blogs/'));
		$suite->assertFalse(self::eq($url1, 'http://foo.bar/blog/'));

		$suite->assertTrue(self::in_array($url1, array('/blog/', '/blogs/')));
		$suite->assertFalse(self::in_array($url1, array('/blogs1/', '/blogs2/')));
	}
}
