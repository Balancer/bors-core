<?php

class MemCache
{
	private $last_value	= NULL;
	private $last_key	= NULL;

	function get($key, $default = NULL)
	{
		$this->last_key = $key;

		if(!($memcache = \B2\Cfg::get('memcached_instance')))
			return $this->last_value = $default;

//		$memcache = &new Memcache();
//		$memcache->connect(\B2\Cfg::get('memcached')) or debug_exit('Could not connect memcache');

		if($x = @$memcache->get($key))
			return $this->last_value = $x;

		return $this->last_value = $default;
	}

//	function delete($key) { return $this->set($key, NULL, 0); }

	function set($value, $timeout = 600)
	{
		if(!($memcache = \B2\Cfg::get('memcached_instance')))
			return $this->last_value = $value;

//		$memcache = &new Memcache();
//		$memcache->connect(\B2\Cfg::get('memcached')) or debug_exit('Could not connect memcache');

		if($value = NULL || $timeout == 0)
			$memcache->delete($this->last_key);
		else
			$memcache->set($this->last_key, $value, true, $timeout);

	}

	function last() { return $this->last_value; }
}
