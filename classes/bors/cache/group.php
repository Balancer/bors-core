<?php

class cache_group extends bors_object_db
{
	function db_name() { return config('cache_database'); }
	function table_name() { return 'cache_groups'; }
	function table_fields() { return array('id', 'cache_group', '_target_class_id', '_target_object_id', 'create_time'); }

	static function register($group, $obj)
	{
		$db = new driver_mysql(config('cache_database'));
		$db->replace('cache_groups', array(
			'cache_group' => $group,
			'_target_class_id' => $obj->class_name(),
			'_target_object_id' => $obj->id() ? $obj->id() : '',
			'create_time' => time(),
		));
	}

	function clean()
	{
		$list = $this->db()->select_array('cache_groups', '*', array('cache_group' => $this->cache_group()));
		$this->db()->query("DELETE FROM cache_groups WHERE cache_group = '".addslashes($this->cache_group())."'");
		foreach($list as $x)
			if($obj = bors_load($x['_target_class_id'], $x['_target_object_id']))
				$obj->cache_clean_register();
	}
}
