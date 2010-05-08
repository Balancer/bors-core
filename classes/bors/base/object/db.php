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
//		if(config('strict_auto_fields_check'))
//			bors_db_fields_init($this);
	}

	function fields_map_db() { return $this->fields(); }

	function fields() { return array($this->db_name(config('main_bors_db')) => array($this->table_name() => $this->fields_map())); }

	function db_name($default = NULL) { return $this->main_db($default); }
	function main_db($default = NULL) { return $this->main_db_storage($default); }
	function main_db_storage($default = NULL) { return $default ? $default : array_shift(array_keys($this->fields_map_db())); }

	function table_name() { return $this->main_table(); }
	function main_table() { return $this->main_table_storage(); }
	function main_table_storage() { return array_shift(array_keys(array_shift($this->fields_map_db()))); }

	function new_instance() { bors_object_new_instance_db($this); }

	function select($field, $where_map) { return $this->db()->select($this->table_name(), $field, $where_map); }
	function select_array($field, $where_map) { return $this->db()->select_array($this->table_name(), $field, $where_map); }

	function main_id_field()
	{
		$f = $this->fields_map_db();
		$f = $f[$this->db_name()];
		$f = $f[$this->table_name()];
		if($fid = @$f['id'])
			return $fid;
		if($f[0] == 'id')
			return 'id';

		return NULL;
	}

	function delete($remove_cross = true)
	{
		$tab = $this->table_name();
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

	static function objects_array($where) { return objects_array($where); }
	static function objects_first($where) { return objects_first($where); }
}
