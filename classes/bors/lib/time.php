<?php

// Библиотеки для работы со временем

class bors_lib_time
{

	static function parse_property($name, &$array, $unixtime = true)
	{
		if($unixtime)
			unset($array["{$name}_is_fuzzy"]);
//		var_dump($array);
//		if(empty($array['time_vars']))
//			$array['time_vars'] = $name;
//		else
//			$array['time_vars'] .= ' '.$name;

		self::parse_form($array);
//		var_dump($array); exit();
		return $array[$name];
	}

	static function parse_form(&$array)
	{
		if(empty($array['time_vars']))
			return;

/**
		Возможные входные форматы даты
		 - xxx_year, xxx_month, xxx__day[, xxx_hour, xxx_minute, xxx_seconds - полный формат
		 - xxx_year[, xxx_month[, xxx__day[, ... ]]] - Неполный формат, только с xxx_is_fuzzy
		 - нету xxx_year, но нет и xxx_is_fuzzy, значит, дата на входе в виде простой строки.

		- параметр xxx_is_null указывает на то, что дата не задана, в каком бы виде не передавалась.

		возможный выход:
		 - unixtime		- по умолчанию
		 - YYYY-MM-DD	- xxx_is_fuzzy
		 - YYYYMMDD		- xxx_is_fuzzy && xxx_is_integer
		 - YYYY-MM-DD hh:ii:ss	- xxx_is_fuzzy && xxx_have_time
		 - YYYYMMDDHHIISS		- xxx_is_fuzzy && xxx_have_time && xxx_is_integer

		Старый формат: bors_form_parse_time(&$array, $integer = false)
		http://trac.balancer.ru/bors-core/browser/inc/datetime.php?rev=7f771e3209b85370e4d4fdae5afbe86b5082bddd
*/

		foreach(explode(',', $array['time_vars']) as $var)
		{

			$is_fuzzy = @$array["{$var}_is_fuzzy"];
			$is_utc   = @$array["{$var}_is_utc"];

			$array[$var] = self::_join($var, $array);

			if($size = @$array["{$var}_is_integer"]) // is_integer - не только флаг, но и указание на длину последовательности.
				$array[$var] = substr($array[$var], 0, $size);

			if(!empty($array["{$var}_is_null"]))
				$array[$var] = NULL;

			// И почистим массив, оставив только результат.
			unset($array["{$var}_hour"], $array["{$var}_minute"], $array["{$var}_second"],
				$array["{$var}_month"], $array["{$var}_day"], $array["{$var}_year"],
				$array["{$var}_is_fuzzy"], $array["{$var}_have_time"], $array["{$var}_is_integer"],
				$array["{$var}_is_utc"], $array["{$var}_timezone"], $array["{$var}_is_null"]);
		}

		unset($array['time_vars']);
	}

	private function _join($var, $data)
	{

		$yyyy	= sprintf('%04d', @$data["{$var}_year"]);
		$mm	= sprintf('%02d', @$data["{$var}_month"]);
		$dd	= sprintf('%02d', @$data["{$var}_day"]);
		$hh	= sprintf('%02d', @$data["{$var}_hour"]);
		$ii	= sprintf('%02d', @$data["{$var}_minute"]);
		$ss	= sprintf('%02d', @$data["{$var}_seconds"]);

		// YYYYMMDDHHIISS - xxx_have_time && xxx_is_integer, т.к. is_integer — всегда is_fuzzy
		if(@$data["{$var}_have_time"] && @$data["{$var}_is_integer"])
			return $yyyy.$mm.$dd.$hh.$ii.$ss;

		// YYYYMMDD - xxx_is_integer
		if(@$data["{$var}_is_integer"])
			return $yyyy.$mm.$dd;

		if(!empty($data["{$var}_is_fuzzy"]))
		{
			if(!$year) // Если формат плавающий и год не указан, то дата не задана
				return NULL;

			// YYYY-MM-DD hh:ii:ss - xxx_is_fuzzy && xxx_have_time
			if(@$data["{$var}_have_time"])
				return $yyyy.'-'.$mm.'-'.$dd.' '.$hh.':'.$ii.':'.$ss;

			// YYYY-MM-DD - xxx_is_fuzzy
			return $yyyy.'-'.$mm.'-'.$dd;
		}

		if(!$year) // если формат фиксированный и год не указан, значит нам передали простую строку с датой для strtotime:
		{
//			echo "====== {$array[$var]} -> ".strtotime($array[$var])."<br/>";
			return strtotime(@$data[$var]);
		}

		// unixtime - по умолчанию
//		echo "****** {$yyyy}-{$mm}-{$dd} $hh:$ii:$ss -> ".strtotime("{$yyyy}-{$mm}-{$dd} $hh:$ii:$ss")."<br/>";
		$tz_save = date_default_timezone_get();
		if($tz = @$data["{$var}_timezone"])
			date_default_timezone_set($tz);
		$time = strtotime("{$yyyy}-{$mm}-{$dd} $hh:$ii:$ss");
		date_default_timezone_set($tz_save);
		return $time;
	}
}
