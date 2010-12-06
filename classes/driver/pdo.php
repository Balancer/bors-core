<?php

class driver_pdo
{
	private $connection = NULL;
	private $database = NULL;
	private $statement = NULL;
	private $row;

	function __construct($database = NULL)
	{
		if(empty($database))
			$database = config('pdo_db_default');

		$this->database = $database;

		$this->_reconnect();
	}

	static function dsn($db_name)
	{
		if($dsn = configh('pdo_access', $db_name, 'dsn'))
			return $dsn;

		return configh('pdo_access', $db_name, 'driver')
			.':dbname='.configh('pdo_access', $db_name, 'db_name', $db_name).
			';host='.configh('pdo_access', $db_name, 'host', '127.0.0.1').';';
	}

	private function _reconnect()
	{
		debug_timing_start('pdo_connect');

		$this->connection = new PDO(
			self::dsn($this->database),
			configh('pdo_access', $this->database, 'user'),
			configh('pdo_access', $this->database, 'password')
		);
		debug_timing_stop('pdo_connect');
	}

	function connection() { return $this->connection; }

	function query($query)
	{
		echo "query '$query'\n";
		debug_timing_start('pdo_query');
		$this->row = $this->connection->query($query);
		debug_timing_stop('pdo_query');

		$err = $this->connection->errorInfo();
		if($err[0] != 0)
			return bors_throw("PDO error on query «{$query}»: ".print_r($err, true));

		return $this->row;
	}

	function prepare($sql, $driver_options = array())
	{
		$this->statement = $this->connection->prepare($sql, $driver_options);
	}

	function execute($args)
	{
		$this->statement->execute($args);
	}

	function fetch()
	{
		debug_timing_start('pdo_fetch');
		$row = $this->row;

		$ics = config('internal_charset');
		$dcs = configh('pdo_access', $this->database, 'charset');

		if($row && $ics != $dcs)
		{
			$ics .= '//IGNORE';
			foreach($row as $k => $v)
				$row[$k] = iconv($dcs, $ics, $v);
		}

		debug_timing_stop('pdo_fetch');
		return $row;
	}

	function close() { }

	function select($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
//		echo $query."\n";
		$this->query($query);
		return $this->fetch();
	}

	function select_array($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
		$this->query($query);
		$data = array();
		while($row = $this->fetch())
		{
			$data[] = $row;
		}

		return $data;
	}

	function args_compile($where)
	{
		var_dump($where);
		return mysql_args_compile($where);
	}

	function make_string_values($array, $with_keys = true)
	{
		$values = array();
		if($with_keys)
		{
			$keys = array();
			foreach($array as $k => $v)
			{
				$this->normkeyval($k, $v);
				$keys[] = $k;
				$values[] = $v;
			}

			return " (".join(",", $keys).") VALUES (".join(",", $values).") ";
		}
		else
		{
			foreach($array as $k => $v)
			{
				$this->normkeyval($k, $v);
				$values[] = $v;
			}

			return " (".join(",", $values).") ";
		}
	}

	function make_string_set($array)
	{
		$set = array();

		foreach($array as $k => $v)
		{
			$this->normkeyval($k, $v);
			$set[] = "$k = $v";
		}
		return " SET ".join(",", $set)." ";
	}

	function normkeyval(&$key, &$value)
	{
		if($key[0] == 'i' && preg_match('!^int (.+)$!', $key, $m))
		{
			$key = ($m[1][0] == '`') ? $m[1] : '`'.$m[1].'`';
			return;
		}

		if(preg_match('/^(\S+) (.+?)$/', $key, $m))
		{
			$type = $m[1];
			$key = $m[2];
		}
		else
		{
			$value = is_null($value) ? "NULL" : $this->connection->quote($value);

			if($key[0] != '`')
				$key = "`$key`";

			return;
		}

		if($value === NULL)
			$value = "NULL";
		else
		{
			switch($type)
			{
				case 'raw':
					break;
				case 'float':
					$value = str_replace(',', '.', floatval($value));
					break;
				default:
					$value = $this->connection->quote($value);
			}
		}

		if($key[0] != '`')
			$key = "`$key`";
	}

	function insert($table, $fields)
	{
		echo "Insert:";
		var_dump($fields);
		$this->query("INSERT INTO $table ".$this->make_string_values($fields));
	}

	function update($table, $where, $fields)
	{
		$where['*set'] = $this->make_string_set($fields);
		return $this->query("UPDATE `".addslashes($table)."` ".$this->args_compile($where));
	}

	function last_id() { return $this->connection->lastInsertId(); }
}
