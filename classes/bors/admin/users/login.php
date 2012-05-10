<?php

class bors_admin_users_login extends base_page
{
	function title() { return ec('Аутентификация'); }

	function template()
	{
		return 'default/index.html';
//		return 'admin/net-dreams/login.html';
	}

	var $error;

	function pre_parse($data)
	{
		$this->referer = defval_ne($_GET, 'redirect_url', @$_SERVER['HTTP_REFERER']);
		$this->referer = defval_ne($_GET, 'ref', $this->referer);

		$this->ref = $this->referer;

		if(!$this->referer || preg_match('!login!', $this->referer))
			$this->referer = '/';

		if(empty($data['login']))
		{
			if(empty($data['form_class_name']))
				return false;
			else
				return go_ref_message(ec("Вы не указали логин"), array('go' => $this->referer, 'error_fields' => 'login'));
		}

		if(empty($data['password']))
		{
			return go_ref_message(ec("Не указали пароль"), array('go' => $this->referer, 'error_fields' => 'password'));
		}

		$me = bors_user::do_login($data['login'], $data['password'], false);

		if(!is_object($me))
		{
			if(!$me)
				$me = ec('Ошибка аутентификации');

			$this->error = $me;
			sleep(2);
			return go_ref_message($me, array('go' => $this->referer, 'error_fields' => 'login,password'));
//			return false;
		}

		return go_ref_message(ec('Вы успешно аутентифицированы, ').$me->title().'!', array('go' => $this->referer, 'error' => false));
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'ref' => $this->ref,
		));
	}

	function can_cache() { return false; }
	function admin() { return false; }

	function config_class() { return 'bors_config'; }
}
