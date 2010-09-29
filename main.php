<?php

$GLOBALS['stat']['start_microtime'] = microtime(true);
$GLOBALS['stat']['start_time'] = time();

// Если в запрашиваемом URL присутствуют параметры - переносим их в строку запроса
// такая проблема всплывает на некоторых web-серверах.
if(preg_match('!^([^?]+)\?(.*)$!', $_SERVER['REQUEST_URI'], $m))
{
	$_SERVER['REQUEST_URI'] = $m[1];
	if(empty($_SERVER['QUERY_STRING']))
		$_SERVER['QUERY_STRING'] = $m[2];
}

// DOCUMENT_ROOT должен быть без слеша в конце.
if(preg_match('!^(.+)/$!', $_SERVER['DOCUMENT_ROOT'], $m))
	$_SERVER['DOCUMENT_ROOT'] = $m[1];

// Инициализация фреймворка
require_once(dirname(__FILE__).'/init.php');

// Проверка на загрузку системы данным пользователем.
// Если делает много тяжёлых запросов - просим подождать.
if(config('access_log') && config('overload_time') && $_SERVER['REMOTE_ADDR'] != '127.0.0.1')
{
	$dbh = new driver_mysql(config('main_bors_db'));
	$total = $dbh->select('bors_access_log', 'SUM(operation_time)', array(
		'user_ip' => $_SERVER['REMOTE_ADDR'],
		'access_time>' => time() - 600,
	));

	if($total > config('overload_time'))
	{
		debug_hidden_log('system_overload', $total);

		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 600');
		exit("Service Temporarily Unavailable");
	}
}

// Скажем, кто мы такие. Какой версии.
if(config('bors_version_show'))
	header('X-Bors: v' .config('bors_version_show'));

// А такого не должно быть. Если лоадер вызывается непосредственно, нужно
// разбираться, что это за фигня. Соответственно - в лог.
if($_SERVER['REQUEST_URI'] == '/bors-loader.php')
{
//	print_d($_SERVER);
	@file_put_contents($file = config('debug_hidden_log_dir')."/main-php-referers.log", @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
	exit("Do not use direct bors-call!\n");
}

// Если это бот и включён лимит максимальной загрузки сервера
// то проверяем. И если загрузка превышает допустимую - просим подождать
if(bors()->client()->is_bot() && config('bot_lavg_limit'))
{
	$cache = new BorsMemCache();
	if(!($load_avg = $cache->get('system-load-average')))
	{
		$uptime = explode(' ', exec('uptime'));
		$cache->set($load_avg = floatval($uptime[10]), 30);
	}

	if($load_avg > config('bot_lavg_limit'))
	{
//		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 600');

		@file_put_contents($file = config('debug_hidden_log_dir')."/blocked-bots.log", $_SERVER['REQUEST_URI']."/".@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."; LA={$load_avg}\n", FILE_APPEND);
		@chmod($file, 0666);
		exit("Service Temporarily Unavailable");
	}
}

// Ловушка для ботов. Если сунется в /_bors/trap/* (например, где-то на странице скрытая ссылка туда)
// то шлём его нафиг. Соответственно, /_bors/trap/ должен быть запрещённ в robots.txt
if(preg_match('!/_bors/trap/!', $_SERVER['REQUEST_URI']) && config('load_protect_trap'))
{
	if(bors()->client()->is_bot())
		if(config('404_page_url'))
			return go(config('404_page_url'), true);

	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 3600');

	//TODO: чёрт, надо сделать будет через hidden_log.
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

// Если есть строка запроса, но пустой $_GET, то PHP чего-то не распарсил. Бывает на некоторых
// режимах в некоторых серверах.
//TODO: проверить по проектам, чтобы нигде не использовалось $GLOBALS['cms']['only_load'] и убрать.
if(empty($GLOBALS['cms']['only_load']) && empty($_GET) && !empty($_SERVER['QUERY_STRING']))
{
	parse_str($_SERVER['QUERY_STRING'], $_GET);
	if(empty($_POST))
		$_POST = $_GET; // поскольку мы не знаем, что там и куда
}

$_GET = array_merge($_GET, $_POST); // но, вообще, нужно с этим завязывать

// Если кодировка вывода в браузер не та же, что внутренняя - то перекодируем
// все входные данные во внутреннюю кодировку
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

if($_SERVER['QUERY_STRING'])
	$uri .= '?'.$_SERVER['QUERY_STRING'];

/**********************************************************************************************************/
// Собственно, самое главное. Грузим объект и показываем его.
$res = false;
if($object = object_load($uri))
	$res = bors_object_show($object);
/**********************************************************************************************************/

// Записываем, если нужно, кто получал объект и сколько ушло времени на его получение.
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
}

bors()->changed_save();

// Общее время работы
$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

// Если время работы превышает заданный лимит, то логгируем
if($time > config('timing_limit'))
{
	@file_put_contents($file = config('timing_log'), $time . " [".$uri . "]: " . @$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
}

// Если показываем отладочную инфу, то описываем её в конец выводимой страницы комментарием.
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

	$deb .= "\n=== debug vars: ===\n";
	$deb .= debug_vars_info();

	$deb .= "\n=== debug counting: ===\n";
	$deb .= debug_count_info_all();

	$deb .= "\n=== debug timing: ===\n";
	$deb .= debug_timing_info_all();
	$deb .= "Total time: $time sec.\n";
	$deb .= "-->\n";

	if(config('is_developer'))
		debug_hidden_log('debug_timing', $deb, false);

	$res = str_ireplace('</body>', $deb.'</body>', $res);
}

// Если объект всё, что нужно нарисовал сам, то больше нам делать нечего. Выход.
if($res === true || $res == 1)
	return;

// Если объект вернул строку, то рисуем её и выходим.
if($res)
{
	echo $res;
	return;
}

// Если дошли до сюда, то мы ничего не нашли. Дальше - обработка 404-й ошибки.

if(config('404_logging'))
{
	if(!empty($_SERVER['HTTP_REFERER']) && strpos($uri, 'files/') === false)
	{
		if(preg_match('/aviaport/', $_SERVER['HTTP_REFERER']))
			$fname_404 = '404-internal';
		else
			$fname_404 = '404-external';
	}
	else
		$fname_404 = '404-other';

	$info = array("url = $uri");
	if($referer = @$_SERVER['HTTP_REFERER'])
		$info[] = "referer = $referer";
	$info[] = "user ip = ".bors()->client()->ip();
	$info[] = "user agent = ".bors()->client()->agent();
	$info[] = "user place = ".bors()->client()->place();
	bors_log::info(join("\n", $info), $fname_404);

	@file_put_contents($file = config('debug_hidden_log_dir')."/{$fname_404}.log", "$uri <= ".@$_SERVER['HTTP_REFERER'] 
		. " ; IP=".@$_SERVER['REMOTE_ADDR']
		. "; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
}

@header("HTTP/1.0 404 Not Found");

if(config('404_page_url'))
	return go(config('404_page_url'), true);

if(config('404_show', true))
	echo ec("Page '$uri' not found");
