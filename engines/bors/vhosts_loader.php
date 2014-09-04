<?php

function bors_vhosts()
{
	if(empty($GLOBALS['bors_data']['vhosts']))
		return array();

	return array_keys($GLOBALS['bors_data']['vhosts']);
}

function bors_vhost_data($host, $key = NULL, $def = NULL)
{
	$host = preg_replace('/^www\./', '', $host);

	$data = empty($GLOBALS['bors_data']['vhosts'][$host]) ? array() : $GLOBALS['bors_data']['vhosts'][$host];
	if($key)
		return defval($data, $key, $def);

	return $data;
}

require_once("inc/filesystem.php");

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
			require_once($file);
		//TODO: перенести в проектах handlers/bors_map в url_map, тут пока остаётся для совместимости
		elseif(file_exists($file = secure_path("{$dir}/handlers/bors_map.php")))
			require_once($file);
		$bors_map = array_merge($bors_map, $map);
	}
}

borsmaps_load();
