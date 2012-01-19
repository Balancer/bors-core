<?php

function lp_url($text, $params)
{
	extract($params);
	$url_data = url_parse($url);
	$external = @$url_data['local'] ? '' : ' class="external"';
	$blacklist = $external || preg_match('!'.config('seo_domains_whitelist_regexp', $_SERVER['HTTP_HOST']).'!', $url_data['host']);
	return "<a ".($blacklist ? 'rel="nofollow" ' : '')."href=\"$url\"$external>{$text}</a>";
}

function lt_url($params) 
{
	$url = $params['url'];
	$description = @$params['description'];

	if(preg_match('/^www\./', $url))
		$url = 'http://'.$url;

	if(preg_match('/^\w+\.\w+/', $url))
		$url = 'http://'.$url;

	if(preg_match("!^[^/]+\.\w{2,3}!",$url))
		if(!preg_match("!^\w+://!",$url))
			$params['url']="http://$url";

	$hts = NULL;
	if(class_exists('DataBaseHTS') && config('obsolete_use_handlers_system'))
		$hts = new DataBaseHTS();

	if(!preg_match("!^\w+://!",$url) && !preg_match("!^/!",$url))
		$url = @$GLOBALS['main_uri'].$url;

	$url_data = url_parse($url);
	$external = @$url_data['local'] ? '' : ' class="external"';

	if($hts &&
		!$hts->get_data($url, 'create_time')
		&& !$hts->get_data($url, 'title')
		&& $description
	)
	{
		$hts->set_data($url, 'title', $description);
		$hts->set_data($url, 'modify_time', time());
	}

	if(!$description)
		$description = str_replace('http://', '', $url);
	else
		$description = lcml($description,  array('html'=>'safe', 'only_tags' => true));

	$blacklist = $external || preg_match('!'.config('seo_domains_whitelist_regexp', $_SERVER['HTTP_HOST']).'!', $url_data['host']);

	return "<a ".($blacklist ? 'rel="nofollow" ' : '')."href=\"$url\"$external>{$description}</a>";
}
