<?php

class bors_cache_base
{
	protected $type;
	protected $key;
	var $hmd;
	protected $last;

	function init() { }

	function __construct() { $this->init(); }

	function get($type, $key, $default = NULL, $object = NULL)
	{
		$this->type = $type;
		$this->key  = $key;
		$this->hmd  = md5($type.':'.$key);
		return $this->last = $default;
	}

	function check($type, $key, $default = NULL, $object = NULL)
	{
		$this->type = $type;
		$this->key  = $key;
		$this->hmd  = md5($type.':'.$key);
		return $this->last = $default;
	}

	function set($value, $expire, $cache_id = NULL) { return $this->last = $value; }

	function last() { return $this->last; }

	static function get_or_set($type, $key, $function, $ttl, $object = NULL)
	{
		$ch = new bors_cache;
		if($value = $ch->get($type, $key))
			return $value;

		return $ch->set($function(), $ttl, $object ? $object->internal_uri() : NULL);
	}
}
