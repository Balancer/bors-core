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

	static function decode($url)
	{
		while(preg_match('/%[\da-fA-F]{2,}/', $url))
			$url = urldecode($url);

		return blib_str_charset::decode($url);
	}

	static function parts_encode($url)
	{
		// via http://stackoverflow.com/questions/7973790/urlencode-only-the-directory-and-file-names-of-a-url
		$parts = parse_url($url);

		if(!empty($parts['path']))
			$parts['path'] = join('/', array_map('urlencode', explode('/', $parts['path'])));

		$url = http_build_url('', $parts);
		return $url;
	}

	static function norm($url)
	{
		$url = self::decode($url);
		$ud = parse_url($url);
		$ud['host'] = preg_replace('/^www\./', '', $ud['host']);
		unset($ud['port']);
		$ud['scheme'] = 'http';

		$url = http_build_url('', $ud);
		return $url;
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

	function __dev()
	{
		var_dump(
			self::decode('http://ru.wikipedia.org/wiki/%C8%ED%E4%E5%E9%F1%EA%E8%E5_%E2%EE%E9%ED%FB'),
			self::decode('http://ru.wikipedia.org/wiki/%D0%98%D0%BD%D0%B4%D0%B5%D0%B9%D1%81%D0%BA%D0%B8%D0%B5_%D0%B2%D0%BE%D0%B9%D0%BD%D1%8B'),
			self::decode('http://ru.wikipedia.org/wiki/Индейские_войны')
		);
	}
}

// via http://stackoverflow.com/questions/7751679/php-http-build-url-and-pecl-install
if(!function_exists('http_build_url'))
{
    define('HTTP_URL_REPLACE', 1);              // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);           // Join query strings
    define('HTTP_URL_STRIP_USER', 8);           // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);          // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);          // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);          // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);         // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);     // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);         // Strip anything but scheme and host

    // Build an URL
    // The parts of the second URL will be merged into the first according to the flags argument. 
    // 
    // @param   mixed           (Part(s) of) an URL in form of a string or associative array like parse_url() returns
    // @param   mixed           Same as the first argument
    // @param   int             A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
    // @param   array           If set, it will be filled with the parts of the composed url like parse_url() would return 
    function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
    {
        $keys = array('user','pass','port','path','query','fragment');

        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        else if ($flags & HTTP_URL_STRIP_AUTH)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }

        // Parse the original URL
        $parse_url = parse_url($url);

        // Scheme and Host are always replaced
        if (isset($parts['scheme']))
            $parse_url['scheme'] = $parts['scheme'];
        if (isset($parts['host']))
            $parse_url['host'] = $parts['host'];

        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE)
        {
            foreach ($keys as $key)
            {
                if (isset($parts[$key]))
                    $parse_url[$key] = $parts[$key];
            }
        }
        else
        {
            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
            {
                if (isset($parse_url['path']))
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                else
                    $parse_url['path'] = $parts['path'];
            }

            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
            {
                if (isset($parse_url['query']))
                    $parse_url['query'] .= '&' . $parts['query'];
                else
                    $parse_url['query'] = $parts['query'];
            }
        }

        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key)
        {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
                unset($parse_url[$key]);
        }


        $new_url = $parse_url;

        return 
             ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
            .((isset($parse_url['host'])) ? $parse_url['host'] : '')
            .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            .((isset($parse_url['path'])) ? $parse_url['path'] : '')
            .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
        ;
    }
}
