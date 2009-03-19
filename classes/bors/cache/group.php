<?php

class cache_group extends base_object_db
{
	function main_db_storage() { return config('cache_database'); }
	function main_table() { return 'cache_groups'; }
	function main_table_fields() { return array('id', 'cache_group', '_target_class_id', '_target_object_id', 'create_time'); }

	static function register($group, $obj)
	{
		$db = new driver_mysql(config('cache_database'));
		$db->replace('cache_groups', array(
			'cache_group' => $group,
			'_target_class_id' => $obj->class_id(),
			'_target_object_id' => intval($obj->id()),
			'create_time' => time(),
		));
	}

	function clean()
	{
		$list = $this->db()->select_array('cache_groups', '*', array('cache_group' => $this->cache_group()));
		$this->db()->query("DELETE FROM cache_groups WHERE cache_group = '".addslashes($this->id())."'");
		foreach($list as $x)
			if($obj = object_load($x['_target_class_id'], $x['_target_object_id']))
				$obj->cache_clean_self();
	}
}
