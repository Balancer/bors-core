<?php
	header('X-Bors: begin');

//	ini_set("xdebug.profiler_enable", "1");


	if(preg_match('!^(.+?)\?(.+)$!', $_SERVER['REQUEST_URI'], $m))
	{
		$_SERVER['REQUEST_URI'] = $m[1];
		if(empty($_SERVER['QUERY_STRING']))
			$_SERVER['QUERY_STRING'] = $m[2];
	}


	if($_SERVER['REQUEST_URI'] == '/cms/main.php')
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

    list($usec, $sec) = explode(" ",microtime());
    $GLOBALS['stat']['start_microtime'] = ((float)$usec + (float)$sec);

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');

    require_once('config.php');
	bors_init();

	if($client['is_bot'] && config('bot_lavg_limit'))
	{
		$cache = &new MemCache();
		if(!($load_avg = $cache->get('system-load-average')))
		{
			$uptime=explode(' ', exec('uptime'));
			$cache->set($load_avg = floatval($uptime[10]), 30);
		}

		if($load_avg > config('bot_lavg_limit'))
		{
#			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 600');

			@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/blocked-bots.log", $_SERVER['REQUEST_URI']."/".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."; LA={$load_avg}\n", FILE_APPEND);
			@chmod($file, 0666);
			exit("Service Temporarily Unavailable");
		}
	}

	if(empty($GLOBALS['cms']['only_load']) && empty($_GET) && !empty($_SERVER['QUERY_STRING']))
	{
		foreach(split("&", $_SERVER['QUERY_STRING']) as $pair)
		{
			@list($var, $val) = split("=", $pair);
			$_GET[$var] = "$val";
			$_POST[$var] = "$val";
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

	if($_SERVER['QUERY_STRING'] == 'fromlist')
		$_SERVER['QUERY_STRING'] = '';

	$object = NULL;
	if(!preg_match('!^\w+($|&)!', $_SERVER['QUERY_STRING']))
		if($object = object_load($uri))
			@header("X-Bors-loaded: ".$object->class_name());

	if(!$object || preg_match('!^[\w\-]+$!', $_SERVER['QUERY_STRING']) || ($ret = bors_object_show($object))!== true)
	{
	    @header("X-Bors-obsolete: $uri");
//		@header("X-QS: ".str_replace("\n", ' ', print_r($_GET, true)));
	    require_once("funcs/handlers.php");

		if(empty($GLOBALS['cms']['only_load']))
		{
			$_SERVER['HTTP_HOST'] = str_replace(':80', '', $_SERVER['HTTP_HOST']);

    		$_SERVER['REQUEST_URI'] = preg_replace("!^(.+?)\?.*?$!", "$1", $_SERVER['REQUEST_URI']);
		}
	
		$parse = parse_url($uri);
	
		$cs = &new CacheStaticFile($uri);
		if(!empty($GLOBALS['cms']['cache_static']) 
			&& empty($_GET) 
			&& empty($_POST) 
			&& ($cs_uri = $cs->get_name($uri)) 
			&& file_exists($cs->get_file($uri)))
		{
			go($cs_uri); 
			exit();
		}

		$GLOBALS['cms']['page_number'] = 1;

		if(empty($GLOBALS['main_uri']))
			$GLOBALS['main_uri'] = $uri;

		$GLOBALS['cms']['page_path'] = $GLOBALS['main_uri'];

		$GLOBALS['ref'] = @$_SERVER['HTTP_REFERER'];

		if(empty($GLOBALS['cms']['disable']['log_session']))
		{
			include_once("funcs/logs.php");
			log_session_update();
		}
	
		include_once("funcs/handlers.php");

		$GLOBALS['cms_patterns'] = array();
		$GLOBALS['cms_actions']  = array();

		handlers_load();

		if(!empty($GLOBALS['cms']['only_load']))
			return;
		
		$ret = handlers_exec();
	}

	bors()->changed_save();

    list($usec, $sec) = explode(" ",microtime());
    $time = ((float)$usec + (float)$sec) - $GLOBALS['stat']['start_microtime'];

	if($time > 1)
	{
		@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/cms/logs/timing.log", $time . " [".$uri . "]: " . @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
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
		
//	echo ec("Страница '$uri' не найдена. Попробуйте <a href=\"$uri?edit\">создать её</a>");
//	echo "</pre>";
