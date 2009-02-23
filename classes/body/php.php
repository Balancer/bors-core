<?php

require_once('inc/bors/lists.php');
require_once('inc/bors/modules.php');

class body_php extends base_null
{
	function body($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;

		foreach(explode(' ', $object->template_local_vars()) as $var)
			$$var = $object->$var();
		
		foreach($object->local_template_data_array() as $var => $value)
			$$var = $value;

		foreach($object->local_template_data_set() as $var => $value)
			$$var = $value;

		$self = $object;
		$tpl = preg_replace('!\.php$!', '.tpl.php', $object->class_file());
		ob_start();
		$err_rep_save = error_reporting();
		error_reporting($err_rep_save & ~E_NOTICE);
		require($tpl);
		error_reporting($err_rep_save);
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}
