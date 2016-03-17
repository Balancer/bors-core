<?php

function smarty_function_hidden_log($params, &$smarty)
{
	bors_debug::syslog($params['type'], $params['message']);
}
