<?php

function smarty_function_http_load($params, &$smarty)
{
	$url = defval($params, 'url');
	$html = bors_lib_http::get($url);
	if($charset = defval($params, 'charset'))
		$html = ec($html, $charset);

	return $html;
}
