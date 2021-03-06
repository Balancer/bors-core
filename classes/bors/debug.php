<?php

class bors_debug
{
	static function syslog($type, $message = NULL, $trace = true, $args = array())
	{
		static $out_idx = 0;

		if(!($out_dir = \B2\Cfg::get('debug_hidden_log_dir')))
			return;

		bors_debug::timing_start('hidden_log');

		if(!$message)
		{
			$message = $type;
			$type = 'info/common';
		}

		if(preg_match('/error/', $type))
			bors::log()->error($message, $type, $trace, $args);

		if(preg_match('/^(error|warning|notice|info|debug)-(.+)$/', $type, $m))
		{
			$type = $m[1].'s/'.date('Ymd-His-'.sprintf('%03d', $out_idx++)).'-'.$m[2];
		}

		if($trace && empty($args['dont_show_user']) && class_exists('bors_class_loader', false) && function_exists('bors'))
			$user = bors()->user();
		else
			$user = NULL;

		if(popval($args, 'notime'))
			$out = '';
		else
			$out = strftime('%Y-%m-%d %H:%M:%S: ');

		$out .= $message . "\n";

		if($trace !== false)
		{
			require_once(BORS_CORE.'/inc/locales.php');

			if($trace === true)
				$trace_out = bors_debug::trace(0, false);
			elseif($trace >= 1)
				$trace_out = bors_debug::trace(0, false, $trace);
			else
				$trace_out = '';

			if(!empty($_GET))
				$data = "_GET=".print_r($_GET, true)."\n";
			else
				$data = "";

			if(!empty($_POST))
				$data .= "_POST=".print_r($_POST, true)."\n";

			$out .= "\tmain_url: ".@$GLOBALS['main_uri']."\n";

			foreach(['HTTP_HOST', 'REQUEST_URI', 'QUERY_STRING', 'HTTP_REFERER', 'REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_ACCEPT', 'REQUEST_METHOD'] as $name)
				if(!empty($_SERVER[$name]))
					$out .= "\t{$name}: ".$_SERVER[$name]."\n";

			if(!empty($GLOBALS['stat']['start_microtime']))
				$out .= "\twork time: ".(microtime(true) - $GLOBALS['stat']['start_microtime'])." us\n";

			$out .= (@$user ? "\tuser: ".dc($user->title()) . ' [' .bors()->user_id()."]\n": '')
				. $data
				. $trace_out
				. "\n-------------------------------------------------------------------\n\n";
		}

		if(!empty($args['append']))
			$out .= "\n".$args['append'];

		$file = "{$out_dir}/{$type}.log";

		if(!is_dir($dir = dirname($file)))
		{
			mkpath($dir);
			@chmod($dir, 0777);
		}

		@file_put_contents($file, $out, FILE_APPEND);
		@chmod($file, 0666);

		bors_debug::timing_stop('hidden_log');
	}

	static function exception_log($log, $message, Exception $e)
	{
		bors_debug::syslog($log, $message.':'.$e->getMessage()
			."\n----------------------\nTrace:\n".bors_lib_exception::catch_trace($e)
			."\n----------------------\n");
	}

	static function sepalog($type, $message = NULL, $params = array())
	{
		$dir = \B2\Cfg::get('debug_hidden_log_dir').'/errors';
		if(!is_dir($dir))
		{
			@mkdir($dir);
			@chmod($dir, 0777);
		}

		if(!file_exists($dir))
			return;

		$trace = defval($params, 'trace');

		$args['append'] = "stack:\n==============\n".bors_debug::trace(0, false);

		bors_debug::syslog('errors/'.date('c').'-'.$type, $message."\n\ntrace=$trace", -1, $args);
	}

	static function log($category, $message = NULL, $level = 'info', $trace = true)
	{
		static $enter = false;
		if($enter)
			return;

		$enter = true;

		bors_new('bors_debug_log', array(
			'create_time' => time(),
			'title' => $message,
			'category' => $category,
			'level' => $level,
			'trace' => serialize(array_slice(debug_backtrace(), 0, 100)),
			'owner_id' => bors()->user_id(),
			'request_uri' => bors()->request()->url(),
			'get_vars' => json_encode(@$_GET),
			'referer' => bors()->request()->referer(),
			'remote_addr' => @$_SERVER['REMOTE_ADDR'],
			'server_data' => strlen(serialize($_SERVER)),
		));

		$enter = false;
	}

	static function timing_start($section)
	{
		global $bors_debug_timing;
		if(empty($bors_debug_timing[$section]))
			$bors_debug_timing[$section] = array('start' => NULL, 'calls'=>0, 'total'=>0, 'mem_total' => 0);

		$current = &$bors_debug_timing[$section];

		if($current['start'])
		{
			//TODO: need best method
//			bors_debug::syslog('__debug_error', ec("Вторичный вызов незавершённой функции debug_timing_start('$section')."));
			return;
		}

		$current['start'] = microtime(true);
		$current['mem'] = memory_get_usage();
	}

	static function timing_stop($section)
	{
		global $bors_debug_timing;
		$current = &$bors_debug_timing[$section];

		if(empty($current['start']))
		{
//			bors_debug::syslog('__debug_error', ec("Вызов неактивированной функции bors_debug::timing_stop('$section')."));
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

	static function trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL)
	{
		$MAXSTRLEN = 1000;

		if(is_null($html))
			$html = 0 && !bors_debug::in_console();

		if($html)
			$s = '<pre align="left">';
		else
			$s = '';

		if(is_null($traceArr))
			$traceArr = debug_backtrace();

		for($i = 1; $i <= $skip; $i++)
			array_shift($traceArr);

		if(is_numeric($level) && $level > 0)
			$traceArr = array_slice($traceArr, 0, $level);
//		if(is_numeric($level) && $level < 0)
//			$traceArr = array_slice($traceArr, -$level);

		$tabs = 0; //sizeof($traceArr)-1;
		for($pos=0, $stop=sizeof($traceArr); $pos<$stop; $pos++)
		{
			$arr = $traceArr[$stop-$pos-1];
			$indent = '';
			for ($i=0; $i < $tabs; $i++)
				$indent .= $html ? '&nbsp;' : ' ';

			$Line = (isset($arr['line'])? $arr['line'] : "unknown");
			$File = (isset($arr['file'])? $arr['file'] : "unknown");

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

			$targs = implode(', ',$args);
			if($html)
			{
				$targs = preg_replace('/(".+?")/', '<font color="green">$1</font>', $targs);
				$targs = preg_replace('/(true|false)/', '<font color="brown">$1</font>', $targs);
				$s .= '<font color="#999">(</font>'.$targs.'<font color="#999">)</font>';
			}
			else
				$s .= '('.$targs.')';

			if($html)
				$s .= '</span>';
			$s .= "\n";

			if($html)
				$s .= "$indent<span style=\"font-size:8pt;margin:0;padding:0;color:#ccc\">{$File}:{$Line}</span>";
			else
				$s .= "[{$File}:{$Line}]";
		}

		if($html)
			$s .= '</pre>';

		return $s;
	}

	static function execute_trace($message)
	{
		if(!\B2\Cfg::get('debug.execute_trace'))
			return;

		static $timestamp;
		static $mem;
		$now = microtime(true);

		$time = sprintf("%2.3f",  $now - $GLOBALS['stat']['start_microtime']);
		if($timestamp)
		{
			$delta = sprintf("%1.3f", $now - $timestamp);
			$delta_mem = memory_get_usage() - $mem;
			$mem = memory_get_usage();
		}
		else
		{
			bors_debug::syslog('execute_trace', "--------------------------------------------------", false);
			$delta = sprintf("%1.3f", $now - $GLOBALS['stat']['start_microtime']);
			$mem = memory_get_usage();
			$delta_mem = $mem;
		}

		bors_debug::syslog('execute_trace', "+$delta = $time ["
			.($delta_mem > 0 ? '+' : '')
			.sprintf('%1.2f', $delta_mem/1048576).'Mb = '
			.sprintf('%1.2f', $mem/1048576)."Mb]: $message", false);
		$timestamp = $now;
	}

	static function warning($message)
	{

	}

	//TODO: убрать аналог из bors_global
	static function memory_usage() { return round(memory_get_usage()/1048576)."/".round(memory_get_peak_usage()/1048576)."MB"; }

	static function memory_usage_ping()
	{
		static $prev_usage = 0, $prev_peak_usage = 0;
		static $mb = 1048576;
		$cur_usage = memory_get_usage();
		$cur_peak_usage = memory_get_peak_usage();

		$usage_delta = round(($cur_usage - $prev_usage) / $mb, 2);
		if($usage_delta > 0)
			$usage_delta = "+$usage_delta";

		$peak_usage_delta = round(($cur_peak_usage - $prev_peak_usage) / $mb, 2);
		if($peak_usage_delta > 0)
			$peak_usage_delta = "+$peak_usage_delta";

		$report = round($cur_usage/$mb, 2)."({$usage_delta})/".round($cur_peak_usage/$mb, 2)."({$peak_usage_delta}) MB";

		$prev_usage = $cur_usage;
		$prev_peak_usage = $cur_peak_usage;

		return $report;
	}

	// Если показываем отладочную инфу, то описываем её в конец выводимой страницы комментарием.
	static function append_info(&$res, $app=NULL)
	{
		if(!\B2\Cfg::get('debug.timing') || !is_string($res) || !preg_match('!</body>!i', $res))
			return;

		$deb = "<!--\n=== debug-info ===\n"
			."BORS_CORE = ".BORS_CORE."\n"
			."log dir = ".\B2\Cfg::get('debug_hidden_log_dir')."\n"
			."log created = ".date('r')."\n";

		if($app)
			$object = $app;
		else
			$object = bors()->main_object();

		if($object)
		{
			foreach(explode(' ', 'class_name class_file template body_template') as $var)
				if($val = @$object->get($var))
					$deb .= "$var = $val\n";

			if($cs = $object->cache_static())
				$deb .= "cache static expire = ". date('r', time()+$cs)."\n";
		}

		if(\B2\Cfg::get('is_developer'))
		{
			$deb .= "\n=== config ===\n"
				. "cache_database = ".\B2\Cfg::get('cache_database')."\n";
		}

		require_once BORS_CORE.'/inc/functions/debug/vars_info.php';
		require_once BORS_CORE.'/inc/functions/debug/count.php';
		require_once BORS_CORE.'/inc/functions/debug/count_info_all.php';
		require_once BORS_CORE.'/inc/functions/debug/timing_info_all.php';

		if($deb_vars = debug_vars_info())
		{
			$deb .= "\n=== debug vars: ===\n";
			$deb .= $deb_vars;
		}

		$deb .= "\n=== debug counting: ===\n";
		$deb .= debug_count_info_all();

		// Общее время работы
		$time = microtime(true) - $GLOBALS['stat']['start_microtime'];

		$deb .= "\n=== debug timing: ===\n";
		$deb .= debug_timing_info_all();
		$deb .= "Total time: $time sec.\n";
		$deb .= "-->\n";

		if(\B2\Cfg::get('is_developer'))
			bors_debug::syslog('debug/timing', $deb, false);

		$res = str_ireplace('</body>', $deb.'</body>', $res);
	}

	static function exec_time()
	{
		return microtime(true) - $GLOBALS['stat']['start_microtime'];
	}

	static function count_inc($category, $inc = 1)
	{
		if(empty($GLOBALS['bors_debug_counts']))
			$GLOBALS['bors_debug_counts'] = [];

		if(empty($GLOBALS['bors_debug_counts'][$category]))
			$GLOBALS['bors_debug_counts'][$category] = 0;

		$GLOBALS['bors_debug_counts'][$category] += $inc;
	}
}
