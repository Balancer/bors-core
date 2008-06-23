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
	$data = parse_url($url);

	if(empty ($data['host']))
		$data['host'] = $_SERVER['HTTP_HOST'];

	if(preg_match("!^{$_SERVER['HTTP_HOST']}$!", $data['host']))
		$data['root'] = $_SERVER['DOCUMENT_ROOT'];

	require_once('engines/bors/vhosts_loader.php');
	$vhost_data = bors_vhosts($data['host']);
	if($root = @$vhost_data['document_root'])
		$data['root'] = $root;

	if($data['local'] = !empty ($data['root']))
		$data['local_path'] = $data['root'].str_replace('http://'.$data['host'], '', $url);
		
	$data['uri'] = "http://".@ $data['host'].@ $data['path'];
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
//        $uri = strtolower($uri);

        $uri = to_translit($uri);

        $uri = strtr($uri, array(
        '"' => "~", 
        '`' => "~",
        "'" => "~",
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
		$uri = preg_replace('![^\w\-~\.\,\;]!', '-', $uri);
        $uri = preg_replace("!\-{2,}!",'-', $uri);
        $uri = preg_replace("!^\-+!",'', $uri);
        $uri = preg_replace("!\-+$!",'', $uri);
        $uri = preg_replace("!\.$!",'', $uri);
        $uri = preg_replace("!(,|\.)-!",'$1', $uri);
        return $uri;        
    }
