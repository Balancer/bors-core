<?php

function lsp_acdsee($inner_text)
{
	$url = bors()->main_object() ? bors()->main_object()->url() : NULL;

	if(!$url)
		return;

	require_once('inc/airbase/images.php');
	$out = '';

	$thumb_image_geo = config_set('lcml_tmp_agal_thumb_image_geo', '200x');

	foreach(explode("\n", $inner_text) as $s)
	{
//		s-55-mdl.jpg 34 KB 425x202x24b jpeg Модель С-55(?)|

		if(!preg_match('/^(\S+) \s+ (\d)+ \s+ (\w+) \s+ (\w+) \s+ (\w+) \s+ (.+)$/x', $s, $m))
		{
			debug_hidden_log('lcml-error', "#acdsee: incorrect string '{$s}'");
			continue;
		}
			
		list($dummy, $file, $size, $size_unit, $geo, $type, $description) = $m;

		if(preg_match('/^(.+)\|(.+?)$/', $description, $m))
		{
			$description = $m[1];
			$copy = "<br/><small>{$m[2]}</small>";
		}
		else
			$copy = "";

		$data = airbase_image_data($file, $url);
		if(!$data['local'] || !file_exists($data['local_path']))
		{
			debug_hidden_log('lcml-error', "#acdsee: file {$file} not exists in '{$s}'");
			continue;
		}

		$thumb_url = preg_replace('!^(http://[^/]+)(.*)(/[^/]+)$!', '$1/cache$2/'.$thumb_image_geo.'$3', $data['uri']);
		$image_page = preg_replace('!\.\w+$!', '.htm', $data['uri']);

		$out .= "<tr><td><a href=\"{$image_page}\"><img src=\"{$thumb_url}\" /></a><br/>{$description}{$copy}</td></tr>";
	}

	return $out;
}
