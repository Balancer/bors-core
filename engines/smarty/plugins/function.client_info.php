<?php

function smarty_function_client_info($params, &$smarty)
{
	include_once('inc/clients.php');
	return bors_client_info_short($params['ip'], @$params['ua']);
}
