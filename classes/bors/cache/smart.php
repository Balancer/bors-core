<?php

class bors_cache_smart extends bors_cache_base
{
	private $dbh;
	private $create_time;
	private $expire_time;

	function get($type, $key, $default = NULL, $object = NULL)
	{
		$this->type = base_convert(substr(md5($type), 16), 16, 10);
		$this->key  = base_convert(substr(md5($key), 16), 16, 10);
		$this->uri  = $object ? base_convert(substr(md5($object->internal_uri_ascii()), 16), 16, 10) : 0;
		$this->hmd  = base_convert(substr(md5("$type:$key"), 16), 16, 10);

		$this->start_time = microtime(true);

		if(config('cache_disabled'))
			return $this->last = $default;

		debug_count_inc('smart_cache_gets_total');

		if($x = global_key('cache', $this->hmd))
			return $this->last = $x;

		if($memcache = config('memcached_instance'))
		{
			if($x = @$memcache->get('phpmv4'.$this->hmd))
			{
				debug_count_inc('smart_cache_gets_memcached_hits');
				return $this->last = $x;
			}
		}

		$dbh = new driver_mysql(config('cache_database'));
		$row = $dbh->select('cache', '*', array('raw hmd' => $this->hmd));
		$dbh->close();
		$dbh = NULL;

		$this->last = $row['value'] ? @unserialize($row['value']) : $row['value'];

		$now = time();

		if($row['expire_time'] <= $now)
			$this->last = NULL;
		else
		{
			$this->create_time = $row['create_time'];
			$this->expire_time = $row['expire_time'];
		}

		$new_count = intval($row['count']) + 1;
		$rate = $row['saved_time'] * $new_count / (max($now - $row['create_time'], 1));

		if($this->last && $row['saved_time'] > 0.5)
		{
			@$GLOBALS['bors_stat_smart_cache_gets_db_hits']++;
			$dbh = new driver_mysql(config('cache_database'));
			$dbh->update('cache', array('hmd'=>$this->hmd), array (
				'int access_time' => $now, 
				'int count' => $new_count,
				'float rate' => $rate,
			));
			$dbh->close(); 
			$dbh = NULL;

			if($memcache = config('memcached_instance'))
			{
				$memcache->set('phpmv4'.$this->hmd, $this->last, MEMCACHE_COMPRESSED, $this->expire_time - time()+1);
				debug_count_inc('smart_cache_gets_memcached_updates');
			}
		}

		return ($this->last ? $this->last : $default);
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		$do_time = microtime(true) - $this->start_time;

		if(config('cache_disabled'))
			return $this->last = $value;

		set_global_key('cache', $this->hmd, $value);
		// Если время хранения отрицательное - используется только memcached, при его наличии.
		if($memcache = config('memcached_instance'))
		{
			$memcache->set('phpmv4'.$this->hmd, $value, MEMCACHE_COMPRESSED, abs($time_to_expire));
			debug_count_inc('smart_cache_gets_memcached_stores');

			if($time_to_expire < 0)
				return $this->last = $value;
		}

		$time_to_expire = abs($time_to_expire);

//TODO: сделать настройку отключения. А то мусорит в логах
//		if($do_time < 0.01 && $time_to_expire > 0)
//			debug_hidden_log('cache-not-needed', $do_time);

		if($time_to_expire > 0/* && $do_time > 0.01*/)
		{
			$dbh = new driver_mysql(config('cache_database'));
    		$dbh->replace('cache', array(
				'int hmd'	=> $this->hmd,
				'int type'	=> $this->type,
				'int key'	=> $this->key,
				'int uri'	=> $this->uri,
				'value'	=> serialize($value),
				'int access_time' => 0,
				'int create_time' => $infinite ? -1 : time(),
				'int expire_time' => time() + intval($time_to_expire),
				'int count' => 1,
				'float saved_time' => $do_time,
				'float rate' => 0,
			));
			$dbh->close();
			$dbh = NULL;
		}

		return $this->last = $value;
	}
}
