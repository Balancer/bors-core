<?php

class common_do_logout extends base_page
{
	function _class_file() { return __FILE__; }
	
	function title() { return ec('Авторизация.'); }
	function template() { return 'forum/common.html'; }

	function preParseProcess()
	{
		$referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];
		$me = &new User();
		$me->do_logout();
		
		return go($referer && $referer != $this->url() ? $referer : '/');
	}
	
	function can_cache() { return false; }
}
