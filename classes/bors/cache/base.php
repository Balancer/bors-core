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
}
