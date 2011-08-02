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

	static function fetch($template, $data = array(), $smarty = NULL)
	{
		if(!$smarty)
			$smarty = self::factory();

		$smarty->assign($data);

		if(!$smarty->templateExists($template))
			$template = self::find_template($template, @$data['this']);

//		$smarty->debugging = true;
		$smarty->error_reporting = E_ALL & ~E_NOTICE;
		return $smarty->fetch($template);
	}

	static function find_template($template_name, $object = NULL)
	{
//		echo "\nFind $template_name for {$object} defined in {$object->class_file()}<br/>\n";
//		echo debug_trace();
//		echo $object->class_file();
		$template_name = preg_replace('!^xfile:!', '', $template_name);
		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
				return $file;
		}

		if($object)
		{
			$object_dirname = dirname($object->class_file());
			if(file_exists($file = $object_dirname.'/'.$template_name))
				return $file;

			$parent = get_parent_class($object);

			while($parent)
			{
				if(file_exists($file = dirname(class_include($parent)).'/'.$template_name))
					return $file;

				$parent = get_parent_class($object);
			}
		}

		$trace = debug_backtrace();
		$called_file = @$trace[1]['file'];
		$called_dirname = dirname($called_file);
		if(file_exists($file = $called_dirname.'/'.$template_name))
			return $file;

		return $template_name;
	}
}
