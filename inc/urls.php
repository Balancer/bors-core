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

function url_parse($url)
{
//	if(preg_match('!^/!', $url))
//		$url = 'http://'.$_SERVER['HTTP_HOST'].$url;

	$data = parse_url($url);

	if(empty ($data['host']))
		$data['host'] = @$_SERVER['HTTP_HOST'];

	if(preg_match("!^".preg_quote(@$_SERVER['HTTP_HOST'])."$!", $data['host']))
		$data['root'] = $_SERVER['DOCUMENT_ROOT'];

	$host = $data['host'].(empty($data['port']) ? '' : ':'.$data['port']);

	require_once('engines/bors/vhosts_loader.php');
	$vhost_data = bors_vhost_data($host);
	if(empty($vhost_data) && $host == @$_SERVER['HTTP_HOST'])
		$vhost_data = array(
			'document_root' => $_SERVER['DOCUMENT_ROOT'],
		);

	if($root = @$vhost_data['document_root'])
		$data['root'] = $root;

	//TODO: а вот это теперь, наверное, можно будет снести благодаря {if(empty($vhost_data) && $host == $_SERVER['HTTP_HOST'])} ...
//	if(empty($data['root']) && file_exists($_SERVER['DOCUMENT_ROOT'].$data['path']))
//		$data['root'] = $_SERVER['DOCUMENT_ROOT'];

	if(preg_match('!^'.preg_quote($root, '!').'(/.+)$!', $data['path'], $m))
		$data['path'] = $m[1];

	$data['local_path'] = NULL;
	if($data['local'] = !empty($data['root']))
	{
//		$relative_path = preg_replace('!^http://'.preg_quote($host, '!').'!', '', $url);
/*		if($relative_path[0] != '/')
		{
			$base_relative_path = preg_replace('!^http://'.preg_quote($host).'!', '', bors()->main_object()->url());
			if(file_exists($lp = $data['root'].$base_relative_path.$relative_path))
				$data['local_path'] = $lp;
			else
				if(file_exists($lp = $data['root'].$base_relative_path.'img/'.$relative_path))
					$data['local_path'] = $lp;
		}
		else
*/
//			$data['local_path'] = $data['root'].$relative_path;
			$data['local_path'] = $data['root'].$data['path'];
	}

	//TODO: грязный хак
	$data['local_path'] = preg_replace('!^(/var/www/files.balancer.ru/files/)[0-9a-f]{32}/(.*)$!', '$1$2', @$data['local_path']);
		
	$data['uri'] = "http://".$host.@$data['path'];
	//TODO: грязный хак
	$data['uri'] = preg_replace('!^(http://files.balancer.ru/)[0-9a-f]{32}/(.*)$!', '$1$2', $data['uri']);

	if(@$data['root'] == $data['local_path'])
		$data['local_path'] .= '/';

	if(preg_match('/^(.+?)\?.+/', $data['local_path'], $m))
		$data['local_path'] = $m[1];

	return $data;
}


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

    $data = curl_exec($ch);
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
