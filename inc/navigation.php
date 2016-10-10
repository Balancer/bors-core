<?php

use B2\Cfg;

function goq($uri, $permanent = false, $time = 0, $exit = false)
{
	if(strpos($uri, '?') === false && !empty($_SERVER['QUERY_STRING']))
		$uri .= '?'.$_SERVER['QUERY_STRING'];

	return go($uri, $permanent, $time, $exit);
}

function go($uri, $permanent = false, $time = 0, $exit = false)
{
	bors()->changed_save();

    if(empty($uri))
		bors_debug::syslog('__errors_navigation', 'Try to go to empty url!');

	if(!empty($_GET['inframe']))
		$uri = url_append_param($uri, 'inframe', 'yes');

	Cfg::set('_debug_go_uri', $uri);

	if(Cfg::get('debug_redirect_trace'))
	{
		$body = ec("<p>Это режим отладки переходов. При его
			отключении Вы автоматически будете перемещены по ссылке
			<a href=\"{$uri}\" class=\"btn\">{$uri}</a></p>\n");

		if(!empty($SESSION))
			$body .= '<pre>$_SESSION:</pre>'.print_r($_SESSION, true)."<br/>\n";

		$body .= bors_debug::trace(1, true);

		if($x = @bors()->tmp_go_obj)
			$body .= '<pre>'.bors_objects_helper::object_info($x).'</pre>';

		$body .= '<xmp>'.print_r($_GET, true).'</xmp>';

		$body .= "Go to <a href=\"{$uri}\" class=\"btn\">{$uri}</a>";

		echo twitter_bootstrap::raw_message(array(
			'this' => bors_load('bors_pages_fake', array(
				'title' => ec('Перехвачанный редирект'),
				'body' => $body,
			)),
		));

		return true;
	}

	if(Cfg::get('do_not_exit'))
		return true;

	if(Cfg::get('bors.version_show'))
		@header("X-bors-go: {$uri}");

    if(!headers_sent($filename, $linenum) && $time==0 && !Cfg::get('redirect_by_html'))
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
	{
		$GLOBALS['ref'] = defval($params, 'go');
		return go_ref($go, $permanent);
	}
	else
		return go($go, $permanent);
}

function go_ref_message($message, $params = array())
{
	$params['ref'] = true;
	return go_message($message, $params);
}
