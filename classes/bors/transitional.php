<?php

class bors_transitional
{
	static function init()
	{
		static $inited = false;
		if($inited)
			return;

		$inited = true;

		if(!defined('COMPOSER_ROOT'))
			define('COMPOSER_ROOT', realpath(__DIR__.'/../../../../../'));

		if(!defined('BORS_CORE'))
			define('BORS_CORE', COMPOSER_ROOT.'/vendor/balancer/bors-core');

		require_once BORS_CORE.'/inc/functions/locale/ec.php';
		require_once BORS_CORE.'/engines/bors.php';
	}

	static function function_include($req_name)
	{
		static $defined = array();

		if(preg_match('!^(\w+)/(\w+)$!', $req_name, $m))
		{
			$path = $m[1];
			$name = $m[2];
		}
		else
		{
			$path = '';
			$name = $req_name;
		}

		if(!empty($defined[$req_name]))
			return;

		$defined[$req_name] = true;

		return require_once(__DIR__.'/../../inc/functions/'.$req_name.'.php');
	}
}

if(!function_exists('bors_url_map'))
{
	function bors_url_map($map_array)
	{
		if($router = @$GLOBALS['b2']['side']['router'])
		{
			$router->map_register($map_array);
			return;
		}

		global $bors_map;

		if(empty($bors_map))
			$bors_map = [];

		$bors_map = array_merge($bors_map, $map_array);
	}
}

function bors_use($uses)
{
	static $uses_active = array();
	foreach(explode(',', $uses) as $u)
	{
		$u = trim($u);

		if(in_array($u, $uses_active))
			continue;

		$uses_active[] = $u;

		if(preg_match('/\.css$/', $u))
		{
			if(preg_match('/^pre:(.+)$/', $u, $m))
				template_css($m[1], true);
			else
				template_css($u);

			continue;
		}

		if(preg_match('/\.js$/', $u))
		{
			// template_js_include()
			require_once BORS_CORE.'/engines/smarty/global.php';
			if(preg_match('/^pre:(.+)$/', $u, $m))
				template_js_include($m[1], true);
			else
				template_js_include($u);

			continue;
		}

		if(preg_match('/^\w+$/', $u))
		{
			if(function_exists($f = "bors_use_{$u}"))
			{
				call_user_func($f);
				continue;
			}

//			На будущее.
//			if(file_exists($file = BORS_CORE.DIRECTORY_SEPARATOR.'uses'.DIRECTORY_SEPARATOR.$u.'.php'))
//			{
//				require_once($file);
//				continue;
//			}

			if(preg_match('/^(\w+?)_(\w+)$/', $u, $m))
			{
				bors_function_include("{$m[1]}/{$m[2]}");
				continue;
			}
		}

		if(preg_match('!^(\w+/\w+)$!', $u))
		{
			bors_function_include($u);
			continue;
		}

		bors_throw("Unknown bors_use('$u')");
	}
}

function config_mysql($param_name, $db) { return @$GLOBALS["_bors_conf_mysql_{$db}_{$param_name}"]; }

function bors_vhost_routes($host, $routes)
{
	global $bors_data;
	$bors_data['vhosts'][$host]['bors_map'] = $routes;
}

/**
 * @param string $file
 * Загрузить .ini файл в параметры конфигурации.
 */
function bors_config_ini($file)
{
	if(!file_exists($file))
		return false;

	$ini_data = parse_ini_file($file, true);

	if($ini_data === false)
		bors_throw("'$file' parse error");

	foreach($ini_data as $section_name => $data)
	{
		if($section_name == 'global' || $section_name == 'config')
			$GLOBALS['cms']['config'] = array_merge($GLOBALS['cms']['config'], $data);
		else
			foreach($data as $key => $value)
				$GLOBALS['cms']['config'][$section_name.'.'.$key] = $value;
	}
}
