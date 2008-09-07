<?php

class render_php extends base_null
{
	function render($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;
			
		foreach($object->local_template_data_array() as $var => $value)
			$$var = &$value;
		
		$self = $object;
		ob_start();
		require(preg_replace('!\.php$!', '.tpl.php', $object->class_file()));
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}
