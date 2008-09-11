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

	function new_instance() { bors_object_new_instance_db($this); }

	function uri2id($id) { return $id; }
	
	function __construct($id)
	{
		$driver = $this->db_driver();
		if(!$this->main_db_storage())
			debug_exit('Empty '.$this->class_name().'.main_db_storage()');
			
		$this->db = &new $driver($this->main_db_storage());
		$id = $this->uri2id($id);
			
		parent::__construct($id);
		bors_db_fields_init($this);
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
	
	function storage_engine() { return 'storage_db_mysql_smart'; }

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

	function main_id_field()
	{
		$f = $this->fields();
		$f = $f[$this->main_db_storage()];
		$f = $f[$this->main_table_storage()];
		if($fid = @$f['id'])
			return $fid;
		if($f[0] == 'id')
			return 'id';
		
		return NULL;
	}

	function delete()
	{
		$tab = $this->main_table_storage();
		if(!$tab)
			debug_exit("Try to delete empty main table in class ".__FILE__.":".__LINE__);
		
		
		$id_field = $this->main_id_field();
		if(!$id_field)
			debug_exit("Try to delete empty id field in class ".__FILE__.":".__LINE__);
		
		bors_remove_cross_to($this->class_name(), $this->id());
		$this->db()->delete($tab, array($id_field.'=' => $this->id()));
	}
}
