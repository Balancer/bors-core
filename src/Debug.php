<?php

namespace B2;

\bors_funcs::noop();

class Debug extends \bors_debug
{
	static function set_log_dir($dir)
	{
		config_set('debug_hidden_log_dir', $dir);
	}
}
