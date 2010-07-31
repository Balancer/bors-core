<?php

require_once('inc/mysql.php');
require_once('obsolete/DataBase.php');

class driver_mysql extends DataBase implements Iterator 
{
	static function one($db) { return new driver_mysql($db); }
	static function factory($db) { return new driver_mysql($db); }

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
		if(!is_array($where_map))
			echo debug_trace();

		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}

		return $this->get_array("SELECT $field FROM $table ".mysql_args_compile($where_map), false);
	}

	function update($table, $where, $fields)
	{
		return $this->query("UPDATE `".addslashes($table)."` ".$this->make_string_set($fields)." ".mysql_where_compile($where));
	}

/* $res = (new driver_mysql('BORS'))
		.from($table)
		.order('-create_time')
		.limit(10)
		.where('id>', 5)
		.where_is('is_published')
	.select($field);
*/

/*
	Реализация mysql-итератора. Использование:
		$dbh = new driver_mysql('BORS_HOME');
		$x = $dbh->each('bors_authors', 'id, last_name', array('id<' => 10));
		foreach($x as $r)
			print_r($r);
*/
	public function each($table, $fields, $where)
    {
    	$query = "SELECT $fields FROM {$table} ".mysql_args_compile($where);
    	echo "$query\n";
		$this->query($query);
		return $this;
    }

    public function key() { } // Not Implemented

    public function current() { return $this->__current_value; }

    public function next() { return $this->fetch(); }

    public function rewind()
    {
		mysql_data_seek($this->result, 0);
        return $this->fetch();
    }

    public function valid() { return $this->row != false; }
}
