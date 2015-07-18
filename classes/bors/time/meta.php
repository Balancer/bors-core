<?php

class bors_time_meta extends bors_property
{
	var $is_null = false;

	static function factory($timestamp) { return new bors_time($timestamp); }

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
