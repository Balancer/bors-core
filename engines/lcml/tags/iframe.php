<?php

function lp_iframe($inner, &$params)
{
	$ud = parse_url($params['src']);
	if(!in_array($ud['host'], array('lentaru.media.eagleplatform.com')))
		return ec("Запрещённый <a href=\"{$params['src']}\">iframe</a>");

	$sandbox = " sandbox";
	if(in_array($ud['host'], array('lentaru.media.eagleplatform.com')))
		$sandbox = "";

	$params['skip_around_cr'] = true;
	$html = "<iframe ".make_enabled_params($params, 'src width height frameborder style')." $sandbox></iframe>";

	if(config('is_developer'))
		r($html);

	return $html;
}
