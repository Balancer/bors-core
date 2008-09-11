<?php

function smarty_function_explode($params, &$smarty)
{
	extract($params);
	$vars = explode(',', $vars);
	
	eval("list(".('$'.join(',$', $vars)).") = explode(\"".(empty($delim) ? '|' : $delim)."\", \"".addslashes($value)."\");");
	foreach($vars as $v)
		$smarty->assign($v, $$v);
}
