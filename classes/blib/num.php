<?php

// Работа с числами

class blib_num
{
	static function smart_bin($val)
	{
		if($val < 1024)
			return $val.ec(' байт');

		$val /= 1024;
		if($val<1024)
			return self::round12b($val).ec(' кбайт');

		$val /= 1024;
		if($val<1024)
			return self::round12b($val).ec(' Мбайт');

		$val /= 1024;
		if($val<1024)
			return self::round12b($val).ec(' Гбайт');

		return self::round12b($val/1024).ec(' Тбайт');
	}

	// Возвращает число с удобной точностью
	// 1.02, 1.5, 10.1, 90.2, 155
	static function round12b($val)
	{
		if($val > 100)
			return round($val);

		if($val > 10)
			return round($val, 1);

		if($val - floor($val) > 0.2)
			return round($val, 1);

		return round($val, 2);
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals('2 Мбайт', blib_num::smart_bin(2097152));
	}
}
