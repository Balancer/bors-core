<?php

require_once('engines/lcml/main.php');

class body_lcmlh extends base_null
{
	function body($obj)
	{
		$data = array();

		//TODO: Вычистить все _queries.
		if($qlist = $obj->_queries())
		{
			$db = new DataBase($obj->db_name());

			foreach($qlist as $qname => $q)
			{
				$cache = false;
				if(preg_match("!^(.+)\|(\d+)$!s", $q, $m))
				{
					$q		= $m[1];
					$cache	= $m[2];
				}

				if(preg_match("/!(.+)$/s", $q, $m))
					$data[$qname] = $db->get($m[1], false, $cache);
				else
					$data[$qname] = $db->get_array($q, false, $cache);
			}
		}

		$data['template_dir'] = $obj->class_dir();
		$data['this'] = $obj;

		$obj->template_data_fill();
		require_once('engines/smarty/assign.php');
		return lcml_h(template_assign_data($obj->body_template(), $data));
	}
}
