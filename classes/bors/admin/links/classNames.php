<?php

class bors_admin_links_classNames extends base_list
{
	function named_list()
	{
		$tmp = new bors_link(NULL);
		$db = new driver_mysql($tmp->db_name());

		$names = $db->select_array($tmp->table_name(), 'DISTINCT from_class', array());
		foreach($names as $class_id)
		{
			if(!$class_id)
				continue;

			$class_name = class_id_to_name($class_id);
			if(!preg_match('/^\w+$/', $class_name) || !class_include($class_name))
				continue;

			$class = new $class_name(NULL);

			$extends_class_name = $class->extends_class();
			if($extends_class_name != $class_name)
			{
				add_session_message(ec("Связь с подменяемым классом: {$class_name}($class_id) -> {$extends_class_name}"), array('type' => 'notice'));
				$class = new $extends_class_name(NULL);
				$class_name = $extends_class_name;
			}

			if($class_title = $class->class_title())
				$result[$class_name] = $class_title;
		}

		$result[''] = ec('- Любой -');
		asort($result);

		return $result;
	}
}
