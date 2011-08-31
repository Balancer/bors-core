<?php

class bors_lib_servers_web
{
	var $_uri, $_query, $_host, $_root, $_remote_addr, $_web_server;

	function __construct($web_server = NULL)
	{
		$this->_web_server = $web_server;
		$this->_status = NULL;
	}

	function assign_url($url)
	{
		$url_data = parse_url($url);
		$this->_url = defval($url_data, 'path', '/');
		$this->_query = defval($url_data, 'query');
		$this->_host = defval($url_data, 'host', $this->_host ? $this->_host : 'localhost');
		$this->_root = config('nanoserv_root');
	}

	function assign_server()
	{
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

		$this->_url = $_SERVER['REQUEST_URI'];
		$this->_query = $_SERVER['QUERY_STRING'];
		$this->_host = $_SERVER['HTTP_HOST'];
		$this->_root = $_SERVER['DOCUMENT_ROOT'];
	}

	function prepare()
	{
		// Если в имени хоста есть порт, то вырезаем
		if(preg_match('!^(.+):(\d+)$!', $this->_host, $m))
		{
			$this->_host = $m[1];
			$this->_port = $m[2];
		}

		bors()->server()->_web_server = $this;
	}

	function header($header)
	{
		header($header);
	}

	function err($code, $param = NULL)
	{
		$this->_status = $code;
		switch($code)
		{
			case 200:
				break;
			case 404:
				$this->header("HTTP/1.0 404 Not Found");
				break;
			case 503:
				$this->header('Status: 503 Service Temporarily Unavailable');
//				if($this->)
//					header('HTTP/1.1 503 Service Temporarily Unavailable');
				$this->header('Retry-After: '.($param?$param:600));
				break;
		}
	}

	function do_request()
	{
		$this->prepare();

		// Скажем, кто мы такие. Какой версии.
		if(config('bors_version_show'))
			$this->header('X-Bors: v' .config('bors_version_show'));

		$is_bot = bors()->client()->is_bot();

		// Если это бот и включён лимит максимальной загрузки сервера
		// то проверяем. И если загрузка превышает допустимую - просим подождать
		//if($bot && config('bot_lavg_limit') && !in_array($bot, config('bot_whitelist', array())))
		if($is_bot && config('bot_lavg_limit'))
		{
			$cache = new BorsMemCache();
			if(!($load_avg = $cache->get('system-load-average')))
			{
				$uptime = preg_split('/\s+/', exec('uptime'));
				$load_avg = floatval($uptime[10]);
				$cache->set($load_avg, 30);
			}

			if($load_avg > config('bot_lavg_limit'))
			{
				$this->err(503, 600); // Status: 503 Service Temporarily Unavailable

				bors_debug::syslog('system_overload_bots', $loadavg, false);
				exit("Service Temporarily Unavailable");
			}
		}

		// Ловушка для ботов. Если сунется в /_bors/trap/* (например, где-то на странице скрытая ссылка туда)
		// то шлём его нафиг. Соответственно, /_bors/trap/ должен быть запрещённ в robots.txt
		if(preg_match('!/_bors/trap/!', $this->_url) && config('load_protect_trap'))
		{
			if($is_bot && config('404_page_url'))
				return go(config('404_page_url'), true);

			$this->err(503, 3600); // Status: 503 Service Temporarily Unavailable

			//TODO: чёрт, надо сделать будет через hidden_log.
			@file_put_contents($file = $_SERVER['DOCUMENT_ROOT']."/logs/load_trap.log", 
				date('Y.m.d H:i:s ')
					.$this->_url
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

		// Проверка на загрузку системы данным пользователем или ботом
		// Если делает много тяжёлых запросов - просим подождать.

		if(config('access_log') && $this->_remote_addr != '127.0.0.1')
		{
			$common_overload = config('overload_time', 0);
			$user_overload = config('user_overload_time', $common_overload);
			$bot_overload = config('bot_overload_time', $common_overload);

			if($user_overload || $bot_overload)
			{
				$dbh = new driver_mysql(config('main_bors_db'));
				$total = $dbh->select('bors_access_log', 'SUM(operation_time)', array(
					'user_ip' => $this->_remote_addr,
					'access_time>' => time() - 600,
				));

//				bors_debug::syslog('system_overload_test', $total, 0);

				if(!$is_bot && $user_overload && $total > $user_overload)
				{
					bors_debug::syslog('system_overload_users', $total.' of '.$user_overload, 0);

					$this->err(503, 600); // Status: 503 Service Temporarily Unavailable
					exit("Service Temporarily Unavailable");
				}

				if($is_bot && $bot_overload && $total > $bot_overload)
				{
					bors_debug::syslog('system_overload_bots', $total.' of '.$bot_overload, 0);

					$this->err(503, 600);
					exit("Service Temporarily Unavailable");
				}
			}
		}


		// Если кодировка вывода в браузер не та же, что внутренняя - то перекодируем
		// все входные данные во внутреннюю кодировку
		if(($ics = config('internal_charset')) != ($ocs = config('output_charset')))
			$_GET = array_iconv($ocs, $ics, $_GET);

		require_once('engines/bors/object_show.php');
		require_once('engines/bors/vhosts_loader.php');

		$uri = "http://{$this->_host}{$this->_url}";
		$this->header("X-request-url: $uri");
		if($this->_query == 'del')
		{
			$this->_query = 'act=del';
			$_GET['act'] = 'del';
		}

		if($this->_query)
			$uri .= '?'.$this->_query;

		$GLOBALS['bors_full_request_url'] = $uri;

		/**********************************************************************************************************/
		// Собственно, самое главное. Грузим объект и показываем его.
		$res = false;
		try
		{
			if($object = bors_load_uri($uri))
			{
				// Новый метод вывода, полностью на самом объекте
				if(method_exists($object, 'show'))
					$res = $object->show();

				if(!$res)	// Если новый метод не обработан, то выводим как раньше.
					$res = bors_object_show($object);
			}
		}
		catch(Exception $e)
		{
			$trace = debug_trace(0, false, -1, $e->getTrace());
			$message = $e->getMessage();
			bors_debug::syslog('exception', "$message\n\n$trace", true, array('dont_show_user' => true));
			try
			{
				bors_message(ec("При попытке просмотра этой страницы возникла ошибка:\n")
					."<div class=\"red_box\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
					."<!--\n\n$trace\n\n-->", array(
//						'template' => 'xfile:default/popup.html',
				));
			}
			catch(Exception $e2)
			{
				bors()->set_main_object(NULL);
				bors_message(ec("При попытке просмотра этой страницы возникли ошибки:\n")
					."<div class=\"red_box\">$message</div>\n"
					.ec("Администраторы будут извещены об этой проблеме и постараются её устранить. Извините за неудобство.\n")
					."<!--\n\n$trace\n\n-->", array(
					'template' => 'xfile:default/popup.html',
				));
			}

			$res = true;
		}

		/**********************************************************************************************************/

		// Записываем, если нужно, кто получал объект и сколько ушло времени на его получение.
		if(config('access_log'))
		{
			$data = array(
				'user_ip' => $this->_remote_addr,
				'user_id' => bors()->user_id(),
				'server_uri' => $uri,
				'referer' => @$_SERVER['HTTP_REFERER'],
				'access_time' => round($GLOBALS['stat']['start_microtime']/1000000),
				'operation_time' =>  str_replace(',', '.', microtime(true) - $GLOBALS['stat']['start_microtime']),
				'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
				'is_bot' => $is_bot,
			);

			if(empty($object))
			{
				$data['object_class_name'] = $this->_url;
			}
			else
			{
				$data['object_class_name'] = $object->class_name();
				$data['object_id'] = $object->id();
				$data['has_bors'] = 1;
				$data['has_bors_url'] = 1;
				$data['access_url'] = $object->url();
			}

			bors_new('bors_access_log', $data);
		}

		try
		{
			bors()->changed_save();
		}
		catch(Exception $e)
		{
			$error = bors_lib_exception::catch_html_code($e, ec("<div class=\"red_box\">Ошибка сохранения</div>"));
		}

		// Общее время работы
		$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

		// Если время работы превышает заданный лимит, то логгируем
		if($time > config('timing_limit'))
		{
			@file_put_contents($file = config('timing_log'), $time . " [".$uri . "]: " . @$_SERVER['HTTP_REFERER'] . "; IP=".@$this->_remote_addr."; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
			@chmod($file, 0666);
		}

		// Если показываем отладочную инфу, то описываем её в конец выводимой страницы комментарием.
		if(config('debug_timing') && is_string($res))
		{
			$deb = "<!--\n=== debug-info ===\n"
			."created = ".date('r')."\n";

			if($object)
			{
				foreach(explode(' ', 'class_name class_file template body_template') as $var)
					if($val = $object->get($var))
						$deb .= "$var = $val\n";

				if($cs = $object->cache_static())
					$deb .= "cache static expire = ". date('r', time()+$cs)."\n";
			}

			$deb .= "smarty = ".(config('smarty3_enable') ? 3 : 2)."\n";

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
				bors_debug::syslog('debug_timing', $deb, false);

			$res = str_ireplace('</body>', $deb.'</body>', $res);
		}

		if(!$this->_status)
			$this->err(200);

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
				. " ; IP=".@$this->_remote_addr
				. "; UA=".@$_SERVER['HTTP_USER_AGENT']."\n", FILE_APPEND);
			@chmod($file, 0666);
		}

		$this->err(404);

		if(config('404_page_url'))
			return go(config('404_page_url'), true);

		if(config('404_show', true))
			echo ec("Page '$uri' not found");

	}
}
