<?php

class bors_lib_urls
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
}
