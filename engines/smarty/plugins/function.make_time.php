<?php

function smarty_function_make_time($params, &$smarty)
{
	if(!empty($GLOBALS['stat']['start_microtime']))
		return sprintf("%.3f", microtime(true) - $GLOBALS['stat']['start_microtime']);
	else
		return "";
}
