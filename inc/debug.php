<?php

use B2\Cfg;

//if(!function_exists('bors_function_include')) var_dump(debug_backtrace());

require_once('inc/texts.php');

bors_function_include('debug/count');
bors_function_include('debug/count_inc');
bors_function_include('debug/count_info_all');
bors_function_include('debug/hidden_log');
bors_function_include('debug/in_console');
bors_function_include('debug/log_var');
bors_function_include('debug/print_dd');
bors_function_include('debug/timing_info_all');
bors_function_include('debug/timing_start');
bors_function_include('debug/timing_stop');
bors_function_include('debug/trace');
bors_function_include('debug/vars_info');
bors_function_include('debug/vars_info');

/**
 * Завершает работу, выполняя все необходимые операции.
 * Выводит сообщение $message.
 * @param string $message
 */
function debug_exit($message)
{
	$ob_status = ob_get_status();
	if(!empty($ob_status['type']) && ($tmp = @ob_get_contents()))
	{
		ob_end_clean();
		echo bors_close_tags($tmp);
	}

	echo bors_debug::trace();
	bors_debug::syslog('debug_exit', $message);

	if(bors()->main_object())
		$message .= "<br/>\nmain_object->class_file=".bors()->main_object()->class_file();

	if(Cfg::get('debug.timing'))
	{
		// Общее время работы
		$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

		$deb = "\n=== debug-info ===\n"
			."created = ".date('r')."\n";

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

		if(Cfg::get('is_developer'))
			bors_debug::syslog('debug_timing', $deb, false);

		echo str_replace("\n", "<br/>\n", $deb);
	}

	exit($message);
}


function set_loglevel($n, $file=false)
{
	Cfg::set('log_level', /*$_GET['log_level'] = */ $n);
	if($file === false)
	return;

	$GLOBALS['echofile'] = $file;
}

function loglevel($check) { return $check <= max(Cfg::get('log_level'), @$_GET['log_level']); }

function debug_only_one_time($mark, $trace=true, $times = 1)
{
	if(@$GLOBALS['debug']['onetime'][$mark] >= $times)
	debug_exit('Second call of '.$mark);

	@$GLOBALS['debug']['onetime'][$mark]++;

	if($trace)
		echo bors_debug::trace();
}

function echolog($message, $level=3)
{
	$log_level = max(Cfg::get('log_level'), @$_GET['log_level']);
	if(!$log_level)
		$log_level = 2;

	if(!$log_level)
		return;

	if($log_level >= $level)
	{
		if(!empty($GLOBALS['echofile']))
		{
			$fh=fopen($GLOBALS['echofile'],"at");
			//				$txt = "uri: http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}\n";
			//				$txt. = "query: ".@$_SERVER['QUERY_STRING']."\n";
			//				$txt .= "ref: ".@$_SERVER['HTTP_REFERER']."\n";
			$txt = "$level: $message\n";
			//				if($log_level > 10)
			//					$txt .= "backtrace:".print_r(debug_backtrace(),true)."\n";
			//				$txt .= "-------------------------------\n";
			fputs($fh, $txt);
			fclose($fh);
			@chmod($GLOBALS['echofile'], 0666);
		}
		else
		{
			if($level<3)
			{
				if(debug_in_console())
					echo "=== ";
				else
					echo '<span style="color: red;">';
			}

			if(debug_in_console())
				echo substr($message,0,2048).(strlen($message)>2048?"...":"")."\n";
			else
				echo "<span style=\"font-size: 8pt;\">".substr($message,0,2048).(strlen($message)>2048?"...":"")."</span><br />\n";

			if($level<3)
			{
				if(debug_in_console())
					echo " ===\n";
				else
					echo "</span>\n";
			}
		}
		if($level==1)
		{
			echo "Backtrace error:<br/ >\n";
			echo bors_debug::trace();
		}

		if(empty($GLOBALS['echofile']))
		echo "<hr />";
	}
}

function debug_page_stat()
{
	echo "<noindex>
Новых mysql-соединений: {$GLOBALS['global_db_new_connections']}<br />
Продолженных mysql-соединений: {$GLOBALS['global_db_resume_connections']}<br />
Всего запросов ".debug_count('mysql_queries')."<br />
Попадений в кеш данных: {$GLOBALS['global_key_count_hit']}<br />
Промахов в кеш данных: {$GLOBALS['global_key_count_miss']}<br />
Время генерации страницы: ".(microtime(true) - $GLOBALS['stat']['start_microtime'])." сек.<br />
";

	if($GLOBALS['cms']['cache_copy'])
		echo "Кешированная версия от ".strftime("%Y-%d-%m %H:%I", $GLOBALS['cms']['cache_copy']);
	else
		echo "Перекомпилированная версия";

	echo "<br />\n</noindex>\n";
}

function bors_system_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
	// Решение из http://anvilstudios.co.za/blog/php/how-to-ignore-errors-in-a-custom-php-error-handler/
	if(error_reporting() === 0) // continue script execution, skipping standard PHP error handler
		return false;

	// Примеры также в http://www.homefilm.info/php42/error-handling.html
	if(!($out_dir = Cfg::get('debug_hidden_log_dir')))
		return false;

	$dir = Cfg::get('debug_hidden_log_dir').'/errors';
	if(!is_dir($dir))
	{
		@mkdir($dir);
		@chmod($dir, 0777);
	}

	if(!file_exists($dir))
		return false;

//	echo "$errstr ".$errfile.':'.$errline;
	bors_debug::syslog('errors/'.date('c'), "bors_system_error_handler:
		errno=$errno
		errstr=$errstr
		errfile=$errfile
		errline=$errline", -1
			, array('append' => "stack:\n==============\n".debug_trace(0, false)."\nerrcontext=".print_r($errcontext, true))
		);

	return true;
}

//error_reporting(0);
set_error_handler('bors_system_error_handler', E_ALL & ~E_STRICT & ~E_NOTICE);
//set_error_handler('bors_system_error_handler', E_ERROR | E_WARNING | E_PARSE);
