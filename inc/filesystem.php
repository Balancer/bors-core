<?php

function abs_path_from_relative($uri, $page)
{
    if(preg_match("!^\w+://!", $uri))
        return $uri;

    if(preg_match("!^/!", $uri))
        return 'http://'.$_SERVER['HTTP_HOST'].$uri;

    return "$page$uri";
}

function smart_size($size)
{
	if($size<1024)
		return $size.ec("байт");

	$size = $size/1024;

	if($size<1024)
		return round($size,2).ec("кбайт");

	return round($size/1024,2).ec("Мбайт");
}

if(!function_exists("scandir"))
	require_once("include/php4/scandir.php");

function rec_rmdir($dir, $delete_self = true, $mask = '.*')
{
	if(!$dh = opendir($dir))
		return;

    while(($obj = readdir($dh))) 
	{
        if($obj=='.' || $obj=='..')
			continue;

		if(!preg_match("!^{$mask}$!", $obj))
			continue;

        if(!unlink($dir.'/'.$obj))
			rec_rmdir($dir.'/'.$obj, true, $mask);
    }

	closedir($dh);

    if ($delete_self)
        rmdir($dir);
}

function secure_path($path)
{
    $path = preg_replace('!(?<=[^:|])/{2,}!', '/', $path);
    $path = preg_replace('!^/{2,}!', '/', $path);
    $path = preg_replace('!/([^/]+?)/\.\.!', '', $path);
    $path = preg_replace('!/\.\.!', '', $path);

    return $path;
}

// From http://ru2.php.net/function.opendir, modified by Balancer.
function search_dir($dir, $mask='.*', $level=5)
{
	$return_me = array();
	if(is_dir($dir))
	{
		if($dh = opendir($dir))
		{
			while(($file = readdir($dh)) !== false)
			{
				if(is_dir($dir.'/'.$file) && $file != '.' && $file != '..')
				{
					$test_return = search_dir($dir.'/'.$file, $mask, $level+1);
					if(is_array($test_return))
					{
						$temp = array_merge($test_return, $return_me);
						$return_me = $temp;
					}
					if(is_string($test_return))
						array_push($return_me,$test_return);
				}
				elseif(preg_match("!{$mask}!", $file))
					array_push($return_me, $dir.'/'.$file);
			}
			closedir($dh);
		}
	}
	sort($return_me);
	return $return_me;
}

// Источник: http://snippets.dzone.com/posts/show/4147
// Вызов в виде: find_files('/', '\.php$', 'my_handler');
// function my_handler($filename) { echo $filename . "\n"; }
function find_files_loop($path, $pattern = '.*', $callback, $level=0)
{
	if($level > 20)
	{
		echo "Too deep find loop: $path\n";
		return;
	}

	$path = rtrim(str_replace("\\", "/", $path), '/') . '/';
	$matches = array();
	$entries = array();
	$dir = dir($path);
	while (false !== ($entry = $dir->read()))
		$entries[] = $entry;
	$dir->close();
	foreach ($entries as $entry)
	{
		$fullname = $path . $entry;
		if ($entry != '.' && $entry != '..' && is_dir($fullname))
			find_files_loop($fullname, $pattern, $callback, $level+1);
		elseif(is_file($fullname) && preg_match('!'.$pattern.'!', $entry))
			call_user_func($callback, $fullname);
	}
}
