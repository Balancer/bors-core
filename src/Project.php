<?php

namespace B2;

class Project extends \bors_project
{
	static $routers = array();

	function regRouter($router_class, $base_url = '', $domain = '')
	{
		$router = call_user_func(array($router_class, 'factory'));
		$router->routes_init($base_url, $domain);
		self::$routers[$domain][] = $router;
		return $this;
	}
}
