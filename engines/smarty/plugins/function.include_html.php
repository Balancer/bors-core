<?php

function smarty_function_include_html($params, &$smarty)
{
	$html = NULL;
	$mtime = NULL;
	$loader = new bors_templates_smarty_resources_file($smarty);
	$loader->fetch($params['file'], $html, $mtime);

	return $html;
}
