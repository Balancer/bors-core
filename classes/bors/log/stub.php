<?php

//bors_function_include('debug/trace');
//echo debug_trace();

// «Пустой» логгер-заглушка, ничего не делает.

class bors_log_stub extends base_empty
{
	static function error() { }
	static function warning() { }
	static function notice() { }
	static function info() { }
	static function debug() { }

	function object() { return $this->id(); }

	function hidden($type, $message)
	{
		debug_hidden_log($type, $this->object()->debug_title().": " . $message);
	}
}
