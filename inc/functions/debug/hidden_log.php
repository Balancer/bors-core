<?php

function debug_hidden_log($type, $message=NULL, $trace = true, $args = array())
{
	if(!$message)
	{
		$message = $type;
		$type = 'common';
	}

//	bors_debug::log($type, $message, 'hidden');
	if(!($out_dir = config('debug_hidden_log_dir')))
		return;

	if($trace && empty($args['dont_show_user']))
		$user = object_property(bors(), 'user');

	if(popval($args, 'notime'))
		$out = '';
	else
		$out = strftime('%Y-%m-%d %H:%M:%S: ');

	$out .= $message . "\n";

	if($trace !== false)
	{
		bors_function_include('debug/trace');
		require_once('inc/locales.php');

		if($trace === true)
			$trace_out = debug_trace(0, false);
		elseif($trace >= 1)
			$trace_out = debug_trace(0, false, $trace);
		else
			$trace_out = '';

		if(!empty($_GET))
			$data = "_GET=".print_r($_GET, true)."\n";
		else
			$data = "";

		$out .= "url: http://".@$_SERVER['HTTP_HOST'].@$_SERVER['REQUEST_URI']
			.(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '')."\n"
			. (!empty($_SERVER['HTTP_REFERER']) ? "referer: ".$_SERVER['HTTP_REFERER'] : "")."\n"
			. (!empty($_SERVER['REMOTE_ADDR']) ? "addr: ".$_SERVER['REMOTE_ADDR'] : "")."\n"
			. (!empty($_SERVER['HTTP_USER_AGENT']) ? "user agent: ".$_SERVER['HTTP_USER_AGENT'] : "")."\n"
			. (@$user ? 'user = '.dc($user->title()) . ' [' .bors()->user_id()."]\n": '')
			. $data
			. $trace_out
			. "\n---------------------------\n\n";
	}

//	if(!empty($args['mkpath']))
//		mkpath(dirname("{$out_dir}/{$type}.log"));

	if(!empty($args['append']))
		$out .= "\n".$args['append'];

	file_put_contents($file = "{$out_dir}/{$type}.log", $out, FILE_APPEND);
	@chmod($file, 0666);
}
