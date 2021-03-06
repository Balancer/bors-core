<?php

class bors_server extends bors_object
{
	var $_web_server = NULL;

	function can_cached() { return true; }

	static function is_utf8() { return \B2\Cfg::get('internal_charset') == 'utf-8'; }

	static function post_redirector_url($url)
	{
		return "http://{$_SERVER['HTTP_HOST']}/bors-post-redirector.php?go=".urlencode($url);
	}

	function document_root() { return $this->_web_server ? $this->_web_server->_root : @$_SERVER['DOCUMENT_ROOT']; }
	function root() { return $this->_web_server ? $this->_web_server->_root : @$_SERVER['DOCUMENT_ROOT']; }
	function ip() { return @$_SERVER['SERVER_ADDR']; }
	function host() { return $this->_web_server ? $this->_web_server->_host : preg_replace('/:\d+$/', '', @$_SERVER['HTTP_HOST']); }
	function host_strip() { return str_replace('www.', '', $this->host()); }
	function port() { return $this->_web_server ? $this->_web_server->_port : empty($_SERVER['HTTP_PORT']) ? '' : $_SERVER['HTTP_PORT']; }

	function portize($url)
	{
		$port = $this->port();
		if(!$port || $port == 80)
			return $url;

		return preg_replace('!^(http://[^/:]+)/!', '$1:'.$port.'/', $url);
	}
}
