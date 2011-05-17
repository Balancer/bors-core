<?php

//INFO: в процессе написания.

require_once(config('rediska.include'));

class bors_cache_redis extends bors_cache_base
{
	function init()
	{
		static $_rediska = NULL;
		if($_rediska)
			return;

		$options = array(
//			'namespace' => 'BORS_Cache_',
//			'name'      => 'bors_cache',
			'servers' => array(
				'server1' => array('host' => '127.0.0.1', 'port' => 6379)
			)
		);

		$_rediska = new Rediska;//($options);
	}

	function get($type, $key, $default = NULL)
	{
		parent::get($type, $key, $default);

		if(config('cache_disabled'))
			return NULL;

		$key = new Rediska_Key($this->hmd);

		$this->last = $key->getValue();
		if($this->last !== NULL)
			return $this->last;

		return $this->last = $default;
	}

	function set($value, $ttl)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$key = new Rediska_Key($this->hmd);
		$key->setValue($value);
		$key->expire($ttl);

		return $this->last = $value;
	}
}
