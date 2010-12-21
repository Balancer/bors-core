<?php

function smarty_function_http_load($params, &$smarty)
{
	$url = defval($params, 'url');
	$charset = defval($params, 'charset');
	$html = bors_lib_http::get($url, $charset);
	if($charset)
		$html = ec($html, $charset);

	return $html;
}
