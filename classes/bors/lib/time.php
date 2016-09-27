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
			$array[$var] = trim(@$array[$var]);
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

	private static function _join($var, &$data)
	{
		$can_drop = popval($data, "{$var}_can_drop");
		if(empty($data[$var]) && $can_drop)
			return NULL;

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
			if(!$yyyy) // Если формат плавающий и год не указан, то дата не задана
				return NULL;

			// YYYY-MM-DD hh:ii:ss - xxx_is_fuzzy && xxx_have_time
			if(@$data["{$var}_have_time"])
				return $yyyy.'-'.$mm.'-'.$dd.' '.$hh.':'.$ii.':'.$ss;

			// YYYY-MM-DD - xxx_is_fuzzy
			return $yyyy.'-'.$mm.'-'.$dd;
		}

		if(!intval($yyyy)) // если формат фиксированный и год не указан, значит нам передали простую строку с датой для strtotime:
		{
//			echo "====== {$data[$var]} -> ".strtotime($data[$var])."<br/>";
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

	static function short($time, $def = '')
	{
		if(!$time)
			return $def;

		global $now;
		if(empty($now))
			$now = time();

		$time = intval($time);

		if(abs($now - $time) < 86400 && strftime("%d", $time) == strftime("%d", $now))
			return strftime("%H:%M", $time);
		else
			return strftime("%d.%m.%Y", $time);
	}

	static function short_ny($time, $def = '')
	{
		if(!$time)
			return $def;

		global $now;
		if(empty($now))
			$now = time();

		$time = intval($time);

		if(abs($now - $time) < 86400/* && strftime("%d", $time) == strftime("%d", $now)*/)
			return strftime("%H:%M", $time);
		else
			return strftime("%d.%m", $time);
	}

	static function smart_interval($interval, $parts = 2)
	{
		$res = array();
		$res[] = ($x = $interval % 60) ? $x.ec(' секунд').sklon($x,ec('а,ы,')) : '';
		$interval = intval($interval/60);
		$res[] = ($x = $interval % 60) ? $x.ec(' минут').sklon($x,ec('а,ы,')) : '';
		$interval = intval($interval/60);
		$res[] = ($x = $interval % 24) ? $x.ec(' час').sklon($x,ec(',а,ов')) : '';
		$interval = intval($interval/24);

		$res[] = ($x = $interval % 365) ? $x.' '.sklon($x,ec('день,дня,дней')) : '';
		$interval = intval($interval/365);

		$res[] = ($x = $interval) ? $x.' '.sklon($x, ec('год,года,лет')) : '';

		$res = array_reverse($res);

		for($i=0; $i<count($res); $i++)
			if(!empty($res[$i]))
				break;

		return join(' ', array_slice($res, $i, $parts));
	}

	static function smart_interval_vp($interval, $parts = 2)
	{
		require_once('inc/strings.php');
		$res = array();
		$res[] = ($x = $interval % 60) ? $x.ec(' секунд').sklon($x,ec('у,ы,')) : '';
		$interval = intval($interval/60);
		$res[] = ($x = $interval % 60) ? $x.ec(' минут').sklon($x,ec('у,ы,')) : '';
		$interval = intval($interval/60);
		$res[] = ($x = $interval % 24) ? $x.ec(' час').sklon($x,ec(',а,ов')) : '';
		$interval = intval($interval/24);

		$res[] = ($x = $interval % 365) ? $x.' '.sklon($x,ec('день,дня,дней')) : '';
		$interval = intval($interval/365);

		$res[] = ($x = $interval) ? $x.' '.sklon($x, ec('год,года,лет')) : '';

		$res = array_reverse($res);

		for($i=0; $i<count($res); $i++)
			if(!empty($res[$i]))
				break;

		return join(' ', array_slice($res, $i, $parts));
	}

	/**
		Печать в human-readable виде даты последнего
		изменений класса:
	*/

	function class_last_modify_htime($class_name)
	{
		$last_mt = bors_find_first($class_name, array('order' => '-modify_time'))->modify_time();
		$last_ct = bors_find_first($class_name, array('order' => '-create_time'))->create_time();
		$last_action_ts = max($last_mt, $last_ct);
		$last_interval = bors_lib_time::smart_interval_vp(time() - $last_action_ts);
		$last_edit = short_time($last_action_ts);
		return ec("{$last_edit} ({$last_interval} назад)");
	}

	static function __unit_test($suite)
	{
		$data = [
			'begin_time_day' => 9,
			'begin_time_month' => 4,
			'begin_time_year' => 2015,
			'begin_time_hour' => 04,
			'begin_time_minute' => 15,
			'begin_time_timezone' => 'Europe/Moscow',
			'time_vars' => 'begin_time',
		];

		self::parse_form($data);
		$suite->assertEquals(['begin_time' => 1428542100], $data);
	}
}
