<?php

require_once("inc/translit.php");

function url_truncate($url, $max_length)
{
	if(strlen($url) <= $max_length)
		return $url;


	$limit = $max_length - 3; // Учитываем /.../ в середине.
	$chunks = explode('/', $url);
	$count = count($chunks);
	$added = array();
	$left = array();
	$left_length = 0;
	$right = array();
	$right_length = 0;
	$right_pos = $count;

	$left_skipped = false;
	$right_skipped = false;

	for($i=0; $i<$right_pos; $i++)
	{
		if(empty($added[$i]))
		{
			$x = $chunks[$i];
			$sx = strlen($x);

			if($left_length + $sx + 1 + $right_length > $limit)
				$left_skipped = true;

			if(!$left_skipped)
			{
				$left[] = $x;
				$left_length += 1+$sx;
				$added[$i] = true;
			}
		}

		if($i<2)
			continue;

		$j = --$right_pos;
		if(empty($added[$j]))
		{
			$x = $chunks[$j];
			$sx = strlen($x);

			if($right_length + $sx + 1 + $left_length > $limit)
				$right_skipped = true;

			if(!$right_skipped)
			{
				array_unshift($right,  $x);
				$right_length += 1+$sx;
				$added[$j] = true;
			}
		}

	}

	return join('/', $left).($right ? '/.../'.join('/',$right) : '/...');
}

bors_function_include('url/parse');

    function translite_uri($uri)
    {
        $uri = strtolower($uri);

        $uri = to_translit($uri);

        $uri = strtr($uri, array(
        ' ' => '_', 
        '!' => '_exclm_', 
        '"' => '_dquot_', 
        '#' => '_sharp_', 
        '$' => '_doll_', 
        '%' => '_prcnt_', 
        '&' => '_amp_', 
        '\''=> '_quote_', 
        '('=> '_lbrck_', 
        ')'=> '_rbrck_', 
        '*'=> '_mult_', 
        '+' => '_plus_',
        ',' => '_comma_',
        '.' => '_dot_',
        '/' => '_slash_',
        ':' => '_colon_',
        ';' => '_smcln_',
        '<' => '_lt_',
        '=' => '_eq_',
        '>' => '_gt_',
        '?' => '_quest_', 
        '@' => '_at_', 
        '['=> '_lsbrc_', 
        "\\" => '_bkslsh_',
        ']'=> '_rsbrc_', 
        '^'=> '_power_', 
        '`'=> '_gracc_', 
        '{'=> '_lcbrc_', 
        '|'=> '_vertl_', 
        '}'=> '_rcbrc_', 
        '~'=> '_tild_', 
        ));

        $uri = rawurlencode($uri);

        $uri = str_replace('%','_',$uri);
        return $uri;        
    }

    function translite_path($path)
    {
        $path = to_translit($path);

        $path = strtr($path, array(
        ' ' => '_', 
        '!' => '_exclm_', 
        '"' => '_dquot_', 
        '#' => '_sharp_', 
        '%' => '_prcnt_', 
        '&' => '_amp_', 
        '\''=> '_quote_', 
        '*'=> '_mult_', 
        ':' => '_colon_',
        '<' => '_lt_',
        '>' => '_gt_',
        '?' => '_quest_', 
        '['=> '_lsbrc_', 
        "\\" => '_bkslsh_',
        ']'=> '_rsbrc_', 
        '^'=> '_power_', 
        '`'=> '_gracc_', 
        '|'=> '_vertl_', 
        ));

        return $path;        
    }

    function translite_uri_simple($uri)
    {
        $uri = to_translit($uri);
        $uri = strtolower($uri);
		$uri = str_replace("'", '', $uri);
		$uri = preg_replace('/\W/', '-', $uri);
		$uri = preg_replace('/\-+/', '-', $uri);
		$uri = trim($uri, '-');
		
		return $uri;

        $uri = strtr($uri, array(
        '"' => '~', 
        '`' => '~',
        ';' => '.',
        "'" => '~',
        '#' => '-N',
        '&' => '-and-',
        '+' => '-plus-',
        '/' => '-',
        '<' => '-less-',
        '=' => '-eq-',
        '>' => '-great-',
        '?' => '-', 
        "\\" => '-',
        '|'=> '-', 
        ));

//        $uri = rawurlencode($uri);
//        $uri = str_replace('%','_',$uri);
		$uri = preg_replace('![^\w\-~\.\,]!', '-', $uri);
        $uri = preg_replace("!\-{2,}!",'-', $uri);
        $uri = preg_replace("!^\-+!",'', $uri);
        $uri = preg_replace("!\-+$!",'', $uri);
        $uri = preg_replace("!\.$!",'', $uri);
        $uri = preg_replace("!(,|\.)-!",'$1', $uri);
        return $uri;        
    }

    function translite_path_simple($path)
    {
        $path = to_translit($path);
        $path = strtolower($path);
		$path = str_replace("'", '', $path);
		$path = str_replace('_', '-', $path);
		$path = preg_replace('/[^\w\/\.]/', '-', $path);
		$path = preg_replace('/\-+/', '-', $path);
		$path = trim($path, '-');

		return $path;
	}

// Источник: http://ru.php.net/manual/ru/function.curl-setopt.php#79787
function curl_redir_exec($ch,$debug="")
{
    static $curl_loops = 0;
    static $curl_max_loops = 20;

    if ($curl_loops++ >= $curl_max_loops)
    {
        $curl_loops = 0;
        return FALSE;
    }

    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	debug_timing_start('http-get[inc/urls.php]: '.$url);
	debug_timing_start('http-get-total');
    $data = curl_exec($ch);
	debug_timing_stop('http-get-total');
	debug_timing_stop('http-get[inc/urls.php]: '.$url);

    $debbbb = $data;

    list($header, $data) = explode("\n\n", $data, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code == 301 || $http_code == 302)
    {
        $matches = array();
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = @parse_url(trim(array_pop($matches)));
        //print_r($url);
        if (!$url)
        {
            //couldn't process the url to redirect to
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
    /*    if (!$url['scheme'])
            $url['scheme'] = $last_url['scheme'];
        if (!$url['host'])
            $url['host'] = $last_url['host'];
        if (!$url['path'])
            $url['path'] = $last_url['path'];*/
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
        curl_setopt($ch, CURLOPT_URL, $new_url);
    //    debug('Redirecting to', $new_url);

        return curl_redir_exec($ch);
    }
    else
    {
        $curl_loops=0;
        return $debbbb;
    }
}

function normalize_url($url)
{
	$url = preg_replace('!http://(www|win)\.!', 'http://', $url);
	$url = preg_replace('!\?PHPSESSID=[0-9a-f]+&!', '?', $url);
	$url = preg_replace('!#\w+$!', '', $url);
	return $url;
}

function url_append_param($url, $param, $value)
{
	$param = urlencode($param);
	if(strpos($url, $param.'=') !== false)
		return $url;

	if(strpos($url, '?') === false)
		$url .= '?';
	else
		$url .= '&';

	return $url."$param=".urlencode($value);
}

function url_remove_params($url)
{
	@list($url, $params) = @explode('?', $url);
	return $url;
}

function url_clean_params($url)
{
	@list($url, $params) = @explode('?', $url);
	if(!$params)
		return $url;

	$result = array();
	foreach(explode('&', $params) as $pair)
		if(preg_match('!^(.+?)=(.+)$!', $pair, $m) && $m[2])
			$result[] = $pair;

	return $url.'?'.join('&', $result);
}

function url_drop_params($url)
{
	@list($url, $params) = @explode('?', $url);
	return $url;
}
