<?php

function lp_iframe($inner, &$params)
{
	$ud = parse_url($params['src']);
	if(!in_array($ud['host'], array('lj-toys.com')))
		return ec("Запрещённый <a href=\"{$params['src']}\">iframe</a>");

	$params['skip_around_cr'] = true;
	return "<iframe ".make_enabled_params($params, 'src width height frameborder')."></iframe>";
}
