<?php

list($usec, $sec) = explode(" ",microtime());
$GLOBALS['stat']['start_microtime'] = ((float)$usec + (float)$sec);

header('X-Bors-begin', $GLOBALS['stat']['start_microtime']);

// Если в REQUEST_URI есть GET-параметры, то выдёргиваем их
// и прописываем в QUERY_STRING
if(preg_match('!^(.+?)\?(.+)$!', $_SERVER['REQUEST_URI'], $m))
{
	$_SERVER['REQUEST_URI'] = $m[1];
	if(empty($_SERVER['QUERY_STRING']))
		$_SERVER['QUERY_STRING'] = $m[2];
}

require_once('config.php');

global $client;

// Смотрим, не бот ли нас дёргает.
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

// Если дёргает бот и машина загружена, то сообщаем ему, чтобы зашёл попозже.
if($client['is_bot'] && config('bot_lavg_limit'))
{
	$cache = &new MemCache();
	if(!($load_avg = $cache->get('system-load-average')))
	{
		$uptime = explode(' ', exec('uptime'));
		$cache->set($load_avg = floatval($uptime[13]), 30);
	}

	if($load_avg > config('bot_lavg_limit'))
	{
#		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 600');
	
		if(config('blocked_bots_log'))
		{
			@file_put_contents($file = config('blocked_bots_log'), $_SERVER['REQUEST_URI']."/".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."; LA={$load_avg}\n", FILE_APPEND);
			@chmod($file, 0666);
		}
		exit("Service Temporarily Unavailable");
	}
}

// Боремся с глюками lighttpd при редиректе по 404-й ошибке
// Если массив GET пуст, а строка запроса содержит параметры,
// то вытягиваем их вручную.
if(empty($_GET) && !empty($_SERVER['QUERY_STRING']))
{
	foreach(split("&", $_SERVER['QUERY_STRING']) as $pair)
	{
		@list($var, $val) = split("=", $pair);
		$_GET[$var] = "$val";
		$_POST[$var] = "$val";
	}
}

// Объединяем GET и POST.
$_GET = array_merge($_GET, $_POST);

bors_init();

// Конструируем полную URL.
$url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$object = NULL;
//if(!preg_match('!^\w+($|&)!', $_SERVER['QUERY_STRING']))
if($object = object_load($url))
	@header("X-Bors-loaded: ".$object->class_name());

if(!$object || preg_match('!^\w+$!', $_SERVER['QUERY_STRING']) || ($ret = bors_object_show($object))!== true)
	echo time()."<br/>\n";
// echo $url;
