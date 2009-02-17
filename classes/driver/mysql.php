<?php

require_once('inc/mysql.php');
require_once('obsolete/DataBase.php');

class driver_mysql extends DataBase
{
	static function one($db) { return new driver_mysql($db); }

	private $where;
	private $table;
	function table($table_name) { $this->table = $table_name; return $this; }
	function order($order) { $this->where['order'] = $order; return $this; }
//	function where_is($field) { $this->where[] = $field; return $this; }
	function where($field, $val) { $this->where[$field] = $val; return $this; }

	function select($table, $field = NULL, $where_map = array())
	{
		$where_map['limit'] = 1;

		if($field === NULL)
		{
			$field = $table;
			return $this->get("SELECT $field FROM {$this->table} ".mysql_args_compile($this->where));
		}

		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}
	
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

/* $res = (new driver_mysql('BORS'))
		.from($table)
		.order('-create_time')
		.limit(10)
		.where('id>', 5)
		.where_is('is_published')
	.select($field);
*/
}
