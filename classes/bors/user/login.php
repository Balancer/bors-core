<?php

class bors_user_login extends base_page
{
	function body_data()
	{
		$base = object_load($_SERVER['REQUEST_URI']);

		return array(
			'me' => bors()->user(),
			'referer' => $base ? $base->url() : NULL,
		);
	}
}
