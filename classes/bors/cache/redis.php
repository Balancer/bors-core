<?php

//INFO: в процессе написания.

require_once(config('rediska.include'));

class bors_cache_redis extends cache_base
{
	private $_rediska;
	function __construct()
	{
		$options = array(
			'servers' => array(
				'server1' => array('host' => '127.0.0.1', 'port' => 6379)
			)
		);

		$this->_rediska = new Rediska($options);
	}

	function get($type, $key, $uri='', $default=NULL)
	{
		$this->init($type, $key, $uri);

		if(config('cache_disabled'))
			return $this->last = $default;

		$key = new Rediska_Key($this->hmd);
		if($key->isExists())
			return $this->last = $key->getValue();

		return $this->last = $default;
	}

	function set($value, $time_to_expire = 86400)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$key = new Rediska_Key($this->hmd);
		$key->setExpire($time_to_expire);
		$key->setValue($value);

		return $this->last = $value;
	}
}
