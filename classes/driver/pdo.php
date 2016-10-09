<?php

class driver_pdo implements Iterator
{
	protected $connection = NULL;
	protected $database = NULL;
	private $statement = NULL;

	static function factory($db) { $class = get_called_class(); return new $class($db); }

	function __construct($database = NULL)
	{
		$this->database = $database;

		$this->reconnect();
	}

	static function dsn($db_name)
	{
		// Если что-то типа sqlite:/path/to/db.sqlite, то имя БД уже содержит DSN
		if(preg_match('/^\w+:/', $db_name))
			return $db_name;

		if($dsn = configh('pdo_access', $db_name, 'dsn'))
			return $dsn;

		return configh('pdo_access', $db_name, 'driver')
			.':dbname='.configh('pdo_access', $db_name, 'db_name', $db_name).
			';host='.configh('pdo_access', $db_name, 'host', '127.0.0.1').';';
	}

	protected function reconnect()
	{
		bors_debug::timing_start('pdo_connect');

		$dsn = self::dsn($this->database);

		try
		{
			$this->connection = new PDO($dsn, configh('pdo_access', $this->database, 'user'), configh('pdo_access', $this->database, 'password'));
		}
		catch(Exception $e)
		{
			$msg = "PDO exception with $dsn: ".$e->getMessage();

			if(preg_match('/^(\w+):.+/', $dsn, $m) && preg_match('/could not find driver/', $e->getMessage()))
				$msg .= ". You need to install php-{$m[1]} driver?";

			throw new Exception($msg);
		}

		bors_debug::timing_stop('pdo_connect');
	}

	function connection() { return $this->connection; }

	function query($query, $ignore_error = false)
	{
		$start = time();
		bors_debug::timing_start('pdo_query');
		try
		{
			$result = $this->connection->query($query);
		}
		catch(Exception $e)
		{
			if($ignore_error)
				return $this->result = NULL;

			throw new Exception("PDO query exception for [".$query."]:".$e->getException());
		}

		bors_debug::timing_stop('pdo_query');
		$time = time() - $start;

		if($time > 3)
			bors_debug::syslog('warning-mysql-slow-query', "DB={$this->database}; time=$time; query=".$query);

		$err = $this->connection->errorInfo();
		if($err[0] == "00000")
			return $this->result = $result;

		if($ignore_error)
				return $this->result = NULL;

		throw new Exception("PDO error on query [{$query}]: ".print_r($err, true));
	}

	function exec($query)
	{
		bors_debug::timing_start('pdo_exec');
		$result = $this->connection->exec($query);
		bors_debug::timing_stop('pdo_exec');

		$err = $this->connection->errorInfo();
		if($err[0] != "00000")
			return bors_throw("PDO error on exec «{$query}»:\n".print_r($err, true));

		return $result;
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
		bors_debug::timing_start('pdo_fetch');
		$assoc = $this->result->fetch(PDO::FETCH_ASSOC);
		$ics = config('internal_charset');
		$dcs = configh('pdo_access', $this->database, 'charset');

		if($assoc && $ics != $dcs)
		{
			$ics .= '//IGNORE';
			foreach($assoc as $k => $v)
				$assoc[$k] = iconv($dcs, $ics, $v);
		}

		bors_debug::timing_stop('pdo_fetch');
		return $assoc;
	}

	function get($query)
	{
		$row = NULL;
		if($q = $this->connection->query($query))
		{
			foreach($q as $row)
				break;
		}
		else
			$row = NULL;

		$err = $this->connection->errorInfo();
		if($err[0] != "00000")
			// Can't use bors_throw() because need silently catch in callers.
			throw new Exception("PDO error ".print_r($err, true)." on query «{$query}»");

		if($row && count(array_keys($row)) == 2)
			$row = array_pop($row);

		return $row;
	}

	function get_array($query)
	{
		$ics = config('internal_charset');
		$icsi = $ics . '//IGNORE';
		$dcs = configh('pdo_access', $this->database, 'charset');

		$result = array();
		$res = $this->connection->query($query);

		$err = $this->connection->errorInfo();
		if($err[0] != "00000")
		{
			$msg = "PDO error\n".print_r($err, true)."\non query [{$query}]";
			bors_debug::syslog('error-pdo', $msg);
			throw new Exception($msg);
		}

		while($assoc = $res->fetch(PDO::FETCH_ASSOC))
			$result[] = $assoc;

		if(!empty($result[0]) && count(array_keys($result[0])) == 1)
		{
			$result_extracted = [];
			foreach($result as $r)
				$result_extracted[] = array_pop($r);
			return $result_extracted;
		}

		return $result;
	}

	function close()
	{
		if($this->connection)
			$this->connection = NULL;
	}

	// Прочитать одну строку или одно значение
	function select($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
		return $this->get($query);
	}

	function select_array($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
		return $this->get_array($query);
	}

	function args_compile($where)
	{
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
// Шит. Нельзя использовать prepare, так как передаваться могут готовые SQL-функции.
//		$keys = array_keys($fields);
//		$query = "INSERT INTO $table (".join(',', $keys).") VALUES (".join(',', array_map(create_function('$name', 'return ":$name";'), $keys)).")";
//		foreach($fields as $name => $value)
//			$this->connection->bindParam(":$name", );
//		$this->prepare($query);
//		$this->execute(array_values($fields));
//		echo "INSERT INTO $table ".$this->make_string_values($fields).PHP_EOL;
		$this->exec("INSERT INTO $table ".$this->make_string_values($fields));
	}

	function update($table, $where, $fields)
	{
		$where['*set'] = $this->make_string_set($fields);
		$query = "UPDATE `".addslashes($table)."` ".$this->args_compile($where);
//		echo "Update: $query\n";
		return $this->exec($query);
	}

	function last_id() { return $this->connection->lastInsertId(); }

	public function each($table, $fields, $where)
    {
    	$query = "SELECT $fields FROM {$table} ".$this->args_compile($where);
//    	echo "$query\n";
		$this->each_statement = $this->query($query);
		return $this;
    }

    public function rewind()
    {
    	if(!$this->each_statement)
    		return false;

		return $this->each_statement->fetch();
    }

    public function next() { return $this->each_statement->fetch(); }

    public function current() { bors_throw('Not implemented'); }
    public function key() { bors_throw('Not implemented'); }
    public function valid() { return bors_throw('Not implemented'); }

	public function set_skip_one_error() { $this->skip_errors++; }
}
