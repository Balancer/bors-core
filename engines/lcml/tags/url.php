<?php

function lt_url($params) 
{
	$url = $params['url'];

	if(preg_match("!^[^/]+\.\w{2,3}!",$url))
		if(!preg_match("!^\w+://!",$url))
			$params['url']="http://$url";

	$hts = NULL;
	if(class_exists('DataBaseHTS') && config('obsolete_use_handlers_system'))
		$hts = new DataBaseHTS();

	if(!preg_match("!^\w+://!",$url) && !preg_match("!^/!",$url))
		$url = @$GLOBALS['main_uri'].$url;

//		if($hts)
//		$parse = url_parse($url);

	$external = @$parse['local'] ? '' : ' class="external"';

	if($hts && !$hts->get_data($url, 'create_time') && !$hts->get_data($url, 'title'))
	{
		$hts->set_data($url, 'title', $params['description']);
		$hts->set_data($url, 'modify_time', time());
	}

	if(!isset($params['description']) || $url == $params['description'])
		$params['description'] = $url;
	else
	{
		$description = lcml($params['description'],  array('html'=>'safe', 'only_tags' => true));
//		if(!preg_match('!a href!', $description))
			$params['description'] = $description;
	}

	return "<a href=\"$url\"$external>{$params['description']}</a>";
}
