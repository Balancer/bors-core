<?php
	if(config('bors_version_show'))
		header('X-Bors: v' .config('bors_version_show'));

//	if($_SERVER['REMOTE_ADDR'] == '89.178.142.225')
//		$_GET['XDEBUG_PROFILE'] = 1;
//		ini_set("xdebug.profiler_enable", "1");

	if(preg_match('!^([^?]+)\?(.*)$!', $_SERVER['REQUEST_URI'], $m))
	{
		$_SERVER['REQUEST_URI'] = $m[1];
		if(empty($_SERVER['QUERY_STRING']))
			$_SERVER['QUERY_STRING'] = $m[2];
	}

	if(preg_match('!^(.+)/$!', $_SERVER['DOCUMENT_ROOT'], $m))
		$_SERVER['DOCUMENT_ROOT'] = $m[1];

	if($_SERVER['REQUEST_URI'] == '/cms/main.php' || $_SERVER['REQUEST_URI'] == '/bors.php' || $_SERVER['REQUEST_URI'] == '/bors-loader.php')
	{
		@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/main-php-referers.log", @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
		exit("Link error");
	}

/*	if($_SERVER['REMOTE_ADDR'] == '89.108.87.121')
	{
		@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/main-php-89.108.87.121.log", $_SERVER['REQUEST_URI'] . "; ref=" . @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
		exit("Link error");
	}
*/
	global $client;
	$client['is_bot'] = false;
	foreach(array(
			'yahoo' => 'Yahoo',
			'rambler' => 'Rambler',
			'google' => 'Google',
			'yandex' => 'Yandex',
		) as $pattern => $bot)
	{
		if(preg_match("!".$pattern."!i", $_SERVER['HTTP_USER_AGENT']))
		{
			$client['is_bot'] = $bot;
			break;
		}
	}

    $GLOBALS['stat']['start_microtime'] = microtime(true);

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');

    require_once('config.php');
	
	ini_set('default_charset', config('default_character_set', 'utf-8'));
	setlocale(LC_ALL, config('locale', 'ru_RU.UTF-8'));
	
	bors_init();

	if($client['is_bot'] && config('bot_lavg_limit'))
	{
		$cache = &new BorsMemCache();
		if(!($load_avg = $cache->get('system-load-average')))
		{
			$uptime=explode(' ', exec('uptime'));
			$cache->set($load_avg = floatval($uptime[10]), 30);
		}

		if($load_avg > config('bot_lavg_limit'))
		{
//			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 600');

			@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/blocked-bots.log", $_SERVER['REQUEST_URI']."/".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."; LA={$load_avg}\n", FILE_APPEND);
			@chmod($file, 0666);
			exit("Service Temporarily Unavailable");
		}
	}

	if(empty($GLOBALS['cms']['only_load']) && empty($_GET) && !empty($_SERVER['QUERY_STRING']))
	{
		foreach(explode('&', $_SERVER['QUERY_STRING']) as $pair)
		{
			@list($var, $val) = explode('=', $pair);
			if(preg_match('/^(\w+)\[\]$/', $var, $m))
				$_GET[$m[1]][] = $_POST[$var][] = "$val";
			else
				$_GET[$var] = $_POST[$var] = "$val";
		}
	}


	$_GET = array_merge($_GET, $_POST);

	require_once('engines/bors/object_show.php');
	require_once('engines/bors/vhosts_loader.php');

	$uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	if($_SERVER['QUERY_STRING'] == 'del')
	{
		$_SERVER['QUERY_STRING'] = 'act=del';
		$_GET['act'] = 'del';
	}

	if($_SERVER['QUERY_STRING'] == 'fromlist') //TODO: АвиаПортовская заглушка
		$_SERVER['QUERY_STRING'] = '';

	if($_SERVER['QUERY_STRING'])
		$uri .= '?'.$_SERVER['QUERY_STRING'];

	$ret = false;
	if($object = object_load($uri))
		$ret = bors_object_show($object);

	//TODO: снести совсем старый код.
	/*
	if(!$object || $ret !== true)
	{
		if(config('obsolete_use_handlers_system'))
		{
			require_once('obsolete/handlers.php');
			$ret = main_handlers_engine($uri);
		}
		else
			$ret = false;
	}
	*/

	bors()->changed_save();

    list($usec, $sec) = explode(" ",microtime());
    $time = ((float)$usec + (float)$sec) - $GLOBALS['stat']['start_microtime'];

	if($time > config('timing_limit'))
	{
		@file_put_contents($file = config('timing_log'), $time . " [".$uri . "]: " . @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
	}

	if(config('debug_timing'))
	{
		echo "<!--\nDebug timing:\n";
		echo "Total time: $time sec.\n";
		echo debug_timing_info_all();
		echo "-->\n";
	}
	
	if($ret === true)
		return;

	if($ret !== false)
		$uri = $ret;


//	echo "<pre>";

	if(empty($title))
		$title='';

	@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/404.log", "$uri <= ".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);

	if(config('404_page_url'))
		return go(config('404_page_url'), true);

	if(config('404_show'))
		echo ec("Page '$uri' not found");
//	echo "</pre>";

//	if(preg_match('!^(.+)(</html>.*)$!is', $out, $m))
//		$out = $m[1].debug_timing_info_all().$m[2];
