<?php

class cache_group extends base_empty
{
	var $db;
	
	function __construct($group)
	{
		parent::__construct($group);
		$this->db = &new DataBase(config('cache_database'));
	}

	function register($obj)
	{
		$this->db->replace('cache_groups', array(
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
