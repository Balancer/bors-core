<?php

class common_do_logout extends base_page
{
	function title() { return ec('Авторизация.'); }
	function template() { return 'forum/common.html'; }

	function pre_parse()
	{
		$referer = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : @$_SERVER['HTTP_REFERER'];

		if($me = bors()->user())
			$me->do_logout();
		else
			bors_user::do_logout();

		$refo = object_load($referer);
		if(!object_property($refo, 'is_public'))
			$referer = NULL;

		return go(($referer && $referer != $this->url()) ? $referer : '/');
	}

	function can_cache() { return false; }
}
