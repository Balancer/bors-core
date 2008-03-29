<?php

class base_page_db extends base_page
{
	var $db;

	function can_be_empty() { return false; }

	function parents()
	{
//		echo "match=".print_r($this->match, true)."<br />\n";
		return array("http://{$this->match[1]}{$this->match[2]}");
	}

	function id_field() { return 'id'; }

	function new_instance()
	{
		$tab = $this->main_table_storage();
		if(!$tab)
			exit("Try to gent new instance with empty main table in class ".__FILE__.":".__LINE__);
		
		$this->db->insert($tab, array());
		$this->set_id($this->db->get_last_id());

		foreach($this->fields() as $db => $tables)
		{
			foreach($tables as $table => $fields)
			{
				echo "db=$table<br />";
				if(preg_match('!^inner\s+(.+?)$!', $table, $m))
					$table = $m[1];
			
			
				if($db == $this->main_db_storage() && $tab == $table)
					continue;
				
				foreach($fields as $property => $db_field)
				{
					if(is_numeric($property))
						$property = $db_field;
					
					if($property == 'id')
						$this->db($db)->insert($table, array($db_field => $this->id()));
				}
			}
		}

		$this->set_create_time(time(), true);
		$this->set_modify_time(time(), true);
		
	}

	function uri2id($id) { return $id; }
	
	function __construct($id)
	{
		$driver = $this->db_driver();
		if(!$this->main_db_storage())
			debug_exit('Empty '.$this->class_name().'.main_db_storage()');
			
		$this->db = &new $driver($this->main_db_storage());
		$id = $this->uri2id($id);
			
		parent::__construct($id);

	}

	function template_data_fill()
	{
		parent::template_data_fill();

		if(!($qlist = $this->_global_queries()))
			return;
				
		foreach($qlist as $qname => $q)
		{
			if(isset($GLOBALS['cms']['templates']['data'][$qname]))
				continue;
			
			$cache = NULL;
			if(preg_match("!^(.+)\|(\d+)$!s", $q, $m))
			{
				$q		= $m[1];
				$cache	= $m[2];
			}
					
			$GLOBALS['cms']['templates']['data'][$qname] = $this->db->get_array($q, false, $cache);
		}
	}

	function db_driver() { return 'driver_mysql'; }
	
	function edit_link() { return $this->uri."?edit"; }
	function storage_engine() { return 'storage_db_mysql'; }

	function fields() { return array($this->main_db_storage() => $this->main_db_fields()); }

	function main_db_fields()
	{
		return array(
			$this->main_table_storage() => $this->main_table_fields(),
		);
	}
	
	function fields_first() { return NULL; }

	function select($field, $where_map) { return $this->db->select($this->main_table_storage(), $field, $where_map); }
	function select_array($field, $where_map) { return $this->db->select_array($this->main_table_storage(), $field, $where_map); }

	function _global_queries() { return array(); }

	public function __wakeup()
	{
		$this->db = &new DataBase($this->main_db_storage());
	}
}
