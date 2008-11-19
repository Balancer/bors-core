<?php

class cache_smart extends cache_base
{
	private $dbh;
	private $create_time;
	private $expire_time;
        
    function __construct()
	{
		if(!config('cache_disabled'))
			$this->dbh = &new driver_mysql(config('cache_database'));
	}

	function get($type, $key, $uri='', $default=NULL)
	{
		$this->init($type, $key, $uri);
		
		if(config('cache_disabled'))
			return $this->last = $default;

		if($memcache = config('memcached_instance'))
		{
//			$memcache = &new Memcache;
//			$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");

			@$GLOBALS['bors_stat_smart_cache_gets_total']++;
			if($x = @$memcache->get('phpmv3'.$this->last_hmd))
			{
				@$GLOBALS['bors_stat_smart_cache_gets_memcached_hits']++;
				return $this->last = $x;
			}
		}
				
		$row = $this->dbh->get("SELECT * FROM `cache` WHERE `hmd`={$this->last_hmd}");
		$this->last = $row['value'] ? @unserialize($row['value']) : $row['value'];

		$now = time();

		if($row['expire_time'] <= $now)
		{
			$this->last = NULL;
#			$this->dbh->query("DELETE FROM `cache` WHERE `hmd`={$this->last_hmd}");
		}
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
			$this->dbh->update('cache', "`hmd`={$this->last_hmd}", array (
				'int access_time' => $now, 
				'int count' => $new_count,
				'float rate' => $rate,
			));
		}	
			
		return ($this->last ? $this->last : $default);
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		if(config('cache_disabled'))
			return $this->last = $value;


		// Если время хранения отрицательное - используется только memcached, при его наличии.
		
		if($memcache = config('memcached_instance'))
		{
//			$memcache = &new Memcache;
//			$memcache->connect(config('memcached')) or debug_exit("Could not connect memcache");
			@$memcache->set('phpmv3'.$this->last_hmd, $value, true, abs($time_to_expire));
		}
		else
			$time_to_expire = abs($time_to_expire);

		$do_time = microtime(true) - $this->start_time;
		if($time_to_expire > 0 && $do_time > 0.02)
		{
    		$this->dbh->replace('cache', array(
				'int hmd'	=> $this->last_hmd,
				'int type'	=> $this->last_type,
				'int key'	=> $this->last_key,
				'int uri'	=> $this->last_uri,
				'value'	=> serialize($value),
				'int access_time' => 0,
				'int create_time' => $infinite ? -1 : time(),
				'int expire_time' => time() + intval($time_to_expire),
				'int count' => 1,
				'float saved_time' => $do_time,
				'float rate' => 0,
			));
		}
			
		return $this->last = $value;
	}
}
