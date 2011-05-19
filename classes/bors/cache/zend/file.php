<?php

class bors_cache_zend_file extends bors_cache_base
{
	private $ch;

	function init()
	{
		$cache_dir = config('cache_zend_file_dir', '/tmp/cache-zend-file/');
		mkpath($cache_dir, 0777);

		$frontendOptions = array(
			'lifetime' => config('cache_zend_file_lifetime', 7200), // время жизни кэша - 2 часа
			'automatic_serialization' => true
		);

		$backendOptions = array(
			'cache_dir' => $cache_dir // директория, в которой размещаются файлы кэша
		);

		require_once('Zend/Cache.php');
		// получение объекта Zend_Cache_Core
		$this->ch = Zend_Cache::factory('Core',
			'File',
			$frontendOptions,
			$backendOptions);
	}

	function get($type, $key, $default = NULL)
	{
		parent::get($type, $key, $default);

		if(config('cache_disabled'))
			return NULL;

		$this->last = $this->ch->load($this->hmd);
		if($this->last === false)
			$this->last = $default;

		return $this->last;
	}

	function set($value, $ttl)
	{
		if(config('cache_disabled'))
			return $this->last = $value;

		$this->ch->setLifeTime($ttl);
		$this->ch->save($value, $this->hmd);
		return $this->last = $value;
	}

	function last() { return $this->last; }
}
