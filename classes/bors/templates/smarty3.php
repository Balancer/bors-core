<?php

if(!config('smarty3_enable') && class_exists('bors_templates_smarty'))
{
	bors_throw(ec('Уже используется Smarty2. Использвание Smarty3 невозможно. Используйте config_set(\'smarty3_enable\', true);'));
}

class bors_templates_smarty3 extends bors_templates_meta
{
	function render_body($object)
	{
		$template = $object->body_template();
		$data = $object->data;

		foreach(explode(' ', $obj->template_vars()) as $var)
			$$var = $obj->$var();

		foreach(explode(' ', $obj->template_local_vars()) as $var)
			$data[$var] = $obj->$var();

		$data = array_merge($data, $obj->body_data());

		return self::fetch($template, $data);
	}

	static function factory()
	{
		require_once(config('smarty3_include'));
		$smarty = new Smarty();
		require('smarty3-register.php');

//		$smarty->template_dir = '/web/www.example.com/guestbook/templates/';
//		$smarty->config_dir   = '/web/www.example.com/guestbook/configs/';

		$smarty->compile_dir = secure_path(config('cache_dir').'/smarty3-templates_c_'.config('internal_charset').'/');
/*
		$smarty->compile_id = defval($data, 'compile_id');
		if(strlen($smarty->compile_id) > 128)
		{
			debug_hidden_log('need-attention', 'too long compile id: '.$smarty->compile_id);
			$smarty->compile_id = substr($smarty->compile_id, 0, 128);
		}
*/
		$smarty->cache_dir = secure_path(config('cache_dir').'/smarty3-cache/');

		if(!file_exists($smarty->compile_dir))
			mkpath($smarty->compile_dir, 0777);
		if(!file_exists($smarty->cache_dir))
			mkpath($smarty->cache_dir, 0777);

		$smarty->plugins_dir = array();
		foreach(bors_dirs(true) as $dir)
			$smarty->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$smarty->compile_check = true;

//		$smarty->caching = true;
//		$smarty->compile_check = true;
		$smarty->security = false;
		$smarty->cache_modified_check = true;
		$smarty->cache_lifetime = 86400*7;

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

	static function find_template($object, $template_name)
	{
		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
				return $file;
		}

		return $template_name;
	}
}
