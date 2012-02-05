<?php

class common_do_login extends base_page
{
	function title() { return ec('Авторизация.'); }

	function pre_parse()
	{
		if(empty($_GET))
		{
			debug_hidden_log('error_auth', ec('Ошибка передачи параметров в класс логина, пустой _GET'));
			return bors_message(ec('Ошибка передачи параметров. Возможно, сбой в настройке сервера. Администрация извещена о проблеме.'));
		}

		require_once('obsolete/users.php');

		$this->referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];

		$me = bors_user::do_login(@$_GET['req_username'], @$_GET['req_password'], false);

		if(!is_object($me))
			set_session_var('error_message', $me);

		return go(($this->referer && !preg_match('!login!', $this->referer)) ? $this->referer : '/');
	}

	function can_cache() { return false; }
}
