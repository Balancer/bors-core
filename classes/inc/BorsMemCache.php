<?php

class BorsMemCache
{
	private $last_value	= NULL;
	private $last_key	= NULL;

	function get($key, $default = NULL)
	{
		$this->last_key = $key;

		if(!($memcache = config('memcached_instance')))
			return $this->last_value = $default;

		if($x = $memcache->get($key))
			return $this->last_value = $x;

		return $this->last_value = $default;
	}

//	function delete($key) { return $this->set($key, NULL, 0); }

	function set($value, $timeout = 600)
	{
		if(!($memcache = config('memcached_instance')))
			return $this->last_value = $value;

		if($value == NULL || $timeout == 0)
			@$memcache->delete($this->last_key);
		else
			$memcache->set($this->last_key, $value, 0, $timeout);
	}

	function last() { return $this->last_value; }
}
