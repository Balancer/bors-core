<?php

//bors_function_include('debug/trace');
//echo bors_debug::trace();

// «Пустой» логгер-заглушка, ничего не делает.

class bors_log_stub extends bors_object_simple
{
	static function error() { }
	static function warning() { }
	static function notice() { }
	static function info() { }
	static function debug() { }

	function object() { return $this->id(); }

	function hidden($type, $message)
	{
		bors_debug::syslog($type, $this->object()->debug_title().": " . $message);
	}
}
