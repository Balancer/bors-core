<?php

class render_wordpress extends base_null
{
	function render($object)
	{
		debug_timing_start('render_global_wordpress');

		if(!$object->loaded() && !$object->can_be_empty())
		{
			debug_timing_stop('render_global_wordpress');
			return false;
		}

		$tpl_file = false;
		foreach(bors_dirs(true) as $dir)
			if(file_exists($tpl_file = $dir.'/templates/wordpress/'.$object->template().'/index.php'))
				break;

		if(!file_exists($tpl_file))
		{
			debug_timing_stop('render_global_wordpress');
			return false;
		}

		$object->set_template_wordpress_base_dir(dirname($tpl_file));
		global $wp_object, $wp_render;
		$wp_object = $object;
		$wp_render = $this;
		ob_start();

		$err_rep_save = error_reporting();
		error_reporting($err_rep_save & ~E_NOTICE);
		require_once('wordpress.inc.php');
		require($tpl_file);
		error_reporting($err_rep_save);
		$result = ob_get_contents();
		ob_end_clean();

		debug_timing_stop('render_global_wordpress');
		return $result;
	}
}
