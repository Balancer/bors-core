<?php

require_once('inc/texts.php');

function debug_exit($message)
{
	$ob_status = ob_get_status();
	if(!empty($ob_status['type']) && ($tmp = @ob_get_contents()))
	{
		ob_end_clean();
		echo bors_close_tags($tmp);
	}

	echo debug_trace();
	debug_hidden_log('debug_exit', $message);

	if(bors()->main_object())
		$message .= "<br/>\nmain_object->class_file=".bors()->main_object()->class_file();

	exit($message);
}

bors_function_include('debug/in_console');

function debug_xmp($text, $string = false)
{
	if(debug_in_console())
		$out = $text;
	else
		$out = "<xmp>{$text}</xmp>\n";

	if(!$string)
		echo $out;

	return $out;
}

function debug_pre($text)
{
	if(debug_in_console())
		echo $text;
	else
		echo "<xmp>{$text}</xmp>\n";
}

function print_d($data, $string=false) { return debug_xmp(print_r($data, true), $string); }
function print_dd($data, $string=false){ return debug_xmp(__print_dd($data), $string); }
function print_dl($data) { return str_replace("\n", " ", print_r($data, true)); }

function __print_dd($data, $level=0)
{
	$s = '';
	$step = str_repeat(' ', $level*4);
	if(is_object($data))
		$s .= $step.$data->debug_title()."\n";
	elseif(is_array($data))
	{
		$s .= "{$step}array(\n";
		foreach($data as $key => $value)
			$s .= $step."    '{$key}' => " . __print_dd($value, $level+1) . "\n";
		$s .= "{$step});\n";
	}
	else
		$s .= $step.$data."\n";

	return trim($s);
}

function set_loglevel($n, $file=false)
{
	config_set('log_level', /*$_GET['log_level'] = */ $n);
	if($file === false)
	return;

	$GLOBALS['echofile'] = $file;
}

function loglevel($check) { return $check <= max(config('log_level'), @$_GET['log_level']); }

function debug_only_one_time($mark, $trace=true, $times = 1)
{
	if(@$GLOBALS['debug']['onetime'][$mark] >= $times)
	debug_exit('Second call of '.$mark);

	@$GLOBALS['debug']['onetime'][$mark]++;

	if($trace)
		echo debug_trace();
}

function echolog($message, $level=3)
{
	$log_level = max(config('log_level'), @$_GET['log_level']);
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
			echo debug_trace();
		}

		if(empty($GLOBALS['echofile']))
		echo "<hr />";
	}
}

function debug($message,$comment='',$level=3)
{
	//        return;
	$trace = debug_backtrace();
	$caller = $trace[0];
	$file = $caller['file'];
	$line = $caller['line'];

	$fh=@fopen($GLOBALS['cms']['base_dir'].'/logs/debug.log','at');
	@fwrite($fh,strftime("***	%Y-%m-%d %H:%M:%S			").($comment?"$comment:\n":"{$file}[$line]\n")."$message\n----------------------\n");
	@fclose($fh);
}

bors_function_include('debug/trace');

function debug_page_stat()
{
	?>
<noindex>
Новых mysql-соединений: <?php echo $GLOBALS['global_db_new_connections'];?><br />
Продолженных mysql-соединений: <?php echo $GLOBALS['global_db_resume_connections'];?><br />
Всего запросов <?php echo debug_count('mysql_queries');?><br />
Попадений в кеш данных: <?php echo $GLOBALS['global_key_count_hit'];?><br />
Промахов в кеш данных: <?php echo $GLOBALS['global_key_count_miss'];?><br />
Время генерации страницы: <?php echo microtime(true) - $GLOBALS['stat']['start_microtime'];?>сек.<br />
<?php
if($GLOBALS['cms']['cache_copy'])
	echo "Кешированная версия от ".strftime("%Y-%d-%m %H:%I", $GLOBALS['cms']['cache_copy']);
else
	echo "Перекомпилированная версия";
?><br />
</noindex>
<?php
}


$GLOBALS['bors_debug_counts'] = array();
function debug_count_inc($category, $inc = 1) { @$GLOBALS['bors_debug_counts'][$category] += $inc; }
function debug_count($category) { return @$GLOBALS['bors_debug_counts'][$category]; }

$GLOBALS['bors_debug_timing'] = array();
function debug_timing_start($category)
{
	global $bors_debug_timing;
	if(empty($bors_debug_timing[$category]))
		$bors_debug_timing[$category] = array('start' => NULL, 'calls'=>0, 'total'=>0, 'mem_total' => 0);

	$current = &$bors_debug_timing[$category];

	if($current['start'])
	{
		//TODO: need best method
//		debug_hidden_log('__debug_error', ec("Вторичный вызов незавершённой функции debug_timing_start('$category')."));
		return;
	}

	$current['start'] = microtime(true);
	$current['mem'] = memory_get_usage();
}

function debug_timing_stop($category)
{
	global $bors_debug_timing;
	$current = &$bors_debug_timing[$category];

	if(empty($current['start']))
	{
//		debug_hidden_log('__debug_error', ec("Вызов неактивированной функции debug_timing_stop('$category')."));
		return;
	}

	$mem = memory_get_usage() - $current['mem'];
	$time = microtime(true) - $current['start'];

	$current['start'] = NULL;
	$current['mem'] = NULL;
	$current['calls']++;
	$current['total'] += $time;
	$current['mem_total'] += $mem;
}

function debug_timing_info_all()
{
	$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

	global $bors_debug_timing;
	$result = "";
	if($bors_debug_timing)
	{
		ksort($bors_debug_timing);
		foreach($bors_debug_timing as $section => $data)
			$result .= $section.": ".sprintf('%.4f', floatval(@$data['total'])).'sec ['.intval(@$data['calls'])." calls, ".sprintf('%.2f', $data['total']/$time * 100)."%, {$data['mem_total']}]\n";
	}

	return $result;
}

function debug_log_var($var, $value) { return $GLOBALS['bors_debug_log_vars'][$var] = $value; }
function debug_vars_info()
{
	global $bors_debug_log_vars;
	$result = "";
	if(!empty($bors_debug_log_vars))
	{
		ksort($bors_debug_log_vars);
		foreach($bors_debug_log_vars as $var => $value)
		{
			if(is_int($value))
				$value = "$value [int]";
			elseif(is_string($value))
				$value = "'$value' [string]";
			else
				$value = "($value) [unknown]";
			$result .= "{$var} = {$value}\n";
		}
	}

	$result .= 'user='.@$_SERVER['USER']."\n";

	return $result;
}

function debug_count_info_all()
{
	$result = "";

	global $bors_debug_counts;
	if($bors_debug_counts)
	{
		ksort($bors_debug_counts);
		foreach($bors_debug_counts as $section => $count)
			$result .= $section.": {$count}\n";
	}

	return $result;
}

bors_function_include('debug/hidden_log');

function bors_system_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
	// Решение из http://anvilstudios.co.za/blog/php/how-to-ignore-errors-in-a-custom-php-error-handler/
	if(error_reporting() === 0) // continue script execution, skipping standard PHP error handler
		return false;

	// Примеры также в http://www.homefilm.info/php42/error-handling.html

	if(!($out_dir = config('debug_hidden_log_dir')))
		return false;

	@mkdir($dir = config('debug_hidden_log_dir').'/errors');
	@chmod($dir, 0777);
	if(!file_exists($dir))
		return false;

//	echo $errfile.':'.$errline;
	debug_hidden_log('errors/'.date('c'), "Handled error:\n\t\terrno=$errno\n\t\terrstr=$errstr\n\t\terrfile=$errfile\n\t\terrline=$errline", -1, array('append' => "errcontext=".print_r($errcontext, true)));

	return true;
}

//error_reporting(0);
set_error_handler('bors_system_error_handler', E_ALL & ~E_STRICT & ~E_NOTICE);
//set_error_handler('bors_system_error_handler', E_ERROR | E_WARNING | E_PARSE);
//$x = 5/0;

