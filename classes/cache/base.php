<?php

class cache_base
{
	protected $last;
	protected $last_type;
	protected $last_key;
	protected $last_type_name;
	protected $last_uri;
	protected $last_hmd;
	protected $start_time;

	function init($type, $key, $uri = '')
	{
		if(!$uri)
			$uri = $key;

		$this->last_type_name = $type;
		$this->last_type = $type = base_convert(substr(md5($type), 16), 16, 10);
		$this->last_key  = $key  = base_convert(substr(md5($key), 16), 16, 10);
		$this->last_uri  = $uri  = base_convert(substr(md5($uri), 16), 16, 10);
		$this->last_hmd  = $hmd  = base_convert(substr(md5("$type:$key"), 16), 16, 10);

		$this->start_time = microtime(true);
	}

	function last() { return $this->last; }

	function last_cache_id() { return $this->last_hmd; }

	function instance() { return new Cache(); }

	static function get_or_set($type, $key, $function, $ttl)
	{
		$ch = new bors_cache;
		if($ch->get($type, $key))
			return $ch->last();

		return $ch->set($function(), $ttl);
	}
}
