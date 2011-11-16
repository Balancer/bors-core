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
			$query = explode('&', $query);

		$result = array();
		foreach($query as $pair)
			if(preg_match('!^(.+?)=(.+)$!', $pair, $m) && $m[2])
				$result[] = $pair;

		return $result;
	}
}
