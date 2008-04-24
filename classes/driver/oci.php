<?php

class driver_oci
{
	private $connection = NULL;
	private $database = NULL;
	private $statement = NULL;

	private function reconnect()
	{
		$this->connection = oci_connect(
			configh('oci_access', $this->database, 'user'),
			configh('oci_access', $this->database, 'password'),
			configh('oci_access', $this->database, 'db')
		);
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
		echo "q=$query\n";
		$this->statement = oci_parse($this->connection, $query);
	}
	
	function execute()
	{
		oci_execute($this->statement, OCI_DEFAULT);
	}

	function fetch()
	{
		return oci_fetch_assoc($this->statement);
	}
	
	function close() { }
}
