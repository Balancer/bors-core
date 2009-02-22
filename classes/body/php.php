<?php

class body_php extends base_null
{
	function body($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;

		foreach($object->local_template_data_array() as $var => $value)
			$$var = $value;

		foreach($object->local_template_data_set() as $var => $value)
			$$var = $value;

		$self = $object;
		$tpl = preg_replace('!\.php$!', '.tpl.php', $object->class_file());
		ob_start();
		require($tpl);
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}
