<?php

class render_quicky extends base_null
{
	function render($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;

		require_once('quicky/Quicky.class.php');
		$tpl = new Quicky;
		$tpl->compiler_prefs['interpret_varname_params'] = true;
		$tpl->template_dir = dirname($object->class_file()).'/';

		foreach($object->local_template_data_array() as $var => $value)
			$tpl->assign($var, $value);

		foreach($object->local_data() as $var => $value)
			$tpl->assign($var, $value);

		$tpl->compile_dir = config('cache_dir').'/quicky-templates_c/';
//		$tpl->plugins_dir = array();
//		foreach(bors_dirs(true) as $dir)
//			$tpl->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$tpl->plugins_dir[] = 'plugins';
		$tpl->cache_dir   = config('cache_dir').'/quicky-cache/';

		return $tpl->fetch($object->template());
	}
}
