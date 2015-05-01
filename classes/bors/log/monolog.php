<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class bors_log_monolog
{
	// (600): Emergency: system is unusable
	function emergency($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::EMERGENCY, $trace)->addEmergency($msg, $extra); }

	// (550): Action must be taken immediately. Example: Entire website
	//        down, database unavailable, etc. This should trigger the
	//        SMS alerts and wake you up.
	function alert($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::ALERT, $trace)->addAlert($msg, $extra); }

	// (500): Critical conditions. Example: Application component
	//        unavailable, unexpected exception.
	function critical($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::CRITICAL, $trace)->addCritical($msg, $extra); }

	// (400): Runtime errors that do not require immediate action but
	//        should typically be logged and monitored.
	function error($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::ERROR, $trace)->addError($msg, $extra); }

	// (300): Exceptional occurrences that are not errors.
	//        Examples: Use of deprecated APIs, poor use of an API,
	//        undesirable things that are not necessarily wrong
	function warning($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::WARNING, $trace)->addWarning($msg, $extra); }

	// (250): Normal but significant events.
	function notice($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::NOTICE, $trace)->addNotice($msg, $extra); }

	// (200): Interesting events. Examples: User logs in, SQL logs.
	function info($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::INFO, $trace)->addInfo($msg, $extra); }

	// (100): Detailed debug information.
	function debug($msg, $section = 'bors', $trace = false, $extra = array()) { $this->logger($section, Logger::DEBUG, $trace)->addDebug($msg, $extra); }

	static $loggers = array();

	function logger($name, $level, $trace = false)
	{
		$trace = (bool) $trace;
		if(empty($this->loggers[$name][$trace]))
		{
			$log = new Logger($name);

			switch($level)
			{
				case Logger::EMERGENCY:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-emergency.log', $level));
//					$log->pushHandler(new HipChatHandler($token, $room, $name = 'Monolog', $notify = false, Logger::ERROR));
					break;
				case Logger::ALERT:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-alert.log', $level));
//					$log->pushHandler(new HipChatHandler($token, $room, $name = 'Monolog', $notify = false, Logger::ERROR));
					break;
				case Logger::CRITICAL:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-critical.log', $level));
//					$log->pushHandler(new HipChatHandler($token, $room, $name = 'Monolog', $notify = false, Logger::ERROR));
					break;
				case Logger::ERROR:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-error.log', $level));
//					$log->pushHandler(new HipChatHandler($token, $room, $name = 'Monolog', $notify = false, Logger::ERROR));
					break;
				case Logger::WARNING:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-warning.log', $level));
					break;
				case Logger::NOTICE:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-notice.log', $level));
					break;
				case Logger::INFO:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-info.log', $level));
					break;
				case Logger::DEBUG:
				default:
					$log->pushHandler(new StreamHandler(config('debug_hidden_log_dir').DIRECTORY_SEPARATOR.'monolog-debug.log', $level));
					break;
			}

			$log->pushProcessor(function ($record) use($trace) {

				if(bors()->user())
					$record['extra']['user'] = bors()->user()->get('title');
				if(bors()->user_id())
					$record['extra']['user_id'] = bors()->user_id();

				if($trace)
				{
					$record['extra']['trace'] = debug_backtrace();
					$record['extra']['SERVER'] = @$_SERVER;
					$record['extra']['GET'] = @$_GET;
					$record['extra']['POST'] = @$_POST;
				}

			    return $record;
			});

			if($trace)
			{
				$log->pushProcessor(new \Monolog\Processor\WebProcessor);
				$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor);
				$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor);
			}

			$this->loggers[$name][$trace] = $log;
		}

		return $this->loggers[$name][$trace];
	}

	static function instance()
	{
		static $instance = NULL;

		if(!$instance)
			$instance = new bors_log_monolog;

		return $instance;
	}

}
