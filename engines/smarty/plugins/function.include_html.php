<?php

function smarty_function_include_html($params, &$smarty)
{
	$html = NULL;
	smarty_resource_file_get_template($params['file'], $html, $smarty);

	return $html;
}
