<?php

require_once('inc/texts.php');

function debug_exit($message)
{
	if($tmp = @ob_get_contents())
	{
		ob_end_clean();
		echo bors_close_tags($tmp);
	}

	echo debug_trace();
	debug_hidden_log('debug_exit', $message);
	exit($message);
}

function debug_in_console() { return empty($_SERVER['HTTP_HOST']); }

function debug_xmp($text, $string = false)
{
	if(!empty($_SERVER['HTTP_HOST']))
	$out = "<xmp>{$text}</xmp>\n";
	else
	$out = $text;

	if(!$string)
	echo $out;

	return $out;
}

function debug_pre($text)
{
	if(!empty($_SERVER['HTTP_HOST']))
	echo "<xmp>{$text}</xmp>\n";
	else
	echo $text;
}

function print_d($data, $string=false) { return debug_xmp(print_r($data, true), $string); }

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
	debug_trace();
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
				if(!empty($_SERVER['HTTP_HOST']))
				echo '<span style="color: red;">';
				else
				echo "=== ";
			}

			if(!empty($_SERVER['HTTP_HOST']))
			echo "<span style=\"font-size: 8pt;\">".substr($message,0,2048).(strlen($message)>2048?"...":"")."</span><br />\n";
			else
			echo substr($message,0,2048).(strlen($message)>2048?"...":"")."\n";

			if($level<3)
			{
				if(!empty($_SERVER['HTTP_HOST']))
				echo "</span>\n";
				else
				echo " ===\n";
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

function debug_trace($skip = 0, $html = NULL)
{
	$MAXSTRLEN = 128;

	if(is_null($html))
		$html = !empty($_SERVER['HTTP_HOST']);

	if($html)
		$s = '<pre align="left">';
	else
		$s = '';

	$traceArr = debug_backtrace();

	for($i = 0; $i <= $skip; $i++)
		array_shift($traceArr);

	$tabs = 0; //sizeof($traceArr)-1;
	for($pos=0, $stop=sizeof($traceArr); $pos<$stop; $pos++)
	{
		$arr = $traceArr[$stop-$pos-1];
		$indent = '';
		for ($i=0; $i < $tabs; $i++)
			$indent .= $html ? '&nbsp;' : ' ';

		$Line = (isset($arr['line'])? $arr['line'] : "unknown");
		$File = (isset($arr['file'])? $arr['file'] : "unknown");
		if($html)
			$s .= "$indent<span style=\"font-size:8pt;margin:0;padding:0;color:#999\">{$File}:{$Line}</span>";
		else
			$s .= "[{$File}:{$Line}]";

		$s .= "\n$indent";

		$tabs++;
		if($html)
			$s .= '<span style="font-family:monospace;size:9pt;padding:0;margin:0">';
		if(isset($arr['class']))
			$s .= $arr['class'].'.';
		$args = array();
		if(!empty($arr['args']))
		{
			foreach($arr['args'] as $v)
			{
				if (is_null($v)) $args[] = 'null';
				else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
				else if (is_object($v)) $args[] = 'Object:'.get_class($v);
				else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
				else
				{
					$v = (string) @$v;
					$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
					if (strlen($v) > $MAXSTRLEN) $str .= '...';
						$args[] = "\"".$str."\"";
				}
			}
		}
		if($html)
			$s .= "<b>{$arr['function']}</b>";
		else
			$s .= $arr['function'];

		$s .= '('.implode(', ',$args).')';

		if($html)
			$s .= '</span>';
		$s .= "\n";
	}

	if($html)
		$s .= '</pre>';

	return $s;
}

function debug_page_stat()
{
	?>
<noindex>
Новых mysql-соединений: <?echo $GLOBALS['global_db_new_connections'];?><br />
Продолженных mysql-соединений: <?echo $GLOBALS['global_db_resume_connections'];?><br />
Всего запросов <?echo debug_count('mysql_queries');?><br />
Попадений в кеш данных: <?echo $GLOBALS['global_key_count_hit'];?><br />
Промахов в кеш данных: <?echo $GLOBALS['global_key_count_miss'];?><br />
Время генерации страницы: <?echo microtime(true) - $GLOBALS['stat']['start_microtime'];?>сек.<br />
<?
if($GLOBALS['cms']['cache_copy'])
	echo "Кешированная версия от ".strftime("%Y-%d-%m %H:%I", $GLOBALS['cms']['cache_copy']);
else
	echo "Перекомпилированная версия";
?><br />
</noindex>
<?
}


$GLOBALS['bors_debug_counts'] = array();
function debug_count_inc($category, $inc = 1) { @$GLOBALS['bors_debug_counts'][$category] += $inc; }
function debug_count($category) { return @$GLOBALS['bors_debug_counts'][$category]; }

$GLOBALS['bors_debug_timing'] = array();
function debug_timing_start($category)
{
	global $bors_debug_timing;
	if(empty($bors_debug_timing[$category]))
	$bors_debug_timing[$category] = array('start' => NULL, 'calls'=>0, 'total'=>0);

	$current = &$bors_debug_timing[$category];

	if($current['start'])
	debug_exit(ec("Вторичный вызов незавершённой функции debug_timing_start('$category')."));

	list($usec, $sec) = explode(" ",microtime());
	$current['start'] = ((float)$usec + (float)$sec);
}

function debug_timing_stop($category)
{
	global $bors_debug_timing;
	$current = &$bors_debug_timing[$category];

	if(empty($current['start']))
	debug_exit(ec("Вызов неактивированной функции debug_timing_stop('$category')."));

	list($usec, $sec) = explode(" ",microtime());
	$time = ((float)$usec + (float)$sec) - $current['start'];

	$current['start'] = NULL;
	$current['calls']++;
	$current['total'] += $time;
}

function debug_timing_info_all()
{
	global $bors_debug_timing;
	$result = "";
	ksort($bors_debug_timing);
	foreach($bors_debug_timing as $section => $data)
		$result .= $section.": ".sprintf('%.4f', floatval(@$data['total'])).'sec ['.intval(@$data['calls'])." calls]\n";

	return $result;
}

function debug_count_info_all()
{
	global $bors_debug_counts;
	$result = "";
	ksort($bors_debug_counts);
	foreach($bors_debug_counts as $section => $count)
		$result .= $section.": {$count}\n";

	return $result;
}

function debug_hidden_log($type, $message=NULL, $trace = true)
{
	if(!$message)
	{
		$message = $type;
		$type = 'common';
	}

	if(!($out_dir = config('debug_hidden_log_dir')))
		return;

	$out = strftime('%Y-%m-%d %H:%M:%S: ') . $message . "\n";
	if($trace)
	$out .= "url: http://".@$_SERVER['HTTP_HOST'].@$_SERVER['REQUEST_URI']
	.(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '')."\n"
	. (!empty($_SERVER['HTTP_REFERER']) ? "referer: ".$_SERVER['HTTP_REFERER'] : "")."\n"
	. (!empty($_SERVER['REMOTE_ADDR']) ? "addr: ".$_SERVER['REMOTE_ADDR'] : "")."\n"
	. debug_trace(0, false)
	. "\n---------------------------\n\n";


	@file_put_contents("{$out_dir}/hidden-{$type}.log", $out, FILE_APPEND);
}
