<?php

require_once BORS_CORE.'/inc/functions/client/bors_bot_detect.php';

function bors_client_analyze()
{
	global $client;
	$data = array();
	bors_bot_detect(@$_SERVER['HTTP_USER_AGENT'], $data);
	$client['is_bot']		= empty($data['bot']) ? false : $data['bot'];
	$client['is_crawler']	= !empty($data['crawler']);
}
