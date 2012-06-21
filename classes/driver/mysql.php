<?php

require_once('inc/mysql.php');
require_once('obsolete/DataBase.php');

class driver_mysql extends DataBase implements Iterator
{
	function connection() { return $this->dbh; }

	static function one($db) { return new driver_mysql($db); }
	static function factory($db) { return new driver_mysql($db); }

	private $where;
	private $table;
	function table($table_name) { $this->table = $table_name; return $this; }
	function order($order) { $this->where['order'] = $order; return $this; }
//	function where_is($field) { $this->where[] = $field; return $this; }
	function where($field, $val) { $this->where[$field] = $val; return $this; }

	function select($table, $field = NULL, $where_map = array(), $class_name = NULL)
	{
		$where_map['limit'] = 1;

		if($field === NULL)
		{
			$field = $table;
			$query = "SELECT $field FROM {$this->table} ".mysql_args_compile($this->where, $class_name);
			return $this->get($query);
		}

		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}

		return $this->get("SELECT $field FROM $table ".mysql_args_compile($where_map, $class_name));
	}

	function delete($table, $where)
	{
//		echo "DELETE FROM `".addslashes($table)."` ".mysql_where_compile($where)."<br/>\n";
		$this->query("DELETE FROM `".addslashes($table)."` ".mysql_args_compile($where));
	}

	function select_array($table, $field, $where_map, $class = NULL)
	{
		if(!is_array($where_map))
			echo debug_trace();

		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}

		$index_field = popval($where_map, '*select_index_field*');

		$query = "SELECT $field FROM $table ".mysql_args_compile($where_map, $class);
		return $this->get_array($query, false, false, $index_field);
	}

	function union_select_array($data)
	{
		// $data — массив, где каждый элемент — массив ($table, $fields, $where_array, [$class_name])
		$union = array();
		foreach($data as $x)
			$union[] = "SELECT {$x[1]} FROM {$x[0]} ".mysql_args_compile($x[2], @$x[3]);

		return $this->get_array(join(" UNION ", $union), false, false);
	}

	function update($table, $where, $fields)
	{
		$where['*set'] = $this->make_string_set($fields);
		return $this->query("UPDATE `".addslashes($table)."` ".mysql_args_compile($where));
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
//    	echo "$query\n";
		$this->query($query);
		$this->each_result = $this->result;
		return $this;
    }

    public function key() { } // Not Implemented

    public function current() { return $this->__current_value; }

    public function next() { return $this->fetch(); }

    public function rewind()
    {
		if(!$this->each_result)
			return false;

		@mysql_data_seek($this->each_result, 0);

        return $this->fetch();
    }

    public function valid() { return $this->row != false; }

	function estimated_count($table)
	{
		$x = $this->get("SHOW TABLE STATUS LIKE '".addslashes($table)."'");
		return $x['Rows'];
	}

	function escape($string) { return mysql_real_escape_string($string, $this->dbh); }
}
