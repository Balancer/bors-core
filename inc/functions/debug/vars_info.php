<?php

bors_function_include('debug/log_var');

function debug_vars_info()
{
	global $bors_debug_log_vars;
	$result = "";
	if(!empty($bors_debug_log_vars))
	{
		ksort($bors_debug_log_vars);
		foreach($bors_debug_log_vars as $var => $value)
		{
			if(is_int($value))
				$value = "$value [int]";
			elseif(is_string($value))
				$value = "'$value' [string]";
			else
				$value = "($value) [unknown]";
			$result .= "{$var} = {$value}\n";
		}
	}

	$result .= 'user='.@$_SERVER['USER']."\n";
	if(function_exists('gethostname'))
		$result .= 'host='.gethostname()."\n";

	return $result;
}
