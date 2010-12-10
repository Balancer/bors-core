<?php

function smarty_function_hidden_log($params, &$smarty)
{
	bors_hidden_log($params['type'], $params['message']);
}
