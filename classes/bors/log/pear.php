<?php

// Обёртка для унифицированного использования PEAR::Log(http://pear.php.net/package/Log/)

require_once('Log.php');

class bors_log_pear
{
	static function info($message, $type = 'COMMON')
	{
		Log::singleton(config('pear.log.handler', 'console'), '', $type)->log($message, PEAR_LOG_INFO);
	}
}
