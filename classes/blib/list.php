<?php

class blib_list
{
	static function cleaning_int($list)
	{
		if(is_numeric($list))
			return intval($list);

		$result = array();
		foreach(explode(",", $list) as $i)
			$result[] = intval($i);

		return join(",", $result);
	}

	// Разворачивает строку вида "1,5-7,9" в array(1,5,6,7,9);
	static function parse_condensed($condensed_string)
	{
		$numbers = array();

		foreach(explode(",", $condensed_string) as $n)
		{
			if(!$n)
				continue;

			if(strpos($n, '-') === false)
				$numbers[] = intval($n);
			else
			{
				list($b,$e) = explode('-', $n);
				for($j = $b; $j <= $e; $j++)
					$numbers[] = intval($j);
			}
		}

		return $numbers;
	}
}
