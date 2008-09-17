<?php

class common_do_login extends base_page
{
	function _class_file() { return __FILE__; }
	
	function title() { return ec('Авторизация.'); }
	function template() { return 'forum/common.html'; }

	var $error;

	function pre_parse()
	{
		if(empty($_GET))
			return false;
	
		require_once('obsolete/users.php');
		$me = &new User();
		
		$this->referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];

		if($this->error = $me->do_login(@$_GET['req_username'], @$_GET['req_password'], false))
			return false;
		
		return go($this->referer ? $this->referer : '/');
	}
	
	function can_cache() { return false; }
}
