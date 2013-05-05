<?php

bors_function_include('natural/bors_plural');
require_once('inc/bors/cross.php');

class base_object_db extends bors_object
{
	function can_cached() { return true; } //TODO: пока не разберусь, откуда глюки сохранения memcache

	function storage_engine() { return config('storage.default.class_name', 'bors_storage_mysql'); }

	function db_driver() { return 'driver_mysql'; }
	function can_be_empty() { return false; }

	function _db_name_def() { return config('main_bors_db'); }

	function uri2id($id) { return $id; }

	function __construct($id)
	{
		$id = $this->uri2id($id);

		parent::__construct($id);
//		if(config('strict_auto_fields_check'))
//			bors_db_fields_init($this);
	}

	function fields()
	{
//		if($this->storage_engine() != 'storage_db_mysql_smart')
		{
//			echo "{$this} ({$this->storage_engine()}) => ".$this->table_name(bors_plural($this->class_name()))."\n";
			return array(
				$this->db_name(config('main_bors_db')) => array(
					$this->table_name(bors_plural($this->class_name())) => $this->table_fields()
				)
			);
		}

		return array(
			$this->main_db(config('main_bors_db')) => array(
				$this->main_table(bors_plural($this->class_name())) => $this->main_table_fields()
			)
		);
	}

	function __db_name_def() { return $this->main_db(); }
	function main_db($default = NULL) { return $default ? $default : array_shift(array_keys($this->fields())); }

	function __table_name_def() { return $this->main_table(); }

	function main_table()
	{
		if($tab = $this->get('table_name', NULL, true))
			return $tab;

		return array_shift(array_keys(array_shift($this->fields())));
	}

	function new_instance() { bors_object_new_instance_db($this); }

	function select($field, $where_map) { return $this->db()->select($this->table_name(), $field, $where_map); }
	function select_array($field, $where_map) { return $this->db()->select_array($this->table_name(), $field, $where_map); }

	function table_fields() { return $this->fields_map(); }

	function main_id_field()
	{
//		if(method_exists($this, 'table_fields'))
//			return defval($this->table_fields(), 'id', 'id');

		$f = $this->fields();
		$f = $f[$this->db_name()];
		$f = $f[$this->table_name()];
		if($fid = @$f['id'])
			return $fid;
		if($f[0] == 'id')
			return 'id';

		return NULL;
	}

	function inner_join_fields() { return array(); }
	function left_join_fields()  { return array(); }

	function on_delete_pre() { return false; }

	function delete()
	{
		if($this->on_delete_pre() === true)
			return true;

		$tab = $this->table_name();
		if(!$tab)
			debug_exit("Try to delete empty main table in class ".__FILE__.":".__LINE__);


		$id_field = $this->id_field();
		if(!$id_field)
			debug_exit("Try to delete empty id field in class ".__FILE__.":".__LINE__);

		require_once('inc/bors/cross.php');
		bors_remove_cross_to($this->class_name(), $this->id());

		if($this->id())
			$this->db()->delete($tab, array($id_field.'=' => $this->id()));

		if(method_exists($this, 'on_delete_post'))
			if($this->on_delete_post() === true)
				return true;

		return parent::delete();
	}

	static function objects_array($where) { return objects_array($where); }
	static function objects_first($where) { return objects_first($where); }

	static function truncate($class_name)
	{
		$cls = new $class_name(NULL);
		$dbh = new driver_mysql($cls->db_name());
		$dbh->delete($cls->table_name(), array());
	}
}
