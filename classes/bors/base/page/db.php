<?php

class base_page_db extends base_page
{
	function can_be_empty() { return false; }
	function can_cached() { return false; } //TODO: пока не разберусь, откуда глюки сохранения memcache

	function id_field()
	{
		$ff = method_exists($this, 'table_fields') ? $this->table_fields() : $this->fields_map();
		return defval($ff, 'id', 'id');
	}

	function new_instance() { bors_object_new_instance_db($this); }

	function uri2id($id) { return $id; }

	function __construct($id)
	{
		$id = $this->uri2id($id);

		parent::__construct($id);
//		if(config('strict_auto_fields_check'))
//			bors_db_fields_init($this);
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

			$GLOBALS['cms']['templates']['data'][$qname] = $this->db()->get_array($q, false, $cache);
		}
	}

	function db_driver() { return 'driver_mysql'; }

	function storage_engine() { return 'storage_db_mysql_smart'; }

	function fields_first() { return NULL; }

	function select($field, $where_map) { return $this->db()->select($this->table_name(), $field, $where_map); }
	function select_array($field, $where_map) { return $this->db()->select_array($this->table_name(), $field, $where_map); }

	function _global_queries() { return array(); }

	function fields_map_db() { return $this->fields(); }
	function fields() { return array($this->db_name(config('main_bors_db')) => array($this->table_name(bors_plural($this->class_name())) => $this->fields_map())); }

	function db_name($default = NULL) { return $this->main_db($default); }
	function main_db($default = NULL) { return $this->main_db_storage($default); }
	function main_db_storage($default = NULL) { return $default ? $default : array_shift(array_keys($this->fields_map_db())); }

	function table_name($default = NULL) { return $this->main_table($default); }
	function main_table($default = NULL) { return $this->main_table_storage($default); }
	function main_table_storage($default = NULL) { return $default ? $default : array_shift(array_keys(array_shift($this->fields_map_db()))); }

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
		if(method_exists($this, 'on_delete_pre'))
			if($this->on_delete_pre() === true)
				return true;

		$tab = $this->table_name();
		if(!$tab)
			debug_exit("Try to delete empty main table in class ".__FILE__.":".__LINE__);


		$id_field = $this->main_id_field();
		if(!$id_field)
			debug_exit("Try to delete empty id field in class ".__FILE__.":".__LINE__);

		if($remove_cross)
			bors_remove_cross_to($this->class_name(), $this->id());

		if($this->id())
			$this->db()->delete($tab, array($id_field.'=' => $this->id()));

		if(method_exists($this, 'on_delete_post'))
			if($this->on_delete_post() === true)
				return true;
	}

	function compiled_source() { return lcml($this->source()); }
	static function objects_array($where) { return objects_array($where); }
	static function objects_first($where) { return objects_first($where); }

	function table_fields() { return $this->fields_map(); }
}
