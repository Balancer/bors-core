<?php

function bors_url_parse($url, $field=NULL, $var=NULL)
{
	$data = parse_url($url);
	if($field)
		$data = $data[$field];

	if($var)
	{
		$parsed_data = array();
		parse_str($data, $parsed_data);
		$data = $parsed_data[$var];
	}

	return $data;
}

function url_parse($url)
{
//	if(preg_match('!^/!', $url))
//		$url = 'http://'.bors()->server()->host().$url;

	$data = @parse_url($url);
	if(empty($data['path']))
		$data['path'] = $url;

	if(empty($data['host']))
		$data['host'] = bors()->server()->host();

	if(bors()->server()->host() == $data['host'])
		$data['root'] = bors()->server()->root();

	$host = $data['host'].(empty($data['port']) ? '' : ':'.$data['port']);

	require_once('engines/bors/vhosts_loader.php');
	$vhost_data = bors_vhost_data($host);
	if(empty($vhost_data) && $host == bors()->server()->host())
		$vhost_data = array(
			'document_root' => bors()->server()->root(),
		);

	$root = @$data['root'];
	if(!$root && ($root = @$vhost_data['document_root']))
		$data['root'] = $root;

	//TODO: а вот это теперь, наверное, можно будет снести благодаря {if(empty($vhost_data) && $host == bors()->server()->host())} ...
//	if(empty($data['root']) && file_exists(bors()->server()->root().$data['path']))
//		$data['root'] = bors()->server()->root();

	if(preg_match('!^'.preg_quote($root, '!').'(/.+)$!', $data['path'], $m))
		$data['path'] = $m[1];

	$data['local_path'] = NULL;
	if(strlen($data['path']) > 6 && @file_exists($data['path']))
		$data['local_path'] = $data['path'];
	elseif($data['local'] = !empty($data['root']))
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
