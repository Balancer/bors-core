<?php

require_once(config('rediska.include'));

class bors_cache_redis extends bors_cache_base
{
	function init()
	{
		static $_rediska = NULL;
		if($_rediska)
			return;

		$options = array(
			'namespace' => 'BORS_Cache2_',
//			'name'      => 'bors_cache',
//			'serializerAdapter' => 'json',
//			'servers' => array(
//				'server1' => array('host' => '127.0.0.1', 'port' => 6379)
//			)
		);

		$_rediska = new Rediska($options);
	}

	function get($type, $key, $default = NULL)
	{
		parent::get($type, $key, $default);

		if(config('cache_disabled'))
			return NULL;

		$key = new Rediska_Key($this->hmd);

		try
		{
			$this->last = $key->getValue();
		}
		catch(Rediska_Serializer_Adapter_Exception $e)
		{
//			var_dump($e->getMessage());
			debug_count_inc('redis_unserialize_exception');
			debug_hidden_log('redis_exception', $e->getMessage());
			$this->last = NULL;
		}

		if($this->last !== NULL)
		{
			debug_count_inc('redis_cache_hit');
			return $this->last;
		}

		debug_count_inc('redis_cache_miss');
		return $this->last = $default;
	}

	function set($value, $ttl)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$key = new Rediska_Key($this->hmd);
		$key->setValue($value);
		$key->expire($ttl);
		debug_count_inc('redis_cache_store');

		return $this->last = $value;
	}
}
