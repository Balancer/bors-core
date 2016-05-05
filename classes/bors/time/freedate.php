<?php

class bors_time_freedate extends bors_time
{
	function __toString()
	{
		if($this->is_null)
			return '';

		return $this->date('d.m.Y');
	}

	static function load($date)
	{
		if(preg_match('/^(\d{4})(\d\d)(\d\d)$/', $date, $m))
			$timestamp = strtotime("{$m[1]}-{$m[2]}-{$m[3]}");
		else
			throw Exception(sprintf(_('Unknown date format: %s'), $date));

		if($timestamp === false)
			throw Exception(sprintf(_('Can not parse date format: %s'), $date));

		return bors_load(get_called_class(), $timestamp);
	}

	function as_part()
	{
		return bors_lib_date::part($this->_value, strlen($this->_value));
	}
}
