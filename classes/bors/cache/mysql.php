<?php

class bors_cache_mysql extends bors_cache_base
{
	private $dbh;

	function get($type, $key, $default = NULL, $object = NULL)
	{
		$this->type = base_convert(substr(md5($type), 16), 16, 10);
		$this->key  = base_convert(substr(md5($key), 16), 16, 10);
		$this->uri  = $object ? base_convert(substr(md5($object->internal_uri_ascii()), 16), 16, 10) : 0;
		$this->hmd  = base_convert(substr(md5("$type:$key"), 16), 16, 10);

		if(config('cache_disabled'))
			return $this->last = $default;

		debug_count_inc('mysql_cache_gets_total');

		$dbh = new driver_mysql(config('cache_database'));
		$row = $dbh->select('cache', '*', array('raw hmd' => $this->hmd));
		$dbh->close();
		$dbh = NULL;

		$this->last = $row['value'] ? @unserialize($row['value']) : $row['value'];

		$now = time();

		if($row['expire_time'] <= $now)
			$this->last = NULL;

		return ($this->last ? $this->last : $default);
	}

	function set($value, $time_to_expire = 86400, $infinite = false)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$time_to_expire = abs($time_to_expire);

		$dbh = new driver_mysql(config('cache_database'));
   		$dbh->replace('cache', array(
			'int hmd'	=> $this->hmd,
			'int type'	=> $this->type,
			'int key'	=> $this->key,
			'int uri'	=> @$this->uri,
			'value'	=> serialize($value),
			'int access_time' => 0,
			'int create_time' => $infinite ? -1 : time(),
			'int expire_time' => time() + intval($time_to_expire),
			'int count' => 1,
			'float rate' => 0,
		));
		$dbh->close();
		$dbh = NULL;

		return $this->last = $value;
	}
}
