<?php

class blib_parse
{
	static function bytes($val)
	{
		$val = trim($val);

		if(empty($val))
			return 0;

		if(preg_match('/^(\d+)\s*(k|m|g|t)b?$/i', $val, $m))
		{
			$val = $m[1];
			$unit = $m[2];
		}
		else
			$unit = NULL;

		switch (strtolower($unit))
		{
			case 't':
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int) $val;
	}
}
