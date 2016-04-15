<?php

class bors_time_meta extends bors_property
{
	var $is_null = false;

	static function factory($time)
	{
		$class_name = get_called_class();
		if(!is_numeric($time)) // Это дата в произвольном формате
			$time = strtotime($time);

		return new $class_name($time);
	}

	function is_active() { return $this->timestamp() >= time(); }
	function is_future() { return $this->timestamp() > time(); }
	function is_past()   { return $this->timestamp() < time(); }

	function __toString()
	{
		if($this->is_null)
			return '';

		return $this->full();
	}

	function full() { return $this->date('d.m.Y H:i:s'); }

	function full_nobr() { return str_replace(' ', ' ', $this->full()); }

	function short($def = '')
	{
		if($this->is_null)
			return $def;

		return bors_lib_time::short($this->timestamp(), $def);
	}

	function short_ny($def = '')
	{
		if($this->is_null)
			return $def;

		return bors_lib_time::short_ny($this->timestamp(), $def);
	}

	function full_hdate()
	{
		if($this->is_null)
			return '';

		if($ts = $this->timestamp())
			return bors_lib_date::text($ts);

		return NULL;
	}

	function dmy()
	{
		if($this->is_null)
			return '';

		if($ts = $this->timestamp())
			return date('d.m.Y', $ts);

		return NULL;
	}

	function hm()
	{
		if($this->is_null)
			return '';

		if($ts = $this->timestamp())
			return date('H:i', $ts);

		return NULL;
	}

	function dmy_hm()
	{
		if($this->is_null)
			return '';

		if($ts = $this->timestamp())
			return date('d.m.Y H:i', $ts);

		return NULL;
	}

	function fmt($format)
	{
		if($this->is_null)
			return '';

		if($ts = $this->timestamp())
			return date($format, $ts);

		return NULL;
	}
}
