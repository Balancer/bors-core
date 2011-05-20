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
			$yyyy	= sprintf('%04d', @$array["{$var}_year"]);
			$mm	= sprintf('%02d', @$array["{$var}_month"]);
			$dd	= sprintf('%02d', @$array["{$var}_day"]);
			$hh	= sprintf('%02d', @$array["{$var}_hour"]);
			$ii	= sprintf('%02d', @$array["{$var}_minute"]);
			$ss	= sprintf('%02d', @$array["{$var}_seconds"]);

			$is_fuzzy = @$array["{$var}_is_fuzzy"];
			$is_utc   = @$array["{$var}_is_utc"];

			if(intval($yyyy))
			{
				if($is_fuzzy)
				{
					// YYYYMMDDHHIISS - xxx_is_fuzzy && xxx_have_time && xxx_is_integer
					if(@$array["{$var}_have_time"] && @$array["{$var}_is_integer"])
						$array[$var] = $yyyy.$mm.$dd.$hh.$ii.$ss;

					// YYYY-MM-DD hh:ii:ss - xxx_is_fuzzy && xxx_have_time
					elseif(@$array["{$var}_have_time"])
						$array[$var] = $yyyy.'-'.$mm.'-'.$dd.' '.$hh.':'.$ii.':'.$ss;

					// YYYYMMDD - xxx_is_fuzzy && xxx_is_integer
					elseif(@$array["{$var}_is_integer"])
						$array[$var] = $yyyy.$mm.$dd;

					// YYYY-MM-DD - xxx_is_fuzzy
					else
						$array[$var] = $yyyy.'-'.$mm.'-'.$dd;

					if($size = @$array["{$var}_is_integer"]) // is_integer - не только флаг, но и указание на длину последовательности.
						$array[$var] = substr($array[$var], 0, $size);
				}
				else // unixtime - по умолчанию
				{
//					echo "****** {$yyyy}-{$mm}-{$dd} $hh:$ii:$ss -> ".strtotime("{$yyyy}-{$mm}-{$dd} $hh:$ii:$ss")."<br/>";
					$tz_save = date_default_timezone_get();
					if($tz = @$array["{$var}_timezone"])
						date_default_timezone_set($tz);
					$array[$var] = strtotime("{$yyyy}-{$mm}-{$dd} $hh:$ii:$ss");
					date_default_timezone_set($tz_save);
				}
			}
			else // Если год не указан...
			{
				if($is_fuzzy) // И формат плавающий, значит дата не указана совсем.
					$array[$var] = NULL;
				else // если формат фиксированный, значит нам передали простую строку с датой для strtotime:
				{
//					echo "====== {$array[$var]} -> ".strtotime($array[$var])."<br/>";
					$array[$var] = strtotime(@$array[$var]);
				}
			}

			// И почистим массив, оставив только результат.
			unset($array["{$var}_hour"], $array["{$var}_minute"], $array["{$var}_second"],
				$array["{$var}_month"], $array["{$var}_day"], $array["{$var}_year"],
				$array["{$var}_is_fuzzy"], $array["{$var}_have_time"], $array["{$var}_is_integer"],
				$array["{$var}_is_utc"], $array["{$var}_timezone"]);
		}

		unset($array['time_vars']);
	}
}
