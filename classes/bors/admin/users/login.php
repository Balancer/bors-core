<?php

class bors_admin_users_login extends base_page
{
	function title() { return ec('Аутентификация'); }

	var $error;

	function pre_parse($data)
	{
		$this->referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];
		if(!$this->referer || preg_match('!login!', $this->referer))
			$this->referer = '/';

		if(empty($data['login']))
		{
			set_session_var('error_message', ec('Вы не указали логин'));
			return go($this->referer);
		}

		if(empty($data['password']))
		{
			set_session_var('error_message', ec('Вы не указали пароль'));
			return go($this->referer);
		}

		$me = bors_user::do_login($data['login'], $data['password']);

		if(!is_object($me))
		{
			if(!$me)
				$me = ec('Ошибка аутентификации');

			$this->error = $me;
			set_session_var('error_message', $me);
			return go($this->referer);
		}

		set_session_var('success_message', ec('Добро пожаловать, '.$me->title()));
		return go($this->referer);
	}

	function can_cache() { return false; }
	function admin() { return false; }
}
