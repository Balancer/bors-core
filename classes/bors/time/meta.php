<?php

class bors_time_meta extends bors_object //TODO: придумать название «пустого» класса вместо base_empty
{
	var $is_null = false;

	function short($def = '') { return bors_lib_time::short($this->timestamp(), $def); }
	function full_hdate() { return bors_lib_date::text($this->timestamp()); }
}
