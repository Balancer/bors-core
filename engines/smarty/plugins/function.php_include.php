<?php

function smarty_function_php_include($params, &$smarty)
{
	ob_start();
	include(@$params['file']);
	$result = ob_get_contents();
	ob_clean();
	if(($cs = config('smarty_php_include_charset', 'utf-8')) != 'utf-8')
		$result = iconv($cs, config('internal_charset', 'utf-8').'//TRANSLIT', $result);

	return $result;
}
