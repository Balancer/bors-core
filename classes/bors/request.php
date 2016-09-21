<?php

class bors_request extends bors_object
{
	function can_cached() { return true; }

	static function is_utf8() { return config('internal_charset') == 'utf-8'; }

	static function data($key = NULL, $default = NULL) { return $key ? defval($_GET, $key, $default) : $_GET; }

	static function data_parse($type, $key, $default = NULL)
	{
		$val = self::data($key, $default);
		if($val)
		{
			switch($type)
			{
				case 'signed_name':
					if(!preg_match('/^[\-\+]*\w+$/', $val))
						$val = NULL;
					break;
				case 'signed_names':
					if(!preg_match('/^([\-\+]*\w+\s*,?\s*)+$/', $val))
						$val = NULL;
					break;
				case 'bool':
					$val = (bool) $val;
					break;
				case 'int':
					$val = intval($val);
					break;
				case 'color':
					if(preg_match('/^[\da-f]{3}$/i', $val) || preg_match('/^[\da-f]{6}$/i', $val))
						$val = "#$val";
					elseif(!preg_match('/^\w+$/', $val) && !preg_match('/^#[\da-f]+$/i', $val))
						$val = "";
					break;
				case 'float_str':
					$val = str_replace(',', '.', floatval($val));
					break;
			}
		}

		return $val;
	}

	function is_post() { return @$_SERVER['REQUEST_METHOD'] == 'POST'; }

	function pure_url()
	{
		$url = self::url();
		if(preg_match('/^(.+?)\?/', $url, $m))
			return $m[1];

		return $url;
	}

	function query_string()
	{
		return http_build_query($_GET);
	}

	function remote_ip() { return @$_SERVER['REMOTE_ADDR']; }
	function referer() { return defval($_GET, 'ref', @$_SERVER['HTTP_REFERER']); }

	function is_accept_image() { return preg_match('!^image/!', @$_SERVER['HTTP_ACCEPT']); }
	function is_accept_text()  { return self::is_ie8() || preg_match('!^text/!' , @$_SERVER['HTTP_ACCEPT']); }

	//  Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB7.5; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)
	function is_ie8() { return preg_match('/compatible; MSIE 8.0; Windows/', @$_SERVER['HTTP_USER_AGENT']); }

	function url()
	{
		if(!empty($GLOBALS['bors_full_request_url']))
			return @$GLOBALS['bors_full_request_url'];

		$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
		return $request->getUri();
	}

	function url_data($name = NULL, $default = NULL)
	{
		$data = url_parse(self::url());

		if(!$name)
			return $data;

		return defval($data, $name, $default);
	}

	function path() { return $this->url_data('path'); }

	function url_match($regexp)
	{
		if(preg_match($regexp, $this->url(), $m))
			return $m;

		return false;
	}
}
