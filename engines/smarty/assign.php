<?php

if(config('smarty3_enable'))
{
	debug_hidden_log('__obsolete', "Call obsolete smarty2");
	bors_throw(ec('[assign load] Попытка использования Smarty2 при активном Smarty3'));
}

function template_assign_data($assign_template, $data=array(), $uri=NULL, $caller=NULL)
{
	debug_timing_start('template_smarty_assign');

	unset($GLOBALS['module_data']);

	if(config('page_template_class') == 'bors_templates_smarty3')
		bors_throw(ec('[assign fetch] Попытка использования Smarty2 при активном Smarty3'));

	require_once(config('smarty_include'));

	$smarty = new Smarty;
	require('smarty-register.php');

	$smarty->compile_dir = config('cache_dir').'/smarty-e-templates_c';
	$smarty->compile_id = defval($data, 'compile_id');
	if(strlen($smarty->compile_id) > 128)
	{
		debug_hidden_log('need-attention', 'too long compile id: '.$smarty->compile_id);
		$smarty->compile_id = substr($smarty->compile_id, 0, 128);
	}

		$smarty->plugins_dir = array();
		foreach(bors_dirs(true) as $dir)
			$smarty->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$smarty->plugins_dir[] = 'plugins';

		$smarty->cache_dir   = config('cache_dir').'/smarty-e-cache/';

		if(!file_exists($smarty->compile_dir))
			@mkpath($smarty->compile_dir, 0777);
		if(!file_exists($smarty->cache_dir))
			@mkpath($smarty->cache_dir, 0777);

		$caching = !is_null($uri)
				&& @$data['caching'] !== false
				&& @$GLOBALS['cms']['templates_cache_disabled'] !== true
			;

		$smarty->caching = $caching;
		$smarty->compile_check = true;
		$smarty->php_handling = SMARTY_PHP_QUOTE; //SMARTY_PHP_PASSTHRU;
		$smarty->security = false;
		$smarty->cache_modified_check = true;
		$smarty->cache_lifetime = 86400*7;

		$template_uri = __template_assign_data_get_template($assign_template, $smarty, $data);

		$modify_time = empty($data['modify_time']) ? time() : $data['modify_time'];
		$modify_time = max(@$data['compile_time'], $modify_time);

		if(is_array(@$GLOBALS['cms']['smarty']))
			foreach($GLOBALS['cms']['smarty'] as $key => $val)
				$smarty->assign($key, $val);

		//TODO: убрать user_id и user_name в старых шаблонах.
		try { $me = bors()->user(); }
		catch(Exception $e) { $me = NULL; }
		$smarty->assign("me", $me);
		if($me)
		{
			$smarty->assign("my_id", $me->id());
			$smarty->assign("my_name", $me->title());
		}

//		if(!$caching || !$smarty->is_cached($template_uri, $uri))
//		{
			foreach($data as $key => $val)
			{
//				echo "$key -> ".print_d($val, true)."<br />\n";
				$$key = $val;
				$smarty->assign($key, $val);
			}

			$smarty->assign("page_template", $assign_template);
			$smarty->assign("template_uri", $template_uri);
			$dirname = dirname($template_uri);
			if(!preg_match("!^\w+:!", $dirname))
				$dirname = "xfile:$dirname";
			$smarty->assign("template_dirname", $dirname);
			$smarty->assign("time", time());

//			@header("X-Recompile4: Yes");
//		}

		$smarty->assign("uri", $uri);
		$smarty->assign("now", time());

		$smarty->assign("cms", $GLOBALS['cms']);

		if(empty($data['main_uri']))
			$smarty->assign("main_uri", @$GLOBALS['main_uri']);

		if(preg_match('!^http://!',$template_uri))
			$template_uri = "hts:".$template_uri;

		if(!empty($GLOBALS['cms']['templates']['data']))
            foreach($GLOBALS['cms']['templates']['data'] as $key => $value)
       	        $smarty->assign($key, $value);

		foreach(explode(' ', 'host_name main_host_uri') as $key)
			$smarty->assign($key, @$GLOBALS['cms'][$key]);

		debug_timing_stop('template_smarty_assign');
		debug_timing_start('template_smarty_assign_fill');

		if($obj = bors()->main_object())
		{
			$smarty->assign('bors_main_object', $obj);
			foreach(explode(' ', $obj->template_local_vars()) as $var)
				$smarty->assign($var, $obj->$var());

			$smarty->assign("this", $obj);
		}

		if(is_object(@$data['this']))
		{
			$obj = $data['this'];

			foreach(explode(' ', $obj->template_local_vars()) as $var)
				$smarty->assign($var, $obj->$var());

			$smarty->assign("this", $obj);

			foreach($obj->local_template_data_array() as $var => $value)
				$smarty->assign($var, $value);
		}

	debug_timing_stop('template_smarty_assign_fill');
	debug_timing_start('template_smarty_assign');

	if(preg_match('!^/!', $template_uri))
	{
		if(file_exists($template_uri))
			$template_uri = "xfile:".$template_uri;
		else
			$template_uri = "hts:http://{$_SERVER['HTTP_HOST']}$template_uri";
	}

	if(!$caching)
		$smarty->clear_cache($template_uri);

	debug_timing_stop('template_smarty_assign');
//	debug_timing_start('template_smarty_assign_fetch');
	$smarty->assign($data);
	$result = $smarty->fetch($template_uri);
//	debug_timing_stop('template_smarty_assign_fetch');
	return $result;
}

/*
	Допустимые форматы имён шаблонов:
	aviaport/common.html 	-> BORS_LOCAL/templates/aviaport/common.html
	blue_spring				-> BORS_SITE/templates/blue_spring/index.html
	page.html				-> classes/projest/page.html
	navleft.html			-> BORS_SITE/templates/Mesh/modules/design/navleft.html
*/

function __template_assign_data_get_template($assign_template, $smarty, $data)
{
	$wo_xfile_prefix = str_replace('xfile:', '', $assign_template);

	$caller_path = NULL;
	$trace = debug_backtrace();

	for($i=1, $stop=count($trace); $i<$stop; $i++)
	{
		$php_file_dir = dirname(@$trace[$i]['file']).'/';
		if(file_exists($php_file_dir.$wo_xfile_prefix))
		{
			$caller_path = $php_file_dir;
			break;
		}
	}

	$module_relative_path = NULL;
	foreach(bors_dirs(true) as $dir)
	{
		$dir = realpath($dir);
		if(file_exists($fn = "{$dir}/templates/$assign_template/index.html"))
			return "xfile:$fn";

		if(file_exists($fn = "{$dir}/templates/$wo_xfile_prefix/index.html"))
			return "xfile:$fn";

		$path = preg_replace('!^'.preg_quote($dir).'!', '', $caller_path);
		if($path != $caller_path)
		{
			$module_relative_path = $path;
			break;
		}
	}

	if(preg_match('/^[\w\.\-]+\.\w+$/', $assign_template))
		$assign_template = 'xfile:'.$assign_template;

	$template_uri = $assign_template;

	if($module_relative_path)
	{
			$assign_template_pure = str_replace('xfile:', '', $assign_template);
//			echo 'xfile:'.BORS_SITE.'/templates/'.config('default_template').$module_relative_path.'/'.$assign_template_pure;
			if($smarty->template_exists($tpl = 'xfile:'.BORS_SITE.'/templates/'.config('default_template').$module_relative_path.'/'.$assign_template_pure))
				$template_uri = $tpl;
			elseif($smarty->template_exists($tpl = 'xfile:'.BORS_SITE.$module_relative_path.'/'.$assign_template_pure))
				$template_uri = $tpl;
			else
				foreach(bors_dirs(true) as $dir)
				{
//					echo "Check ".'xfile:'.secure_path($dir.' --- /templates/ --- '.config('default_template').$module_relative_path.'/'.$assign_template)."<br/>";
					if($smarty->template_exists($tpl = 'xfile:'.secure_path($dir.'/templates/'.config('default_template').$module_relative_path.'/'.$assign_template_pure)))
						$template_uri = $tpl;
					elseif($smarty->template_exists($tpl = 'xfile:'.$dir.$module_relative_path.'/'.$assign_template_pure))
						$template_uri = $tpl;
				}
	}

	$caller_default_template = BORS_CORE.'/templates/'.$module_relative_path;

	$smarty->template_dir = $caller_path;
	if(!empty($data['template_dir']) && $data['template_dir'] != 'caller')
		$smarty->template_dir = $data['template_dir'];

	$smarty->secure_dir += array($caller_path, $caller_default_template);

	require_once('bors_smarty_common.php');

	if(!$smarty->template_exists($template_uri))
		bors_throw('Not found template '.$assign_template);

	if(!$smarty->template_exists($template_uri))
		$template_uri = $assign_template;

	if(!$smarty->template_exists($template_uri))
		$template_uri = config('default_template');

	if(!$smarty->template_exists($template_uri))
		$template_uri = smarty_template($template_uri);

	return $template_uri;
}
