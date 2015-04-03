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

	function referer() { return defval($_GET, 'ref', @$_SERVER['HTTP_REFERER']); }

	function is_accept_image() { return preg_match('!^image/!', @$_SERVER['HTTP_ACCEPT']); }
	function is_accept_text()  { return preg_match('!^text/!' , @$_SERVER['HTTP_ACCEPT']); }

	function url() { return @$GLOBALS['bors_full_request_url']; }

	function url_data($name = NULL, $default = NULL)
	{
		$data = url_parse(self::url());

		if(!$name)
			return $data;

		return defval($data, $name, $default);
	}

	function path() { return $this->url_data('path'); }
}
