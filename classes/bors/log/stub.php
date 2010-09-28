<?php

// «Пустой» логгер-заглушка, ничего не делает.

class bors_log_stub
{
	static function error() { }
	static function warning() { }
	static function notice() { }
	static function info() { }
	static function debug() { }
}
