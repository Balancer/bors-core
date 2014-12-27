<?php

if(empty($GLOBALS['stat']['start_microtime']))
	$GLOBALS['stat']['start_microtime'] = microtime(true);

if(function_exists('xhprof_enable'))
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

// Инициализация фреймворка
require_once(dirname(__FILE__).'/init.php');

// Если в запрашиваемом URL присутствуют параметры - переносим их в строку запроса
// такая проблема всплывает на некоторых web-серверах.
if(preg_match('!^([^?]+)\?(.*)$!', $_SERVER['REQUEST_URI'], $m))
{
	$_SERVER['REQUEST_URI'] = $m[1];
	if(empty($_SERVER['QUERY_STRING']))
		$_SERVER['QUERY_STRING'] = $m[2];
}

// Если в имени хоста есть порт, то вырезаем
if(preg_match('!^(.+):\d+$!', $_SERVER['HTTP_HOST'], $m))
	$_SERVER['HTTP_HOST'] = $m[1];

// Если в имени хоста есть www, то убираем
// $_SERVER['HTTP_HOST'] = preg_replace('!^www\.!', '', $_SERVER['HTTP_HOST']);

// DOCUMENT_ROOT должен быть без слеша в конце.
if(preg_match('!^(.+)/$!', $_SERVER['DOCUMENT_ROOT'], $m))
	$_SERVER['DOCUMENT_ROOT'] = $m[1];

// Смотрим, нет ли доступных принудительных редиректов
if(
	file_exists($f = BORS_SITE.'/webroot/redirect.list')
	|| file_exists($f = BORS_SITE.'/data/webroot/redirect.list')
)
{
	$content = file_get_contents($f);
	$content = preg_replace('/^#\s+.+$/m', '', $content);
	$content = preg_replace('/^(.+)\s+#\s+.+$/m', '$1', $content);

	foreach(explode("\n", $content) as $s)
	{
		if(!preg_match('!^(\S+)\s+(~|=)>\s+(\S+)$!', trim($s), $m))
			continue;

		// url1 => url2 — прямой редирект
		if($m[2] == '=' && $m[1] == $_SERVER['REQUEST_URI'])
			return go($m[3]);

		// url1 ~> url2 - редирект с регекспом
		if($m[2] == '~' && preg_match("!{$m[1]}!", $_SERVER['REQUEST_URI']))
			return go(preg_replace("!{$m[1]}!", $m[3], $_SERVER['REQUEST_URI']));
	}
}

// Скажем, кто мы такие. Какой версии.
if(config('bors.version_show'))
	header('X-Bors-version: ' .config('bors.version_show'));

// А такого не должно быть. Если лоадер вызывается непосредственно, нужно
// разбираться, что это за фигня. Соответственно - в лог.
if($_SERVER['REQUEST_URI'] == '/bors-loader.php')
{
	@file_put_contents($file = config('debug_hidden_log_dir')."/main-php-referers.log",
		@$_SERVER['HTTP_REFERER'] . "; IP=".@$_SERVER['REMOTE_ADDR']
		."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
	exit("Do not use direct bors-call!\n");
}

bors_function_include('debug/hidden_log');
bors_client_analyze();
$is_bot = bors()->client()->is_bot();
$is_crawler = bors()->client()->is_crawler();

// var_dump($is_bot, $is_crawler, config('bot_lavg_limit'));

// Если это бот и включён лимит максимальной загрузки сервера
// то проверяем. И если загрузка превышает допустимую - просим подождать
//if($bot && config('bot_lavg_limit') && !in_array($bot, config('bot_whitelist', array())))
if($is_crawler && config('bot_lavg_limit'))
{
	$cache = new BorsMemCache();
	if(!($load_avg = $cache->get('system-load-average')))
	{
		$uptime = preg_split('/\s+/', exec('uptime'));
		$load_avg = floatval($uptime[10]);
		$cache->set($load_avg, 30);
	}

	if($load_avg > config('bot_lavg_limit'))
		bors_main_error_503('system_overload_crawlers', "$is_bot: LA=$load_avg");
}

// Ловушка для ботов. Если сунется в /_bors/trap/* (например, где-то на странице скрытая ссылка туда)
// то шлём его нафиг. Соответственно, /_bors/trap/ должен быть запрещённ в robots.txt
if(preg_match('!/_bors/trap/!', $_SERVER['REQUEST_URI']) && config('load_protect_trap'))
{
	if($is_bot)
		if(config('404_page_url'))
			return go(config('404_page_url'), true);

	//TODO: чёрт, надо сделать будет через hidden_log.
	@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/logs/load_trap.log", 
		date('Y.m.d H:i:s ')
			.$_SERVER['REQUEST_URI']
			."; ref=".@$_SERVER['HTTP_REFERER'] 
			. "; IP=".@$_SERVER['REMOTE_ADDR']
			."; UA=".@$_SERVER['HTTP_USER_AGENT']
			."; LA={$load_avg}\n", FILE_APPEND);
	@chmod($file, 0666);

	bors_main_error_503();
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

// Проверка на загрузку системы данным пользователем или ботом
// Если делает много тяжёлых запросов - просим подождать.

//var_dump(preg_split('/[,\s]+/', config('overload.skip_ips')));

if(config('access_log')
		&& !in_array($_SERVER['REMOTE_ADDR'], preg_split('/[,\s]+/', config('overload.skip_ips', '127.0.0.1')))
		&& !config('locked_db_time')
	)
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$sid = $is_bot ? $is_bot : $ip;
	$access_log_mem_name = 'access-load-summary-3-'.$sid;
	$session_user_load_summary_pack = bors_var::fast_get($access_log_mem_name, array(0, 0));
	$session_user_load_summary = $session_user_load_summary_pack[0]; // session_var('user.stat.load_summary', 0);

	if($session_user_load_summary_pack[1] < time() - 60)
		$session_user_load_summary = 0;

	$common_overload = config('overload_time', 0);
	$user_overload = config('user_overload_time', $common_overload);
	$bot_overload = config('bot_overload_time', $common_overload);

	if($is_bot && ($bl = config('limit.bot.'.$is_bot)))
		$bot_overload = $bl;

//	$admin_overload = config('admin_overload_time', $common_overload);

	if($user_overload || $bot_overload)
	{
		if(!$session_user_load_summary)
		{
			$dbh = new driver_mysql(config('bors_local_db'));
			if($is_bot)
				$session_user_load_summary = $dbh->select('bors_access_log', 'SUM(operation_time)', array(
					'is_bot' => $is_bot,
					'access_time>' => time() - 600,
				));
			else
			{
				$session_user_load_summary = $dbh->select('bors_access_log', 'SUM(operation_time)', array(
					'user_ip' => $_SERVER['REMOTE_ADDR'],
					'access_time>' => time() - 600,
				));
			}
		}

		if(!$is_crawler && $user_overload && $session_user_load_summary > $user_overload)
			bors_main_error_503('system_overload_users', $session_user_load_summary.' of '.$user_overload);

		if($is_crawler && $bot_overload && $session_user_load_summary > $bot_overload)
			bors_main_error_503('system_overload_crawlers', $session_user_load_summary.' of '.$bot_overload."\nbot=$is_bot");
	}
}

//if($is_bot || $is_crawler)
//	debug_hidden_log('system_overload_test', "$is_bot/$is_crawler, lavg=$load_avg, total=$session_user_load_summary, bot_overload=$bot_overload");

// Если кодировка вывода в браузер не та же, что внутренняя - то перекодируем
// все входные данные во внутреннюю кодировку
if(($ics = config('internal_charset')) != ($ocs = config('output_charset')))
	$_GET = array_iconv($ocs, $ics, $_GET);

require_once('engines/bors/object_show.php');
require_once('engines/bors/vhosts_loader.php');

$uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

if(config('bors.version_show'))
	header("X-request-url: $uri");

if($_SERVER['QUERY_STRING'] == 'del')
{
	$_SERVER['QUERY_STRING'] = 'act=del';
	$_GET['act'] = 'del';
}

if($_SERVER['QUERY_STRING'])
	$uri .= '?'.$_SERVER['QUERY_STRING'];

$GLOBALS['bors_full_request_url'] = $uri;

if($uri == 'http:///')
	exit("Incorrect url '$uri'");

/**********************************************************************************************************/
// Собственно, самое главное. Грузим объект и показываем его.
$res = false;
try
{
	if(config('debug.execute_trace'))
		debug_execute_trace("bors_load_uri('$uri');");

	config_set('__main_object_load', true); // костыли, ну и фиг с ними. Боком нигде не должно вылезти.
	if($object = bors_load_uri($uri))
	{
		config_set('__main_object_load', false);

		// Если это редирект
		if(!is_object($object))
		{
			if(config('bors.version_show'))
				header('X-bors-object: redirect to '.$object);

			return go($object);
		}

		if(config('bors.version_show'))
			header('X-bors-object: '.$object->internal_uri());

		// Новый метод вывода, полностью на самом объекте
		if(method_exists($object, 'show'))
		{
			if(config('debug.execute_trace'))
				debug_execute_trace("{$object}->show()");

			$res = $object->show();
		}

		if(!$res)	// Если новый метод не обработан, то выводим как раньше.
		{
			if(config('debug.execute_trace'))
				debug_execute_trace("bors_object_show($object)");

			$res = bors_object_show($object);
		}
	}
}
catch(Exception $e)
{
	@header('HTTP/1.1 500 Internal Server Error');
	bors_function_include('debug/trace');
	bors_function_include('debug/hidden_log');
//	var_dump($e->getTrace());
	$trace = debug_trace(0, false, -1, $e->getTrace());
	$message = $e->getMessage();
	debug_hidden_log('exception', "$message\n\n$trace", true, array('dont_show_user' => true));
	try
	{
		bors_message(ec("При попытке просмотра этой страницы возникла ошибка:\n")
			."<div class=\"red_box\">$message</div>\n"
			.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n~~~1")
			.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
//				'template' => 'xfile:default/popup.html',
		));
	}
	catch(Exception $e2)
	{
		bors()->set_main_object(NULL);
		bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
			."<div class=\"red_box\">$message</div>\n"
			.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n~~~2")
			.(config('site.is_dev') ? "<pre>$trace</pre>" : "<!--\n\n$trace\n\n-->"), array(
			'template' => 'xfile:default/popup.html',
		));
	}

	$res = true;
}

if(config('debug.execute_trace'))
	debug_execute_trace("process done. Return type is ".gettype($res)."; Next — changed save");

try
{
	bors()->changed_save();
}
catch(Exception $e)
{
	$error = bors_lib_exception::catch_html_code($e, ec("<div class=\"red_box\">Ошибка сохранения</div>"));
}

if(config('debug.execute_trace'))
	debug_execute_trace("Changes saved");

/**********************************************************************************************************/

// Записываем, если нужно, кто получал объект и сколько ушло времени на его получение.
if(config('access_log'))
{
	if(config('debug.execute_trace'))
		debug_execute_trace("Access log begin");

	$operation_time = microtime(true) - $GLOBALS['stat']['start_microtime'];

	$data = array(
		'user_ip' => $_SERVER['REMOTE_ADDR'],
		'user_id' => bors()->user_id(),
		'server_uri' => $uri,
		'referer' => empty($_SERVER['HTTP_REFERER']) ? NULL : $_SERVER['HTTP_REFERER'],
		'access_time' => round($GLOBALS['stat']['start_microtime']),
		'operation_time' =>  str_replace(',', '.', $operation_time),
		'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
		'is_bot' => $is_bot ? $is_bot : NULL,
		'is_crawler' => $is_crawler,
	);

	if(empty($object) || !is_object($object))
	{
		$data['object_class_name'] = $_SERVER['REQUEST_URI'];
		$data['access_url'] = $uri;
	}
	else
	{
		$data['object_class_name'] = $object->class_name();
		$data['object_id'] = $object->id();
		$data['has_bors'] = 1;
		$data['has_bors_url'] = 1;
		$data['access_url'] = ($u=$object->url()) ? $u : $uri;
	}

	bors_new('bors_access_log', $data);

//	NOTIFY - переменная не найдена. Найти.
//	if($session_user_load_summary)
//		set_session_var('user.stat.load_summary', $session_user_load_summary + $operation_time);

	if(!empty($access_log_mem_name))
	{
		bors_var::fast_set($access_log_mem_name, array($session_user_load_summary + $operation_time, time()));
//		if(config('is_debug'))
//			debug_hidden_log('00-system_overload_set', $session_user_load_summary + $operation_time, 0);
	}
}

if(config('debug.execute_trace'))
	debug_execute_trace("process done. Check work time...");

// Общее время работы
$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

// Если время работы превышает заданный лимит, то логгируем
if($time > config('timing_limit'))
{
	@file_put_contents($file = config('timing_log'), $time . " [".$uri . "]: " . defval($_SERVER, 'HTTP_REFERER') . "; IP=".@$_SERVER['REMOTE_ADDR']."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
	@chmod($file, 0666);
}

// Если показываем отладочную инфу, то описываем её в конец выводимой страницы комментарием.
if(config('debug.timing') && is_string($res))
{
	$deb = "<!--\n=== debug-info ===\n"
		."BORS_CORE = ".BORS_CORE."\n"
		."log_dir = ".config('debug_hidden_log_dir')."\n"
		."created = ".date('r')."\n";

	if($object)
	{
		foreach(explode(' ', 'class_name class_file template body_template') as $var)
			if($val = @$object->get($var))
				$deb .= "$var = $val\n";

		if($cs = $object->cache_static())
			$deb .= "cache static expire = ". date('r', time()+$cs)."\n";
	}

	if(config('is_developer'))
	{
		$deb .= "\n=== config ===\n"
			. "cache_database = ".config('cache_database')."\n";
	}

	bors_function_include('debug/vars_info');
	bors_function_include('debug/count');
	bors_function_include('debug/count_info_all');
	bors_function_include('debug/timing_info_all');
	if($deb_vars = debug_vars_info())
	{
		$deb .= "\n=== debug vars: ===\n";
		$deb .= $deb_vars;
	}

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

if(!empty($GLOBALS['bors_profiling']['mysql-queries']) && count($GLOBALS['bors_profiling']['mysql-queries']) > 30)
	bors_debug::syslog('profiling-mysql', "Too many queries: ".print_r($GLOBALS['bors_profiling']['mysql-queries'], true));

if(function_exists('xhprof_enable') && $time >= config('debug.profile_min', 1.0))
{
	$xhprof_data = xhprof_disable();

	$XHPROF_ROOT = COMPOSER_ROOT."/vendor/facebook/xhprof";
	if(file_exists($XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php"))
	{
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
	}

	if(class_exists('XHProfRuns_Default'))
	{
		$xhprof_runs = new XHProfRuns_Default();
		$run_id = $xhprof_runs->save_run($xhprof_data, urlencode(preg_replace('!\W+!', '-', preg_replace("!^\w+://!", '', $uri))));
	}

//	echo "http://localhost/xhprof/xhprof_html/index.php?run={$run_id}&source=xhprof_testing\n";
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

if($cn = config('404.class_name'))
{
	if($object404 = bors_load($cn, $uri))
	{
		if(method_exists($object404, 'show'))
			$res = $object404->show();

		if(!$res)
			$res = bors_object_show($object404);
	}

	if($res)
	{
		echo $res;
		return;
	}
}

if($url = config('404.url'))
	return readfile($url);

if(config('404_page_url'))
{
	return go(config('404_page_url'), true);
}

if(config('404_show', true))
	echo ec("Page '$uri' not found\n<!--\nBORS_SITE=".BORS_SITE."\nBORS_CORE=".BORS_CORE."\n-->");

function bors_main_error_503($logfile = NULL, $message = 'error 503', $trace = false)
{
//	header('HTTP/1.1 503 Service Temporarily Unavailable');

	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 600');

	if($logfile)
		debug_hidden_log($logfile, $message, $trace);

	if($url = config('503.url'))
		readfile($url);
	else
		echo "Service Temporarily Unavailable";

	exit();
}
