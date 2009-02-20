<?php

require_once('inc/bors/cross.php');

class base_object_db extends base_object
{
	function storage_engine() { return 'storage_db_mysql_smart'; }
	function db_driver() { return 'driver_mysql'; }
	function can_be_empty() { return false; }

	function uri2id($id) { return $id; }
	
	function __construct($id)
	{
		$id = $this->uri2id($id);
			
		parent::__construct($id);
//		bors_db_fields_init($this);
	}

	function id_field() { $fields = $this->main_table_fields(); return empty($fields['id']) ? 'id' : $fields['id']; }
	
	function new_instance() { bors_object_new_instance_db($this); }

	function select($field, $where_map) { return $this->db()->select($this->main_table_storage(), $field, $where_map); }
	function select_array($field, $where_map) { return $this->db()->select_array($this->main_table_storage(), $field, $where_map); }

	function fields() { return array($this->main_db_storage() => $this->main_db_fields()); }
	function main_db_fields()
	{
		return array(
			$this->main_table_storage() => $this->main_table_fields(),
		);
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

	function delete($remove_cross = true)
	{
		$tab = $this->main_table_storage();
		if(!$tab)
			debug_exit("Try to delete empty main table in class ".__FILE__.":".__LINE__);
		
		
		$id_field = $this->main_id_field();
		if(!$id_field)
			debug_exit("Try to delete empty id field in class ".__FILE__.":".__LINE__);
		
		if($remove_cross)
		{
			require_once('inc/bors/cross.php');
			bors_remove_cross_to($this->class_name(), $this->id());
		}
		
		if($this->id())
			$this->db()->delete($tab, array($id_field.'=' => $this->id()));
	}
}
