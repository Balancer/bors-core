<?php

class bors_time_date extends bors_time
{
	function __toString()
	{
		if($this->is_null)
			return '';

		return $this->date('d.m.Y');
	}

	static function load($timestamp)
	{
		return bors_load(__CLASS__, $timestamp);
	}

	function as_part()
	{
		return bors_lib_date::part($this->_value, strlen($this->_value));
	}
}
