<?php

class bors_cache_apc extends cache_base
{
	function get($type, $key, $uri='', $default=NULL)
	{
		$this->init($type, $key, $uri);

		if(config('cache_disabled'))
			return $this->last = $default;

		$data = apc_fetch($this->last_hmd);
		if($data !== false)
			return $this->last = $data;

		return $default;
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		apc_store($this->last_hmd, $value);

		return $this->last = $value;
	}
}
