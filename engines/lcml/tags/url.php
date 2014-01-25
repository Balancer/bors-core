<?php

function lp_url($text, $params)
{
	extract($params);
	$url_data = url_parse($url);
	$external = !empty($url_data['local']) || empty($url_data['host']) ? '' : ' class="external"';

   	if(!empty($url_data['host']) && ($skip_domains = config('lcml.urls.skip_domains')))
   	{
		$host = str_replace('www.', '', $url_data['host']);
		if(in_array($host, $skip_domains))
			return "$text ($url)";
	}

	$blacklist = $external || preg_match('!'.config('seo_domains_whitelist_regexp', @$_SERVER['HTTP_HOST']).'!', $url_data['host']);
	// specialchars для http://balancer.ru/g/p2728134
	return "<a ".($blacklist ? 'rel="nofollow" ' : '')."href=\"".htmlspecialchars($url)."\"$external>".lcml($text, array('html'=>'safe', 'only_tags' => true))."</a>";
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
//	if(class_exists('DataBaseHTS') && config('obsolete_use_handlers_system'))
//		$hts = new DataBaseHTS();

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

	$blacklist = $external || preg_match('!'.config('seo_domains_whitelist_regexp', @$_SERVER['HTTP_HOST']).'!', $url_data['host']);

	return "<a ".($blacklist ? 'rel="nofollow" ' : '')."href=\"$url\"$external>{$description}</a>";
}
