<?php

class base_tree extends base_page_db
{
	function all_ids()
	{
		$result = array();
		
		foreach($this->db()->get_array("
				SELECT `".$this->tree_table_id()."`
					FROM `".$this->main_table()."`
					ORDER BY ".$this->tree_order()) as $obj_id)
		{
			$result[] = $obj_id;
		}
		
		return $result;
	}

	function root_ids()
	{
		$result = array();
		
		foreach($this->db()->get_array("
				SELECT `".$this->tree_table_id()."`
					FROM `".$this->main_table()."`
						WHERE `".$this->tree_parent_id()."` = 0
					ORDER BY ".$this->tree_order()) as $obj_id)
		{
			$result[] = $obj_id;
		}
		
		return $result;
	}

	private $tree = NULL;
	private $names = NULL;

	private function db_load()
	{
		$this->tree = array();
		$this->names = array();
		
		foreach($this->db()->get_array("
				SELECT `".$this->tree_table_id()."` AS `id`, `".$this->tree_table_title()."` AS `title`, `".$this->tree_parent_id()."` AS `parent`
					FROM `".$this->main_table()."`
					ORDER BY ".$this->tree_order()) as $x)
		{
			$this->tree[$x['parent']][] = $x;
			$this->names[$x['id']] = $x['name'];
		}
	}

	function all_tree()
	{
		if($this->tree == NULL)
			$this->db_load();

		
		return $this->tree;
	}

	function id_to_name($id)
	{
		if($this->names == NULL)
			$this->db_load();

		
		return @$this->names[$id];
	}

	function children_ids()
	{
		$result = array();
		
		foreach($this->db()->get_array("
				SELECT `".$this->tree_table_id()."`
					FROM `".$this->main_table()."`
						WHERE `".$this->tree_parent_id()."` = ".$this->id()."
					ORDER BY ".$this->tree_order()) as $obj_id)
		{
			$result[] = $obj_id;
		}
		
		return $result;
	}
	
	function children_subs_ids($self = false)
	{
		$result = $self ? array($this->id()) : array();

		foreach($this->children_ids() as $chid)
			$result = array_merge($result, class_load(get_class($this), $chid)->children_subs_ids(true));
			
		return $result;
	}
	
	function is_root() { return !$this->parent_id(); }
	function have_children() { return count($this->children_ids()) > 0; }
	function have_parent() { return $this->parent_id(); }
}
