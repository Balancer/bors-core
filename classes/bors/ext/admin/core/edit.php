<?php

class bors_ext_admin_core_edit extends base_page
{
	//TODO: добавить access
	function title() { return ec('Редактор класса ').$this->id(); }

	function body_engine() { return 'body_lcmlh'; }
	function acl_edit_sections() { return array('*' => 10); }

	function pre_show()
	{
		$this->class_path = class_include($this->id());
		return false;
	}

	function local_data()
	{
		$class_name = $this->id();
		return array(
			'class_path' => $this->class_path,
			'class' => new $class_name(NULL),
			'title' => $this->get_func_ret('title'),
			'class_title' => $this->get_func_ret('class_title'),
			'class_title_rp' => $this->get_func_ret('class_title_rp'),
			'main_db' => $this->get_func_ret('db_name'),
			'main_table' => $this->get_func_ret('table_name'),
		);
	}

	function target_source() { return file_get_contents($this->class_path); }

	function get_func_ret($func_name)
	{
//		print_d($this->target_source());
		if(preg_match('!function\s+'.preg_quote($func_name).'\(\)\s+\{\s+return\s+([^}]+)\s*;\s*\}!sx', $this->target_source(), $m))
			return $m[1];
		
		return false;
	}
}
