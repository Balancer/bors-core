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
		$this->last_type = $type = "0x".substr(md5($type), 16);
		$this->last_key  = $key  = "0x".substr(md5($key), 16);
		$this->last_uri  = $uri  = "0x".substr(md5($uri), 16);
		$this->last_hmd  = $hmd  = "0x".substr(md5("$type:$key"), 16);

		list($usec, $sec) = explode(" ",microtime());
		$this->start_time = (float)$usec + (float)$sec;
	}

	function last() { return $this->last; }

	function last_cache_id() { return $this->last_hmd; }

	function instance() { return new Cache(); }
}
