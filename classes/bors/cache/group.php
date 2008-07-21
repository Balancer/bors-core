<?php

class cache_group extends base_object_db
{
	function main_db_storage() { return config('cache_database'); }
	function main_table_storage() { return 'cache_groups'; }
	function main_table_fields() { return array('id', 'cache_group', 'object_class_id' => 'class_name', 'object_id' => 'class_id', 'create_time'); }

	function register($obj)
	{
		$this->db()->replace('cache_groups', array(
			'cache_group' => $this->id(),
			'class_name' => get_class($obj),
			'class_id' => $obj->id(),
			'create_time' => time(),
		));
	}

	function clean()
	{
		$list = $this->db->get_array('cache_groups', array('group='=>$this->id()));
		$this->db->query("DELETE FROM cache_groups WHERE cache_group = '".addslashes($this->id())."'");
		foreach($list as $x)
		{
			$obj = class_load($x['class_name'], $x['id']);
			$obj->cache_clean();
		}
	}
}
