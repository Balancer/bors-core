<?php

class bors_object_data extends bors_object
{
	function can_cached() { return true; }
	function storage_engine() { bors_throw(ec('Не задан класс хранилища данных')); }
	function can_be_empty() { return false; }
	function storage() { return bors_load($this->storage_engine(), NULL); }

	function data_load()
	{
		return $this->storage()->load($this);
	}
}
