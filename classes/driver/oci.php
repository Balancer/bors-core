<?php

bors_function_include('debug/timing_start');
bors_function_include('debug/timing_stop');

class driver_oci implements Iterator
{
	static function factory($db) { return new driver_oci($db); }

	private $connection = NULL;
	private $database = NULL;
	private $statement = NULL;

	private function _reconnect()
	{
		if($envs = configh('oci_access', $this->database, 'env'))
			foreach($envs as $e)
				putenv($e);

/*		echo $this->database;
		echo configh('oci_access', $this->database, 'user');
		echo configh('oci_access', $this->database, 'password');
		echo configh('oci_access', $this->database, 'db');
		exit();
*/
		debug_timing_start('oci_connect');
//		echo "oci_connect(".configh('oci_access', $this->database, 'user').", ".configh('oci_access', $this->database, 'password').", ".configh('oci_access', $this->database, 'db').")";
		$this->connection = oci_connect(
			configh('oci_access', $this->database, 'user'),
			configh('oci_access', $this->database, 'password'),
			configh('oci_access', $this->database, 'db'),
			configh('oci_access', $this->database, 'oci_charset')
		);
		debug_timing_stop('oci_connect');

		if(!$this->connection)
		{
			$error = oci_error();
//			var_dump(oci_error());
//			print_d($error);
			bors_throw('oci_connection error: '.print_r($error, true));
		}
	}

	function __construct($database = NULL)
	{
		if(empty($database))
			$database = config('oci_db_default');

		$this->database = $database;

		$this->_reconnect();
	}

	function query($query)
	{
		debug_timing_start('oci_query');
		$this->statement = oci_parse($this->connection, $query);
		debug_timing_stop('oci_query');

		$this->execute();
	}

	function execute()
	{
		debug_timing_start('oci_execute');
		$success = oci_execute($this->statement, OCI_DEFAULT);
		if(!$success)
		{
			$error = oci_error($this->statement);
			if(@$error['sqltext'] && @$error['offset'])
				$error['error_in'] = substr($error['sqltext'], $error['offset']);
			bors_function_include('debug/print_d');
			print_d($error);
			bors_throw('oci_execute error: '.print_r($error, true));
		}
		debug_timing_stop('oci_execute');
	}

	function fetch()
	{
		debug_timing_start('oci_fetch');
		$row = oci_fetch_assoc($this->statement);
		debug_timing_stop('oci_fetch');

		$ics = config('internal_charset');
		$dcs = configh('oci_access', $this->database, 'charset');
//		echo "ics=$ics, dcs=$dcs\n";

		if($row && $dcs && ($ics != $dcs))
		{
			$ics .= '//IGNORE';
			foreach($row as $k => $v)
				$row[$k] = iconv($dcs, $ics, $v);
		}

		return $row;
	}

	function close() { }

	function select($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.mysql_args_compile($where);
		$query = str_replace('`', '"', $query);
//		echo $query."\n";
		$this->query($query);
		return $this->fetch();
	}

	function select_array($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.mysql_args_compile($where);
		$query = str_replace('`', '"', $query);
		$query = preg_replace('/"(\d+)"/', '$1', $query);
		$query = preg_replace("/'(\d+)'/", '$1', $query);
//		echo $query."\n";
		$this->query($query);
		$data = array();

		while($row = $this->fetch())
			$data[] = $row;

		return $data;
	}

	function get($query)
	{
		$this->query($query);
		$row = $this->fetch();
		if(count($row) == 1)
			foreach($row as $s) // Фактически это $row = array_pop(array_values($row)). Нужно будет поискать оптимальный вариант.
				$row = $s;

		return $row;
	}

	function get_all($query)
	{
		$this->query($query);
		return $this->fetch_all();
	}

	function fetch_all()
	{
		$data = array();
		while($row = $this->fetch())
		{
			$data[] = $row;
		}

		return $data;
	}

	private $current_row = NULL;

	public function each($table, $fields, $where)
    {
		$query = 'SELECT '.$fields.' FROM '.$table.' '.mysql_args_compile($where);
		$query = str_replace('`', '"', $query);
		$query = preg_replace('/"(\d+)"/', '$1', $query);
		$query = preg_replace("/'(\d+)'/", '$1', $query);
		$this->query($query);
		return $this;
    }

	// void Iterator::rewind ( void )
	// Rewinds back to the first element of the Iterator.
	// Any returned value is ignored.
	// Вызывается первый раз.
    public function rewind()
    {
    	$this->current_row = $this->fetch();
    }

	// void Iterator::next ( void )
	// Moves the current position to the next element.
	// Any returned value is ignored.
	// Вызывается после выдачи первого элемента, перед выдачей следующих
    public function next()
    {
    	$this->current_row = $this->fetch();
    }

	// boolean Iterator::valid ( void )
	// This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
	// The return value will be casted to boolean and then evaluated. Returns TRUE on success or FALSE on failure.
    public function valid()
    {
    	return (bool) $this->current_row;
    }

	// mixed Iterator::current ( void )
	// Returns the current element.
	// Can return any type.
    public function current()
    {
    	return $this->current_row;
    }

	// scalar Iterator::key ( void )
	// Returns the key of the current element.
	// Returns scalar on success, or NULL on failure.
    public function key() // Not implemented yet
    {
		return NULL;
    }
}
