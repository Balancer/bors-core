<?php

function smarty_function_call_static($params, &$smarty)
{
	extract($params);
	echo call_user_func(array($class_name, $method), $arg);
}
