<?php

function smarty_function_http_load($params, &$smarty)
{
	$url = defval($params, 'url');
	$charset = defval($params, 'charset');
	$x = blib_http::get_ex($url, array(
		'is_raw' => (bool) $charset,
		'timeout' => defval($params, 'timeout', 1),
	));

	$html = @$x['content'];

	if($charset)
		$html = ec($html, $charset);

	if(defval($params, 'fail_proof'))
	{
		$var = 'http_load:on-fail:'.$url;
		if($html)
			bors_set_server_var($var, $html);
		else
			$html = bors_server_var($var);
	}

	return $html;
}
