<?php

/*
	Изучить:
		predis/predis
		Flexible and feature-complete PHP client library for Redis
		http://mikehaertl.github.io/phpwkhtmltopdf/
*/

if(!class_exists('Rediska'))
	bors_throw("Can't find Rediska package. Go to composer directory at BORS_CORE level and execute composer require rediska/rediska=*");

class bors_cache_redis extends bors_cache_base
{
	function init()
	{
		static $_rediska = NULL;
		if($_rediska)
			return;

		$options = array(
			'namespace' => 'BORS_Cache3:',
//			'name'      => 'bors_cache',
//			'serializerAdapter' => 'json',
//			'servers' => array(
//				'server1' => array('host' => '127.0.0.1', 'port' => 6379)
//			)
		);

		if($cfg_srv = config('redis.servers'))
			$options['servers'] = $cfg_srv;

		$_rediska = new Rediska($options);
	}

	function check($type, $key, $default = NULL)
	{
		if(config('cache_disabled'))
		{
			$this->last = $default;
			return false;
		}

		$this->type = $type;
		$this->key  = $key;
		$this->hmd  = $type.':'.(preg_match('/^[\w\.\-:]+$/', $key) ? $key : md5($key));

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
			$this->last = $default;
		}

		if($key->isExists())
		{
			debug_count_inc('redis_cache_check_hit');
			return true;
		}

		debug_count_inc('redis_cache_check_miss');
		$this->last = $default;
		return false;
	}

	function get($type, $key, $default = NULL)
	{
		bors_function_include('debug/count_inc');

		if(config('cache_disabled'))
			return NULL;

		$this->type = $type;
		$this->key  = $key;
		$this->hmd  = $type.':'.(preg_match('/^[\w\.\-:]+$/', $key) ? $key : md5($key));

		debug_count_inc('redis_debug. Get for '.$this->hmd);

		try
		{
			$key = new Rediska_Key($this->hmd);
			$this->last = $key->getValue();
		}
		catch(Exception $e)
		{
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

		debug_count_inc('redis_debug. Set for '.$this->hmd);
//		if(preg_match('/GIF/', $value))
//			bors_debug::syslog('debug-set-redis', "val=".print_r($value, true));

		try
		{
			$key = new Rediska_Key($this->hmd);
			$key->setValue($value);
			$key->expire($ttl);
		}
		catch(Exception $e)
		{
		}

		debug_count_inc('redis_cache_store');

		return $this->last = $value;
	}
}
