<?php

function debug_hidden_log($type, $message=NULL, $trace = true, $args = array())
{
	if(!($out_dir = config('debug_hidden_log_dir')))
		return;

	if(!$message)
	{
		$message = $type;
		$type = 'common';
	}

//	bors_debug::log($type, $message, 'hidden');

	bors_debug::timing_start('hidden_log');

	if($trace && empty($args['dont_show_user']) && class_exists('bors_class_loader', false) && function_exists('bors'))
		$user = bors()->user();
	else
		$user = NULL;

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

		if(!empty($_POST))
			$data .= "_POST=".print_r($_POST, true)."\n";

		$out .= "\tmain_url: ".@$GLOBALS['main_uri']."\n";

		foreach(array('HTTP_HOST', 'REQUEST_URI', 'QUERY_STRING', 'HTTP_REFERER', 'REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_ACCEPT', 'REQUEST_METHOD') as $name)
			if(!empty($_SERVER[$name]))
				$out .= "\t{$name}: ".$_SERVER[$name]."\n";

		$out .= (@$user ? "\tuser: ".dc($user->title()) . ' [' .bors()->user_id()."]\n": '')
			. $data
			. $trace_out
			. "\n-------------------------------------------------------------------\n\n";
	}

	if(!empty($args['append']))
		$out .= "\n".$args['append'];

	if(!file_exists($out_dir))
	{
		mkpath($out_dir);
		@chmod($out_dir, 0777);
	}

	@file_put_contents($file = "{$out_dir}/{$type}.log", $out, FILE_APPEND);
	@chmod($file, 0666);
	bors_debug::timing_stop('hidden_log');
}
