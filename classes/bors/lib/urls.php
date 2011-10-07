<?php

class bors_lib_urls
{
	function local_dir($url)
	{

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
