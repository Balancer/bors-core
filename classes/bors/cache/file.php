<?php

//TODO: внимание! Первая пробная версия. Никакой защиты при многопоточности!

class bors_cache_file extends cache_base
{
	function get($type, $key, $uri='', $default=NULL)
	{
		debug_count_inc('file_cache_check');
		$this->init($type, $key, $uri);

		if(config('cache_disabled'))
			return $this->last = $default;

		$cache_dir = config('bors_cache_file_dir', '/tmp/bors-cache-file');

		$data = @unserialize(@file_get_contents($cache_dir.'/'.$this->last_hmd));
		if($data !== false)
		{
			if($data[0] > time()) // $data[0] = expire_time
			{
				debug_count_inc('file_cache_check_hit');
				return $this->last = $data[1];
			}
		}

		return $default;
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$cache_dir = config('bors_cache_file_dir', '/tmp/bors-cache-file');
		$file = $cache_dir.'/'.$this->last_hmd;
		$expire = time() + $time_to_expire;
		mkpath($cache_dir);
		file_put_contents($file, serialize(array($expire, $value)));
		touch($file, $expire);

		debug_count_inc('file_cache_set');
		return $this->last = $value;
	}
}
