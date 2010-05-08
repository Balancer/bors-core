<?php

class base_cross extends base_page_db
{
	var $cross_ids  = NULL;
	var $cross_objs = NULL;

	function to_cross_ids()
	{
		if($this->cross_ids !== NULL)
			return $this->cross_ids;
			
		$this->cross_ids = $this->db()->get_array("
				SELECT `".$this->cross_storage_id_to()."`
					FROM `".$this->table_name()."`
					WHERE `".$this->cross_storage_id_from()."` = '".addslashes($this->id())."'");

		return $this->cross_ids;
	}

	function clean()
	{
		$this->db()->query("DELETE FROM `".$this->table_name()."` WHERE `".$this->cross_storage_id_from()."` = '".addslashes($this->id())."'");
	}

	function setup($to_id)
	{
		$this->db()->store($this->table_name(), 
				"`".$this->cross_storage_id_from()."` = '".addslashes($this->id()).
				"' AND `".$this->cross_storage_id_to()."` = '".addslashes($to_id)."'", array(
			$this->cross_storage_id_from() => $this->id(),
			$this->cross_storage_id_to() => $to_id,
		));
	}

	function to_objects_list()
	{
		if($this->cross_objs !== NULL)
			return $this->cross_objs;
			
		$this->cross_objs = array();
		
		foreach($this->to_cross_ids() as $obj_id)
		{
			$obj = class_load($this->cross_to_class(), $obj_id);
			if($obj)
				$this->cross_objs[] = $obj;
		}
		
		return $this->cross_objs;
	}
	
	function in_to_cross_ids($to_id)
	{
//		$GLOBALS['log_level'] = 10;
//		if($to_id == 154)
//			exit(print_r($this->to_cross_ids(), true));
		
		return in_array($to_id, $this->to_cross_ids());
	}

	function storage_engine() { return 'storage_db_mysql'; }
	function can_be_empty() { return true; }
}
