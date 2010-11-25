<?php

class driver_oci
{
	private $connection = NULL;
	private $database = NULL;
	private $statement = NULL;

	private function _reconnect()
	{
		if($envs = configh('oci_access', $this->database, 'env'))
			foreach($envs as $e)
				putenv($e);

		debug_timing_start('oci_connect');
		$this->connection = oci_connect(
			configh('oci_access', $this->database, 'user'),
			configh('oci_access', $this->database, 'password'),
			configh('oci_access', $this->database, 'db')
		);
		debug_timing_stop('oci_connect');
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

		if($row && $ics != $dcs)
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
		$this->execute();
		return $this->fetch();
	}

	function select_array($table, $fields, $where)
	{
		$query = 'SELECT '.$fields.' FROM '.$table.' '.mysql_args_compile($where);
		$query = str_replace('`', '"', $query);
		$this->query($query);
		$this->execute();
		$data = array();
		while($row = $this->fetch())
		{
			$data[] = $row;
		}

		return $data;
	}
}
