<?php

class bors_templates_smarty3 extends bors_template
{
	function render_body($object)
	{
		$template = $object->body_template();
		$data = $object->data;

		foreach(explode(' ', $object->template_vars()) as $var)
			$$var = $object->$var();

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$data[$var] = $object->$var();

		$data = array_merge($data, $object->body_data());

		return self::fetch($template, $data);
	}

	static function factory()
	{
		require_once(config('smarty3_include'));
		$smarty = new Smarty();
		require('classes/bors/templates/smarty3-register.php');

//		$smarty->template_dir = '/web/www.example.com/guestbook/templates/';
//		$smarty->config_dir   = '/web/www.example.com/guestbook/configs/';

		$smarty->compile_dir = config('cache_dir').'/smarty3-templates_c/';
		$smarty->auto_literal = false; //TODO: придумать, как сделать разрешение для отдельных шаблонов.
/*
		$smarty->compile_id = defval($data, 'compile_id');
		if(strlen($smarty->compile_id) > 128)
		{
			debug_hidden_log('need-attention', 'too long compile id: '.$smarty->compile_id);
			$smarty->compile_id = substr($smarty->compile_id, 0, 128);
		}
*/
		$smarty->cache_dir = config('cache_dir').'/smarty3-cache/';

		if(!file_exists($smarty->compile_dir))
			mkpath($smarty->compile_dir, 0777);
		if(!file_exists($smarty->compile_dir))
			bors_throw(ec('Не могу создать каталог для компиляции шаблонов: ').$smarty->compile_dir);
		if(!file_exists($smarty->cache_dir))
			mkpath($smarty->cache_dir, 0777);

		$plugins_dir = array();
		foreach(bors_dirs(true) as $dir)
			$plugins_dir[] = $dir.'/engines/smarty/plugins';

		$smarty->setPluginsDir($plugins_dir);

		$smarty->compile_check = true;

//		$smarty->caching = true;
//		$smarty->compile_check = true;
//		$smarty->security = false;
		$smarty->cache_modified_check = true;
		$smarty->cache_lifetime = 86400*7;

		return $smarty;
	}

	// Нужно запомнить и убедиться в унификации — обычно метод fetch()
	// не должен заниматься поиском нечётко заданного шаблона,
	// это задача вызывающего. Например, bors_template->render_page($template, $object);
	static function fetch($template, $data = array(), $smarty = NULL)
	{
		if($template[0] == '/')
			$template = 'xfile:'.$template;

		if(!$smarty)
			$smarty = self::factory();

		$data = array_merge(bors_template::page_data(), $data);
		$smarty->auto_literal = popval($data, 'smarty_auto_literal', $smarty->auto_literal);
		$smarty->assign($data);
		$trace = debug_backtrace();

		$caller_path = NULL;
		$wo_xfile_prefix = str_replace('xfile:', '', $template);

		for($i=1, $stop=count($trace); $i<$stop; $i++)
		{
			$php_file_dir = dirname(@$trace[$i]['file']).'/';

			if(file_exists($php_file_dir.$wo_xfile_prefix))
			{
				$caller_path = $php_file_dir;
				break;
			}
		}

		$dirname = dirname($wo_xfile_prefix);
		if(!preg_match("!^\w+:!", $dirname))
			$dirname = "xfile:$dirname";
		if(!($dir_names = $smarty->getTemplateVars('template_dirnames')))
			$dir_names = array();
		array_unshift($dir_names, $dirname);
		array_unshift($dir_names, $caller_path);
		$smarty->assign("template_dirnames", $dir_names);

		$smarty->assign('me', bors()->user());
		$smarty->assign("main_uri", @$GLOBALS['main_uri']);

		// Снести в пользу render_page(), наверное.
		if(!$smarty->templateExists($template))
			$template = self::find_template($template, @$data['this']);

//		$smarty->debugging = true;

		if(config('debug.execute_trace'))
			debug_execute_trace("smarty3->fetch()");

		$smarty->error_reporting = E_ALL & ~E_NOTICE;
		return $smarty->fetch($template);
	}
}
