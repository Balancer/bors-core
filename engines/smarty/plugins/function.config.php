<?php

function smarty_function_config($params, &$smarty)
{
	return config($params['name'], @$params['default']);
}
