<?php

function smarty_function_hidden_log($params, &$smarty)
{
	debug_hidden_log($params['type'], $params['message']);
}
