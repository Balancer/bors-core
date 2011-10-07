<?php

class bors_templates_smarty2 extends bors_templates_abstract
{
	// Нужно запомнить и убедиться в унификации — обычно метод fetch()
	// не должен заниматься поиском нечётко заданного шаблона,
	// это задача вызывающего. Например, bors_template->render_page($template, $object);
	static function fetch($template, $data)
	{
//		if(config('is_developer')) echo "bors_templates_smarty2::fetch($template)<br/>\n";

		require_once(config('smarty_include'));
		require_once('engines/smarty/bors_smarty_common.php');

		$smarty = new Smarty;
		require('smarty-register.php');

		$smarty->compile_dir = config('cache_dir').'/smarty2-templates_c/';
//		$smarty->use_sub_dirs = true;
		$smarty->plugins_dir = array();
		foreach(bors_dirs() as $dir)
			$smarty->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$smarty->plugins_dir[] = 'plugins';

		$smarty->cache_dir   = config('cache_dir').'/smarty2-cache/';

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
			$template = self::find_template($template, @$data['this']);

		if(!$smarty->template_exists($template))
			$template = smarty_template($template);

		if(!$smarty->template_exists($template))
			return "[2] Not existing template {$template}<br/>";


		$smarty->template_dir = dirname(preg_replace("!^xfile:!", "", $template));
		$smarty->assign("page_template", $template);
		$smarty->assign('me', bors()->user());
//		if(config('is_developer')) echo debug_trace();
		$smarty->assign(bors_template::page_data($data));

		$out = $smarty->fetch($template);

//		print_d($out);
		$out = preg_replace("!<\?php(.+?)\?>!es", "do_php(stripq('$1'))", $out);

		return $out;
	}
}
