<?php

function smarty_function_php_include($params, &$smarty)
{
	ob_start();
//	$cwd = getcwd();
//	echo "Load {$params['file']}<br/>";
//	@chdir(dirname(@$params['file']));
//	echo ini_get('include_path');

/*	$tpl = $smarty->get_template_vars('page_template');
	if(preg_match('!^xfile:(.+)/[^/]+$!', $tpl, $m))
		print_d($m);
*/
	include(@$params['file']);
//	@chdir(dirname($cwd));
	$result = ob_get_contents();
	ob_clean();
	if(($cs = config('smarty_php_include_charset', 'utf-8')) != 'utf-8')
		$result = iconv($cs, config('internal_charset', 'utf-8').'//TRANSLIT', $result);

	return $result;
}
