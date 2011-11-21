<?php

class bors_core_vhost
{
	function register($host, $documents_root=NULL, $bors_host=NULL)
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

		if(file_exists($file = $bors_site.'/bors_map.php'))
			include($file);

		if(file_exists($file = $bors_site.'/url_map.php'))
			include($file);

		if(file_exists($file = $bors_host.'/handlers/bors_map.php'))
			include($file);

//		echo "$host: <xmp>"; print_r($map); echo "</xmp>";

		$bors_data['vhosts'][$host] = array(
			'bors_map' => array_merge($map2, $map),
			'bors_local' => $bors_host,
			'bors_site' => $bors_site,
			'document_root' => $documents_root,
		);
	}
}
