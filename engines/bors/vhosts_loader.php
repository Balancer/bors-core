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

function register_vhost($host, $documents_root=NULL, $bors_host=NULL)
{
	global $bors_data;

	if(empty($documents_root))
		$documents_root = '/var/www/'.$host.'/htdocs';

	if(empty($bors_host))
	{
		$bors_host = dirname($documents_root).'/bors-host';
		$bors_site = dirname($documents_root).'/bors-site';
	}
	else
		$bors_site = $bors_host;

	$map = array();

	if(file_exists($file = BORS_HOST.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);
	elseif(file_exists($file = BORS_LOCAL.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);
	elseif(file_exists($file = BORS_CORE.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);

	$map2 = $map;

	if(file_exists($file = $bors_site.'/handlers/bors_map.php'))
		include($file);

	if(file_exists($file = $bors_host.'/handlers/bors_map.php'))
		include($file);

//	echo "$host: <xmp>"; print_r($map); echo "</xmp>";

	$bors_data['vhosts'][$host] = array(
		'bors_map' => array_merge($map2, $map),
		'bors_local' => $bors_host,
		'bors_site' => $bors_site,
		'document_root' => $documents_root,
	);
}

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
