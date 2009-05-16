<?php

class bors_user_flat extends bors_user_base
{
	function title() { return $this->data('user_name'); }
	function can_be_empty() { return false; }
	function loaded() { return $this->cookie_hash(); }

	static function id_prepare($id)
	{
		if($id == -1)
		{
			$me = self::id_by_cookie();
			return $me ? $me->id() : NULL;
		}

		return $id;
	}

	static $users_data = NULL;
	static function _load_users_data()
	{
		if(!is_null(self::$users_data))
			return self::$users_data;

		$users_data = array();
		foreach(file(config('user_flat_base')) as $x)
			if(preg_match('!^(\d+)::([^:]+)::([\da-fA-F]+)::([^:]+)::(\d+)$!', chop($x), $m))
				self::$users_data[$m[1]] = array(
					'login' => $m[2],
					'md5_password' => $m[3],
					'user_name' => $m[4],
					'access_level' => $m[5],
					'user_hash' => md5($m[2].':'.$m[3]),
				);

		return self::$users_data;
	}

	static function id_by_cookie($cookie_hash = NULL)
	{
		if(is_null($cookie_hash))
			$cookie_hash = @$_COOKIE['cookie_hash'];

		if(!$cookie_hash)
			return false;

		foreach(self::_load_users_data() as $test_user_id => $x)
			if($cookie_hash == $x['user_hash'])
				return self::_user_object($test_user_id);

		return false;
	}

	static function do_login($login, $password)
	{
		$md5_password = md5($password);

		foreach(self::_load_users_data() as $test_user_id => $x)
			if($login == $x['login'] && $x['md5_password'] == $md5_password)
				return self::_user_object($test_user_id, true);

		return false;
	}

	static function do_logout()
	{
		foreach(array('user_id', 'cookie_hash', 'is_admin') as $k)
		{
			SetCookie($k, NULL, 0, "/", '.'.$_SERVER['HTTP_HOST']);
			SetCookie($k, NULL, 0, "/", $_SERVER['HTTP_HOST']);
			SetCookie($k, NULL, 0, "/");
		}
	}

	static function _user_object($user_id, $update_cookie = false)
	{
		$user = new bors_user($user_id);

		if($update_cookie)
			$user->cookie_hash_set();

		return $user;
	}

	function cookie_hash()
	{
		$ud = self::_load_users_data();
		return @$ud[$this->id()]['user_hash'];
	}

	function cookie_hash_set($expired = -1)
	{
		if($expired == -1)
			$expired = time()+86400*365;

		foreach(array('user_id' => $this->id(), 'cookie_hash' => $this->cookie_hash(), 'is_admin' => $this->is_admin()) as $k => $v)
		{
			SetCookie($k, $v, $expired, "/", '.'.$_SERVER['HTTP_HOST']);
			SetCookie($k, $v, $expired, "/", $_SERVER['HTTP_HOST']);
			SetCookie($k, $v, $expired, "/");
		}
	}

	private function data($key)
	{
		$ud = $this->_load_users_data();
		return @$ud[$this->id()][$key];
	}

	function is_admin() { return $this->data('access_level') > 2; }
	function can_edit($object) { return $this->data('access_level') > 2; }
}
