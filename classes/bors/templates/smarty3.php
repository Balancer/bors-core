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

		$data['this'] = $object;

		return self::fetch($template, $data);
	}

	static function factory()
	{
		if(!class_exists('Smarty'))
			bors_throw("Can't find Smarty. Please do \"composer require 'smarty/smarty=!=3.1.17'\"");

		$smarty = new Smarty();
//		require(__DIR__.'/smarty3-register.php');
		$smarty->registerResource('xfile', new bors_templates_smarty_resources_file($smarty));

		$smarty->compile_dir = config('cache_dir').'/smarty3-templates_c/';
		//TODO: придумать, как сделать разрешение для отдельных шаблонов.
		//		Хотя ниже есть вариант через данные шаблона.
		$smarty->auto_literal = true;
//		http://www.smarty.net/docs/en/variable.escape.html.tpl
//		This is a compile time option. If you change the setting you must make sure that the templates get recompiled.
//		$smarty->escape_html = true;
//		$smarty->escape_html = true; // config('smarty3.autoescape', true);

		$smarty->cache_dir = config('cache_dir').'/smarty3-cache/';

		if(!file_exists($smarty->compile_dir))
			mkpath($smarty->compile_dir, 0777);
		if(!file_exists($smarty->compile_dir))
			throw new Exception("Can't create templates cache dir: ".$smarty->compile_dir);
		if(!file_exists($smarty->cache_dir))
			mkpath($smarty->cache_dir, 0777);

//		$plugins_dir = array(BORS_3RD_PARTY.'/'.dirname(config('smarty.include')).'/plugins');
		$plugins_dir = array(COMPOSER_ROOT.'/vendor/smarty/smarty/libs/plugins');
		foreach(bors_dirs(true) as $dir)
			if(file_exists($d = $dir.'/engines/smarty/plugins'))
				$plugins_dir[] = $d;
//echo '.';
//echo '<xmp>'; var_dump($plugins_dir); echo '</xmp>';
		$smarty->setPluginsDir($plugins_dir);
//echo '<xmp>'; var_dump($smarty->getPluginsDir()); echo '</xmp>';

		$smarty->compile_check = true;

		$smarty->cache_modified_check = true;
		$smarty->cache_lifetime = 86400*7;

		return $smarty;
	}

	// Нужно запомнить и убедиться в унификации — обычно метод fetch()
	// не должен заниматься поиском нечётко заданного шаблона,
	// это задача вызывающего. Например, bors_template->render_page($template, $object);
	static function fetch($template, $data = array(), $smarty = NULL)
	{
//		if($template[0] == '/')
//			$template = 'xfile:'.$template;

//		PhpConsole\Handler::getInstance()->debug($template, 'tpl');
//		PhpConsole\Handler::getInstance()->debug($data, 'tpldata');

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
			if(!empty($trace[$i]['file']))
				$php_file_dir = dirname(@$trace[$i]['file']).'/';
			else
				$php_file_dir = '/unknown/';

			if(file_exists($php_file_dir.$wo_xfile_prefix))
			{
				$caller_path = $php_file_dir;
				break;
			}
		}

		$dirname = dirname($wo_xfile_prefix);
		if($dirname == '.' && ($object = @$data['this']))
			$dirname = dirname($object->real_class_file());

		if(!preg_match("!^\w+:!", $dirname))
			$dirname = "xfile:$dirname";
		if(!($dir_names = $smarty->getTemplateVars('template_dirnames')))
			$dir_names = array();
		array_unshift($dir_names, $dirname);
		array_unshift($dir_names, $caller_path);
		$smarty->assign("template_dirnames", $dir_names);

		$smarty->assign('me', bors()->user());
		$smarty->assign("main_uri", empty($GLOBALS['main_uri']) ? NULL : $GLOBALS['main_uri']);

		// Снести в пользу render_page(), наверное.
		if(!$smarty->templateExists($template))
			$template = self::find_template($template, @$data['this']);

//		$smarty->debugging = true;

		if(config('debug.execute_trace'))
			debug_execute_trace("smarty3->fetch()");

		$smarty->error_reporting = E_ALL & ~E_NOTICE;
		$result = $smarty->fetch($template);
		$dir_names = $smarty->getTemplateVars('template_dirnames');
		array_shift($dir_names);
		array_shift($dir_names);
		$smarty->assign("template_dirnames", $dir_names);
		return $result;
	}
}
