<?php

bors_function_include('debug/common');

function print_d($data, $string=false) { return debug_xmp(print_r($data, true), $string); }
function print_dd($data, $string=false){ return debug_xmp(__print_dd($data), $string); }
function print_dl($data) { return str_replace("\n", " ", print_r($data, true)); }

function __print_dd($data, $level=0)
{
	$s = '';
	$step = str_repeat(' ', $level*4);
	if(is_object($data))
		$s .= $step.$data->debug_title()."\n";
	elseif(is_array($data))
	{
		$s .= "{$step}array(\n";
		foreach($data as $key => $value)
			$s .= $step."    '{$key}' => " . __print_dd($value, $level+1) . "\n";
		$s .= "{$step});\n";
	}
	else
		$s .= $step.$data."\n";

	return trim($s);
}
