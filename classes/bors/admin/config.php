<?php

class bors_admin_config extends base_config
{
	function config_data()
	{
		return array(
			'access_engine' => config('access_default', 'access_airbase'),
		);
	}
}
