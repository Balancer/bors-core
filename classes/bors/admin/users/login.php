<?php

class bors_admin_users_login extends base_page
{
	function title() { return ec('Аутентификация'); }

	var $error;

	function pre_parse($data)
	{
		if(empty($_GET) || empty($data['login']) || empty($data['password']))
			return false;

		$this->referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];

		$me = bors_user::do_login($data['login'], $data['password']);

		if(!is_object($me))
		{
			$this->error = $me;
			return false;
		}

		return go(($this->referer && !preg_match('!login!', $this->referer)) ? $this->referer : '/');
	}

	function can_cache() { return false; }
	function admin() { return false; }
}
