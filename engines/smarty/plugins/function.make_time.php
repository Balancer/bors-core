<?php

function smarty_function_make_time($params, &$smarty)
{
	if(!empty($GLOBALS['stat']['start_microtime']))
	{
	    list($usec, $sec) = explode(" ",microtime());
		return sprintf("%.3f", ((float)$usec + (float)$sec) - $GLOBALS['stat']['start_microtime']);
	}
	else
		return "";
}