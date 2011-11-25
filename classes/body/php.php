<?php

require_once('inc/bors/lists.php');
require_once('inc/bors/modules.php');
bors_function_include('debug/timing');

class body_php extends base_null
{
	function body($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;

		debug_timing_start('body_php_body-'.$object->class_name());

		$object->template_data_fill();

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$$var = $object->$var();

		foreach($object->local_template_data_array() as $var => $value)
			$$var = $value;

		$self = $object;
		$ext = $object->body_template_ext();
		if(!$ext || $ext == 'html')
			$ext = 'tpl.php';

		$tpl = preg_replace('!\.php$!', '.'.$ext, $object->class_file());
		ob_start();
		$err_rep_save = error_reporting();
		error_reporting($err_rep_save & ~E_NOTICE);
		require($tpl);
		error_reporting($err_rep_save);
		$result = ob_get_contents();
		ob_end_clean();

		debug_timing_stop('body_php_body-'.$object->class_name());
		return $result;
	}
}
