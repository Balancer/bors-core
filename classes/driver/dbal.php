<?php

/**
	Драйвер для работы с DBAL Doctrine: http://www.doctrine-project.org/projects/dbal
	Внимание! Работает только с PHP 5.3.3+
*/


class driver_dbal
{
	private $connection = NULL;
	private $dbname = NULL;
	private $statement = NULL;

	static function register()
	{
		static $registered = false;
		if($registered)
			return;

//		use Doctrine\Common\ClassLoader;
		require config('doctrine.include') . '/Doctrine/Common/ClassLoader.php';
		$classLoader = new \Doctrine\Common\ClassLoader('Doctrine', config('doctrine.include'));
		$classLoader->register();
		$registered = true;
	}

	function __construct($dbname, $create_db = false)
	{
		$this->dbname = $dbname;
		$this->_reconnect($create_db);
	}

	private function _reconnect($create_db = false)
	{
		debug_timing_start('dbal_connect');

		$dbname = $this->dbname;

		$connectionParams = array(
			'driver'	=> configh('dbal', $dbname, 'driver'),
			'dbname'	=> configh('dbal', $dbname, 'dbname'),
			'path'		=> configh('dbal', $dbname, 'path'),
			'user'		=> configh('dbal', $dbname, 'user'),
			'password'	=> configh('dbal', $dbname, 'password'),
			'host'		=> configh('dbal', $dbname, 'host', 'localhost'),
		);

		$this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

		if($create_db)
		{
			$sm = $this->connection->getSchemaManager();
			$sm->createDatabase($connectionParams['dbname']);
		}

		debug_timing_stop('dbal_connect');
	}

	function connection() { return $this->connection; }

	function prepare($query) { $this->statement = $this->connection->prepare($query); }
	function execute() { $this->statement->execute(); }

	function query($query)
	{
//		echo "query '$query'\n";
		debug_timing_start('dbal_query');
//		$this->prepare($query);
//		$this->execute();
		$this->connection->executeQuery($query);
		debug_timing_stop('dbal_query');
	}

	function fetch()
	{
		debug_timing_start('dbal_fetch');
		$row = $this->statement->fetch();

		$ics = config('internal_charset');
		$dcs = configh('dbal', $this->database, 'charset');

		if($row && $ics != $dcs)
		{
			$ics .= '//IGNORE';
			foreach($row as $k => $v)
				$row[$k] = iconv($dcs, $ics, $v);
		}

		debug_timing_stop('dbal_fetch');
		return $row;
	}

	function close() { }

	function select($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
//		echo $query."\n";
		$this->prepare($query);
		$this->execute();
		return $this->fetch();
	}

	function select_array($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.$this->args_compile($where);
//		$query = str_replace('`', '"', $query);
		$data = array();
		$this->prepare($query);
		$this->execute();
		while($row = $this->fetch()) //TODO: Заменить на $statement->fetchAll() и разовую конвертацию.
			$data[] = $row;

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
//		echo "Insert:"; var_dump($fields);
		$this->connection->insert($table, $fields);
	}

	function update($table, $where, $fields)
	{
//		echo "update $table set ".print_r($fields, true)." where ".print_r($where, true)."\n";
//		$where['*set'] = $this->make_string_set($fields);
		unset($where['*class_name']);
		try
		{
			$this->connection->update($table, $fields, $where); //FIXME: посмотреть на тему преобразований where.
		}
		catch(Exception $e)
		{
			var_dump($e);
		}
	}

	function last_id() { return $this->connection->lastInsertId(); }
}

driver_dbal::register();
