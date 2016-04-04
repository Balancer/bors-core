<?php

class driver_mysql extends driver_pdo implements Iterator
{
	var $charset = NULL;
	static $connections = [];

	function reconnect()
	{
		$this->close();

		$db_name = $this->database;

		$real_db  = config_mysql('db_real', $db_name);

		if(!empty(self::$connections[$real_db]))
			return $this->connection = self::$connections[$real_db];

		$server   = config_mysql('server', $db_name);
		$login    = config_mysql('login', $db_name);
		$password = config_mysql('password', $db_name);

		$dsn = "mysql:dbname=$real_db;host=$server;charset=utf8mb4";
		try
		{
			$this->connection = new PDO($dsn, $login, $password);
		}
		catch(Exception $e)
		{
			$msg = "PDO exception with $dsn: ".$e->getMessage();

			if(preg_match('/^(\w+):.+/', $dsn, $m) && preg_match('/could not find driver/', $e->getMessage()))
				$msg .= ". You need to install php-{$m[1]} driver?";

			throw new Exception($msg);
		}

		if($c = config('mysql_set_character_set', 'utf8mb4'))
		{
			bors_debug::timing_start('mysql_set_character_set');
			$this->query("SET CHARACTER SET '$c'");
			bors_debug::timing_stop('mysql_set_character_set');
		}

		if(($c = config('mysql_set_names_charset', 'utf8mb4')) && $this->charset != $c)
		{
			bors_debug::timing_start('mysql_set_names');
			$this->query("SET NAMES '$c'");
			$this->charset = $c;
			bors_debug::timing_stop('mysql_set_names');
		}

		self::$connections[$real_db] = $this->connection;
	}

	function connection() { return $this->connection; }

	static function one($db)     { $class = get_called_class(); return new $class($db); }
	static function factory($db) { $class = get_called_class(); return new $class($db); }

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

		$row = $this->get("SELECT $field FROM $table ".mysql_args_compile($where_map, $class_name));
		if(count($row) == 2)
			$row = $row[0];

		return $row;
	}

	function delete($table, $where)
	{
//		echo "DELETE FROM `".addslashes($table)."` ".mysql_where_compile($where)."<br/>\n";
		$this->query("DELETE FROM `".addslashes($table)."` ".mysql_args_compile($where));
	}

	function select_array($table, $field, $where_map, $class = NULL)
	{
		if(!is_array($where_map))
			echo bors_debug::trace();

		if(!empty($where_map['table']))
		{
			$table = $where_map['table'];
			unset($where_map['table']);
		}

		$index_field = popval($where_map, '*select_index_field*');

		$fake = popval($where_map, '*fake_select');

		$query = "SELECT $field FROM $table ".mysql_args_compile($where_map, $class);
		if($fake)
			return $this->query($query);

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

	function insert($table, $fields, $ignore_error = false)
	{
		if(!empty($fields['*DELAYED']))
		{
			unset($fields['*DELAYED']);
			$DELAYED="DELAYED ";
		}
		else
			$DELAYED="";

		$this->query("INSERT {$DELAYED}INTO $table ".$this->make_string_values($fields), $ignore_error);
	}

	function insert_ignore($table, $fields)
	{
		$this->query("INSERT IGNORE $table ".$this->make_string_values($fields));
	}

	function replace($table, $fields)
	{
		$this->query("REPLACE $table ".$this->make_string_values($fields));
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
		if(!@$this->each_result)
			return false;

//		@mysql_data_seek($this->each_result, 0);

        return $this->fetch();
    }

    public function valid() { return @$this->row != false; }

	function estimated_count($table)
	{
		$x = $this->get("SHOW TABLE STATUS LIKE '".addslashes($table)."'");
		return $x['Rows'];
	}

	//TODO: Change 'where' to array-type
	function store($table, $where, $fields, $append=false)
	{
		if(!$append)
			$n = $this->get("SELECT COUNT(*) FROM `".addslashes($table)."` WHERE $where");

		if(!$append && $n>0)
			$res = $this->query("UPDATE `".addslashes($table)."` ".$this->make_string_set($fields)." WHERE $where");
		else
			$res = $this->query("REPLACE INTO `".addslashes($table)."` ".$this->make_string_values($fields));

		if($res === false)
			throw new Exception("Invalid query: " . mysql_error($this->dbh) ." ");
	}
}
