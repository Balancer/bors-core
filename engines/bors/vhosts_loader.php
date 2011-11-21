<?php

global $bors_data;
$bors_data['vhost_handlers'] = array();

function bors_vhosts()
{
	if(empty($GLOBALS['bors_data']['vhosts']))
		return array();

	return array_keys($GLOBALS['bors_data']['vhosts']);
}

function bors_vhost_data($host, $key = NULL, $def = NULL)
{
	$data = @$GLOBALS['bors_data']['vhosts'][$host];
	if($key)
		return defval($data, $key, $def);

	return $data;
}

bors_function_include('bors/register_vhost');

require_once("inc/filesystem.php");

function bors_url_map($map_array)
{
	global $bors_map;
	$bors_map = array_merge($bors_map, $map_array);
}

function bors_url_submap($map)
{
	$GLOBALS['bors_url_submap_map'] = $map;
}

function borsmaps_load()
{
	global $bors_map;
	if(empty($bors_map))
		$bors_map = array();

	foreach(bors_dirs(true) as $dir)
	{
		$map = array();
		if(file_exists($file = secure_path("{$dir}/url_map.php")))
			include($file);
		//TODO: перенести в проектах handlers/bors_map в url_map, тут пока остаётся для совместимости
		elseif(file_exists($file = secure_path("{$dir}/handlers/bors_map.php")))
			include($file);
		$bors_map = array_merge($bors_map, $map);
	}
}

borsmaps_load();
