<?php

class bors_cache_memcache extends bors_cache_base
{
	function get($type, $key, $default = NULL, $object = NULL)
	{
		if(config('cache_disabled'))
			return $this->last = $default;

		parent::get($type, $key, $default, $object);

		debug_count_inc('memcache_cache_gets_total');

		$memcache = new Memcache;

		if(!@$memcache->connect(config('memcached')))
		{
			bors_debug::syslog("memcache-error", "Can't connect");
			return $this->last = $default;
		}

		if($x = @$memcache->get('php_bcm_'.$this->hmd))
			return $this->last = $x;

		return $this->last = $default;
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$memcache = new Memcache;

		if(!@$memcache->connect(config('memcached')))
		{
			bors_debug::syslog("memcache-error", "Can't connect");
			return $this->last = $value;
		}

		$memcache->set('php_bcm_'.$this->hmd, $value, MEMCACHE_COMPRESSED, $time_to_expire);

		return $this->last = $value;
	}
}
