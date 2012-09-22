<?php

class bors_admin_users_logout extends bors_admin_page
{
	function title() { return ec('Выход из системы'); }

	function pre_parse()
	{
		$referer = defval($_GET, 'redirect_url', @$_SERVER['HTTP_REFERER']);
		$referer = defval($_GET, 'ref', $referer);

		if($me = bors()->user())
			$me->do_logout();

		return go($referer && $referer != $this->url() ? $referer : '/');
	}

	function can_cache() { return false; }
	function admin() { return false; }
}
