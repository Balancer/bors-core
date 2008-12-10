<?php

class bors_admin_logout extends base_page
{
	function pre_parse()
	{
		bors()->user()->logout();
		return go('/');
	}
}
