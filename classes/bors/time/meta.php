<?php

class bors_time_meta extends bors_object //TODO: придумать название «пустого» класса вместо base_empty
{
	var $is_null = false;

	function short($def = '') { return bors_lib_time::short($this->timestamp(), $def); }
	function full_hdate()
	{
		if($ts = $this->timestamp())
			return bors_lib_date::text($ts);

		return NULL;
	}

	function dmy()
	{
		if($ts = $this->timestamp())
			return date('d.m.Y', $ts);

		return NULL;
	}

	function hm()
	{
		if($ts = $this->timestamp())
			return date('H:i', $ts);

		return NULL;
	}
}
