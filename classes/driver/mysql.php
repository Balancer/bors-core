<?php

require_once('inc/mysql.php');
require_once('obsolete/DataBase.php');

class driver_mysql extends DataBase
{
	function select($table, $field, $where_map = array())
	{
		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}
	
		$where_map['limit'] = 1;
		return $this->get("SELECT $field FROM $table ".mysql_args_compile($where_map));
	}

	function delete($table, $where)
	{
		$this->query("DELETE FROM `".addslashes($table)."` ".mysql_where_compile($where));
	}

	function select_array($table, $field, $where_map)
	{
		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}

		return $this->get_array("SELECT $field FROM $table ".mysql_args_compile($where_map), false);
	}
}
