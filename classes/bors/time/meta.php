<?php

class bors_time_meta extends bors_object //TODO: придумать название «пустого» класса вместо base_empty
{
	function short($def = '') { return bors_lib_time::short($this->timestamp(), $def); }
}