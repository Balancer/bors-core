<?php

namespace B2\Traits;

trait MySql
{
	function db($database_name = NULL)
	{
		if(empty($this->_dbh))
		{
			if(!$database_name)
				 $database_name = $this->get('db_name');
			if(!$database_name)
				 $database_name = config('main_bors_db');

			$this->_dbh = new \driver_mysql($database_name);
		}

		return $this->_dbh;
	}
}
