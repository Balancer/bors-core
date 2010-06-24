<?php

function goq($uri, $permanent = false, $time = 0, $exit = false)
{
	if(strpos($uri, '?') === false && !empty($_SERVER['QUERY_STRING']))
		$uri .= '?'.$_SERVER['QUERY_STRING'];

	return go($uri, $permanent, $time, $exit);
}

function go($uri, $permanent = false, $time = 0, $exit = false)
{
	bors()->changed_save();

	if(config('debug_redirect_trace'))
		return debug_exit("Go to <a href=\"{$uri}\">{$uri}</a>");

	if(config('do_not_exit'))
		return true;

	if(config('bors_version_show'))
		@header("X-bors-go: {$uri}");

    if(!headers_sent($filename, $linenum) && $time==0) 
    {
		if($permanent)
            header("Status: 301 Moved Permanently");
		else
            header("Status: 302 Moved Temporarily");
		if(preg_match("!\n!", $uri))
			echolog("cr in uri '$uri'", 1);

		header("Location: $uri");

		if($exit)
			bors_exit('');
    }

	echo "<meta http-equiv=\"refresh\" content=\"$time; url=$uri\">";

	if($exit)
		bors_exit('');

	return true;
}

function go_ref($def = "/")
{
	unset($_SERVER['QUERY_STRING']);

	if(!empty($GLOBALS['ref']))
		return go($GLOBALS['ref']);

	if(!empty($_SERVER['HTTP_REFERER']))
		return go($_SERVER['HTTP_REFERER']);

	return go($def);
}

function go_reload()
{
	return go($_SERVER['REQUEST_URI']);
}

function go_message($message, $params = array())
{
	$error		= defval($params, 'error', true);
	$go			= defval($params, 'go', $_SERVER['REQUEST_URI']);
	$permanent	= defval($params, 'permanent', false);

	$type		= defval($params, 'type', NULL);
	switch($type)
	{
		case 'success':
			set_session_var('success_message', $message);
			break;
		case 'error':
			set_session_var('error_message', $message);
			break;
		case 'notice':
			set_session_var('notice_message', $message);
			break;
		default:
			if($error)
				set_session_var('error_message', $message);
			else
				set_session_var('success_message', $message);
			break;
	}

	if(($ef = defval($params, 'error_fields')))
		set_session_var('error_fields', $ef);

	if(defval($params, 'ref'))
		return go_ref($go, $permanent);
	else
		return go($go, $permanent);
}

function go_ref_message($message, $params = array())
{
	$params['ref'] = true;
	return go_message($message, $params);
}
