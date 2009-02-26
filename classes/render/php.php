<?php

require_once('inc/bors/modules.php');

class render_php extends base_null
{
	function render($object)
	{
		debug_timing_start('render_global_php');

		if(!$object->loaded() && !$object->can_be_empty())
		{
			debug_timing_stop('render_global_php');
			return false;
		}

		foreach(explode(' ', $object->template_vars()) as $var)
			$$var = $object->$var();

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$$var = $object->$var();
		
		foreach($object->local_template_data_array() as $var => $value)
			$$var = $value;

		foreach($object->local_data() as $var => $value)
			$$var = $value;

		$tpl_file = false;
		foreach(bors_dirs() as $dir)
			if(file_exists($tpl_file = $dir.'/templates/'.$object->template()))
				break;
		
		if(!$tpl_file)
		{
			debug_timing_stop('render_global_php');
			return false;
		}
		
		$self = $object;
		ob_start();

		$err_rep_save = error_reporting();
		error_reporting($err_rep_save & ~E_NOTICE);
		require($tpl_file);
		error_reporting($err_rep_save);
		$result = ob_get_contents();
		ob_end_clean();
		
		debug_timing_stop('render_global_php');
		return $result;
	}
}
