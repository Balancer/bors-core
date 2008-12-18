<?php

class driver_oci
{
	private $connection = NULL;
	private $database = NULL;
	private $statement = NULL;

	private function reconnect()
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
		
		$this->reconnect();
	}

	function query($query)
	{
//		$query = str_replace('`', '', $query);
//		$query = str_replace('\'', '', $query);
		debug_timing_start('oci_query');
		$this->statement = oci_parse($this->connection, $query);
		debug_timing_stop('oci_query');
	}
	
	function execute()
	{
		debug_timing_start('oci_execute');
		oci_execute($this->statement, OCI_DEFAULT);
		debug_timing_stop('oci_execute');
	}

	function fetch()
	{
		debug_timing_start('oci_fetch');
		$result = oci_fetch_assoc($this->statement);
		debug_timing_stop('oci_fetch');
		return $result;
	}
	
	function close() { }
}
