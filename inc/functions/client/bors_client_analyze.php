<?php

bors_function_include('client/bors_bot_detect');

function bors_client_analyze()
{
	global $client;
	$data = array();
	bors_bot_detect(@$_SERVER['HTTP_USER_AGENT'], $data);
	$client['is_bot'] = @$data['bot'];
	$client['is_crowler'] = @$data['crowler'];
}
