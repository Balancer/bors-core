<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class bors_log_monolog
{
	var $log;

	function __construct()
	{
		$log = new Logger('BORS');
		$log->pushHandler(new StreamHandler('debug_hidden_log_dir'.DIRECTORY_SEPARATOR.'mono-warnings.log', Logger::WARNING));
		$log->pushHandler(new StreamHandler('debug_hidden_log_dir'.DIRECTORY_SEPARATOR.'mono-info.log', Logger::INFO));

		$log->pushProcessor(function ($record) {
			$record['extra']['user'] = object_property(bors()->user(), 'title');
			$record['extra']['user_id'] = object_property(bors()->user_id());
			$record['extra']['trace'] = debug_backtrace();

		    return $record;
		});

		$log->pushProcessor(new \Monolog\Processor\WebProcessor);
		$log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor);
		$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor);

		$this->log = $log;
	}

	static function instance()
	{
		static $instance = NULL;

		if(!$instance)
			$instance = new bors_log_monolog;

		return $instance;
	}

	function error($msg, $section, $extra = NULL) { $this->log($section)->addError($msg, $extra); }
	function warning($msg, $section, $extra = NULL) { $this->log($section)->addWarning($msg, $extra); }
	function notice($msg, $section, $extra = NULL) { $this->log($section)->addNotice($msg, $extra); }
	function info($msg, $section, $extra = NULL) { $this->log($section)->addInfo($msg, $extra); }
	function debug($msg, $section, $extra = NULL) { $this->log($section)->addDebug($msg, $extra); }
}
