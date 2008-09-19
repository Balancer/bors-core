<?php

class common_do_login extends base_page
{
	function title() { return ec('Авторизация.'); }
	function template() { return 'forum/common.html'; }

	var $error;

	function pre_parse()
	{
		if(empty($_GET))
			return false;
	
		require_once('obsolete/users.php');
		
		$this->referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];

		$me = bors_user::do_login(@$_GET['req_username'], @$_GET['req_password'], false);

		if(!is_object($me))
			return false;
		
		return go($this->referer ? $this->referer : '/');
	}
	
	function can_cache() { return false; }
}
