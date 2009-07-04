<?php

global $bors_data;
$bors_data['vhost_handlers'] = array();

function bors_vhosts()
{
	if(empty($GLOBALS['bors_data']['vhosts']))
		return array();
			
	return array_keys($GLOBALS['bors_data']['vhosts']);
}

function bors_vhost_data($host)
{
	return @$GLOBALS['bors_data']['vhosts'][$host];
}

function register_vhost($host, $documents_root=NULL, $bors_local=NULL)
{
	global $bors_data;
		
	if(empty($documents_root))
		$documents_root = '/var/www/'.$host.'/htdocs';
		
	if(empty($bors_local))
		$bors_local = dirname($documents_root).'/bors-host';
			
	$map = array();

	if(file_exists($file = BORS_HOST.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);
	elseif(file_exists($file = BORS_LOCAL.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);
	elseif(file_exists($file = BORS_CORE.'/vhosts/'.$host.'/handlers/bors_map.php'))
		include($file);

	$map2 = $map;

	if(file_exists($file = $bors_local.'/handlers/bors_map.php'))
		include($file);
	
//	echo "$host: <xmp>"; print_r($map); echo "</xmp>";
			
	$bors_data['vhosts'][$host] = array(
		'bors_map' => array_merge($map2, $map),
		'bors_local' => $bors_local,
		'document_root' => $documents_root,
	);
}

@include_once("config/vhosts.php");
require_once("inc/filesystem.php");

function borsmaps_load()
{
	global $bors_map;
	if(empty($bors_map))
		$bors_map = array();

	foreach(bors_dirs(true) as $dir)
	{
		$map = array();
		if(file_exists($file = secure_path("{$dir}/handlers/bors_map.php")))
			include($file);
//		echo $file.'->'.file_exists($file)."<br/>\n";
		$bors_map = array_merge($bors_map, $map);
	}
}

borsmaps_load();
