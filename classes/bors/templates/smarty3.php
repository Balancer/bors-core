<?php

require_once(config('smarty3_include'));

class bors_templates_smarty3 extends bors_templates_meta
{
	function render_body($object)
	{
		$template = $object->body_template();
		$data = $object->data;

		foreach(explode(' ', $obj->template_local_vars()) as $var)
			$data[$var] = $obj->$var();

		return self::fetch($template, $data);
	}

	static function factory()
	{
		$smarty = new Smarty();

//		$smarty->template_dir = '/web/www.example.com/guestbook/templates/';
		$smarty->compile_dir= secure_path(config('cache_dir').'/smarty/templates_c/');
//		$smarty->config_dir   = '/web/www.example.com/guestbook/configs/';
		$smarty->cache_dir	= secure_path(config('cache_dir').'/smarty/cache/');

		return $smarty;
	}

	static function fetch($template, $data, $smarty = NULL)
	{
		if(!$smarty)
			$smarty = self::factory();

		$smarty->assign($data);

//		$smarty->debugging = true;
		return $smarty->fetch($template);
	}
}
