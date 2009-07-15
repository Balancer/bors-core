<?php

class bors_user_login extends base_page
{
	function local_template_data_set()
	{
		$base = object_load($_SERVER['REQUEST_URI']);
	
		return array(
			'me' => bors()->user(),
			'referer' => $base ? $base->url() : NULL,
		);
	}
}
