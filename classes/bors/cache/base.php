<?php

class bors_cache_base
{
	protected $type;
	protected $key;
	protected $hmd;
	protected $last;

	function init() { }

	function __construct() { $this->init(); }

	function get($type, $key)
	{
		$this->type = $type;
		$this->key  = $key;
		$this->hmd  = md5($type.':'.$key);
		return $this->last = NULL;
	}

	function set($value, $expire) { return $this->last = $value; }

	function last() { return $this->last; }

	static function get_or_set($type, $key, $function, $ttl)
	{
		$ch = new bors_cache;
		if($ch->get($type, $key))
			return $ch->last();

		return $ch->set($function(), $ttl);
	}
}
