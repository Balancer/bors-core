<?php
	if(config('bors_version_show'))
		header('X-Bors: v' .config('bors_version_show'));

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
		@file_put_contents($file = config('debug_hidden_log_dir')."/main-php-referers.log", @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
		exit("Do not user direct bors-call!");
	}

    $GLOBALS['stat']['start_microtime'] = microtime(true);
    $GLOBALS['stat']['start_time'] = time();

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');

    require_once('config.php');

	if(bors()->client()->is_bot() && config('bot_lavg_limit'))
	{
		$cache = &new BorsMemCache();
		if(!($load_avg = $cache->get('system-load-average')))
		{
			$uptime = explode(' ', exec('uptime'));
			$cache->set($load_avg = floatval($uptime[10]), 30);
		}

		if($load_avg > config('bot_lavg_limit'))
		{
//			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 600');

			@file_put_contents($file = config('debug_hidden_log_dir')."/blocked-bots.log", $_SERVER['REQUEST_URI']."/".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."; LA={$load_avg}\n", FILE_APPEND);
			@chmod($file, 0666);
			exit("Service Temporarily Unavailable");
		}
	}

	if(preg_match('!/_bors/trap/!', $_SERVER['REQUEST_URI']) && config('load_protect_trap'))
	{
		if(bors()->client()->is_bot())
			if(config('404_page_url'))
				return go(config('404_page_url'), true);
			
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');

		@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/logs/load_trap.log", 
			date('Y.m.d H:i:s ')
				.$_SERVER['REQUEST_URI']
				."; ref=".@$_SERVER['HTTP_REFERER'] 
				. "; IP=".@$_SERVER['REMOTE_ADDR']
				."; UA=".@$_SERVER['HTTP_USER_AGENT']
				."; LA={$load_avg}\n", FILE_APPEND);
		@chmod($file, 0666);

		exit("Service Temporarily Unavailable");
	}

	if(empty($GLOBALS['cms']['only_load']) && empty($_GET) && !empty($_SERVER['QUERY_STRING']))
	{
		foreach(split('[&\?]', $_SERVER['QUERY_STRING']) as $pair)
		{
			@list($var, $val) = explode('=', $pair);
			$var = urldecode($var);
			if(preg_match('/^(\w+)\[\]$/', $var, $m))
				$_GET[$m[1]][] = $_POST[$var][] = "$val";
			else
				$_GET[$var] = $_POST[$var] = "$val";
		}
	}

	$_GET = array_merge($_GET, $_POST);
	if(($ics = config('internal_charset')) != ($ocs = config('output_charset')))
		$_GET = array_iconv($ocs, $ics, $_GET);

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

/**********************************************************************************************************/
	$res = false;
	if($object = object_load($uri))
		$res = bors_object_show($object);
/**********************************************************************************************************/

	bors()->changed_save();

    list($usec, $sec) = explode(" ",microtime());
    $time = ((float)$usec + (float)$sec) - $GLOBALS['stat']['start_microtime'];

	if($time > config('timing_limit'))
	{
		@file_put_contents($file = config('timing_log'), $time . " [".$uri . "]: " . @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
		@chmod($file, 0666);
	}

	if(config('debug_timing') && is_string($res))
	{
		$deb = "<!--\n=== debug-info ===\n"
			."created = ".date('r')."\n";

		if($object)
		{
			$deb .= "class = {$object->class_name()}\n"
				."class_file = {$object->class_file()}\n";
			if($cs = $object->cache_static())
				$deb .= "cache static expire = ". date('r', time()+$cs)."\n";

			$deb .= "class_template = {$object->template()}\n";
		}

		$deb .= "\n=== debug counting: ===\n";
		$deb .= debug_count_info_all();

		$deb .= "\n=== debug timing: ===\n";
		$deb .= debug_timing_info_all();
		$deb .= "Total time: $time sec.\n";
		$deb .= "-->\n";

		$res = str_replace('</body>', $deb.'</body>', $res);
	}

	if(config('access_log'))
	{
		$data = array(
			'user_ip' => $_SERVER['REMOTE_ADDR'],
			'user_id' => bors()->user_id(),
			'server_uri' => $uri,
			'referer' => @$_SERVER['HTTP_REFERER'],
			'access_time' => $GLOBALS['stat']['start_time'],
			'operation_time' =>  str_replace(',', '.', microtime(true) - $GLOBALS['stat']['start_microtime']),
			'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
			'is_bot' => bors()->client()->is_bot(),
		);

		if($object)
		{
			$data['object_class_name'] = $object->class_name();
			$data['object_id'] = $object->id();
			$data['has_bors'] = 1;
			$data['has_bors_url'] = 1;
			$data['access_url'] = $object->url();
		}
		
		$x = object_new_instance('bors_access_log', $data);
		$x->store();
	}

	if($res === true || $res == 1)
		return;

	if($res)
	{
		echo $res;
		return;
	}

	if(empty($title))
		$title='';

	@file_put_contents($file = config('debug_hidden_log_dir')."/404.log", "$uri <= ".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
	@header("HTTP/1.0 404 Not Found");

	if(config('404_page_url'))
		return go(config('404_page_url'), true);

	if(config('404_show', true))
		echo ec("Page '$uri' not found");
