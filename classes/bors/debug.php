<?php

class bors_debug
{
	static function syslog($type, $message, $trace = true, $args = array())
	{
		bors_function_include('debug/hidden_log');

		if(preg_match('/error/', $type))
			bors::log()->error($message, $type, $trace, $args);

		return debug_hidden_log($type, $message, $trace, $args);
	}

	static function sepalog($type, $message = NULL, $params = array())
	{
		$dir = config('debug_hidden_log_dir').'/errors';
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
//			debug_hidden_log('__debug_error', ec("Вторичный вызов незавершённой функции debug_timing_start('$section')."));
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
//			debug_hidden_log('__debug_error', ec("Вызов неактивированной функции debug_timing_stop('$section')."));
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
		if(!config('debug.execute_trace'))
			return;

		static $timestamp;
		static $mem;
		$now = microtime(true);

		bors_function_include('debug/hidden_log');
		$time = sprintf("%2.3f",  $now - $GLOBALS['stat']['start_microtime']);
		if($timestamp)
		{
			$delta = sprintf("%1.3f", $now - $timestamp);
			$delta_mem = memory_get_usage() - $mem;
			$mem = memory_get_usage();
		}
		else
		{
			debug_hidden_log('execute_trace', "--------------------------------------------------", false);
			$delta = sprintf("%1.3f", $now - $GLOBALS['stat']['start_microtime']);
			$mem = memory_get_usage();
			$delta_mem = $mem;
		}

		debug_hidden_log('execute_trace', "+$delta = $time ["
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
}
