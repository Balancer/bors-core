<?php

if(config('smarty3_enable'))
{
	eval('class bors_templates_smarty extends bors_templates_smarty3 { }');
	return;
}

class bors_templates_smarty extends bors_templates_abstract
{
	static function find_template($object, $template_name)
	{
		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
				return $file;
		}

		return $template;
	}

	static function fetch($template, $data)
	{
		require_once(config('smarty_include'));

		$smarty = new Smarty;
		require('smarty-register.php');

		$smarty->compile_dir = secure_path(config('cache_dir').'/smarty/templates_c/');
//		$smarty->use_sub_dirs = true;
		$smarty->plugins_dir = array();
		foreach(bors_dirs() as $dir)
			$smarty->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$smarty->plugins_dir[] = 'plugins';

		$smarty->cache_dir   = secure_path(config('cache_dir').'/smarty/cache/');

		if(!@file_exists($smarty->compile_dir))
		{
			@mkpath($smarty->compile_dir, 0777);
			@chmod($smarty->compile_dir, 0777);
		}

		if(!@file_exists($smarty->cache_dir))
		{
			@mkpath($smarty->cache_dir, 0777);
			@chmod($smarty->cache_dir, 0777);
		}

//		$caching = !$obj->is_cache_disabled() && config('templates_cache_disabled') !== true;

		$smarty->caching = false;// $caching;
		$smarty->compile_check = true; 
		$smarty->php_handling = SMARTY_PHP_QUOTE; //SMARTY_PHP_PASSTHRU;
		$smarty->security = false;
		$smarty->cache_modified_check = true;
		$smarty->cache_lifetime = 86400*7;

		if(!$smarty->template_exists($template))
			$template = smarty_template($template);

		if(!$smarty->template_exists($template))
			return "[2] Not existing template {$template}<br/>";

		$smarty->template_dir = dirname(preg_replace("!^xfile:!", "", $template));
		$smarty->assign("page_template", $template);
		$smarty->assign($data);

		$out = $smarty->fetch($template);

		$out = preg_replace("!<\?php(.+?)\?>!es", "do_php(stripq('$1'))", $out);

		return $out;
	}

	static function append_data($name, $value)
	{
		$data = base_object::template_data($name);
		$data[] = $value;
		base_object::add_template_data($name, $data);
	}

	static function form_hidden_data($name)
	{
		if($vars = base_object::template_data($name))
			return "<input type=\"hidden\" name=\"".str_replace('form_', '', $name)."\" value=\"".join(',', array_unique(array_filter($vars)))."\" />\n";

		base_object::add_template_data($name, NULL);
	}
}
