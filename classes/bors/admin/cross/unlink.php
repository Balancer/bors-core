<?php

class bors_admin_cross_unlink extends base_page
{
	function config_class() { return config('admin_config_class'); }
	function parents()
	{
		$parents = array();
		if($this->from())
			$parents[] = $this->from()->admin_url();

		if($this->to())
			$parents[] = $this->to()->admin_url();
			
		return $parents;
	}
	function title() { return ec('снятие привязки'); }
	function from() { return object_load(@$_GET['from']); }
	function to() { return object_load(@$_GET['to']); }

	function on_action_unlink()
	{
		$from = $this->from();
		if(!$from)
			return bors_message(ec('Не найден объект ').$this->from());
		$to = $this->to();
		if(!$to)
			return bors_message(ec('Не найден объект ').$this->to());
		
//		set_loglevel(10);
		bors_remove_cross_pair($from->class_id(), $from->id(), $to->class_id(), $to->id());
//		debug_exit(0);
		return go($_GET['ref']);
	}

	function ref()
	{
		if(!empty($_GET['ref']))
			return $_GET['ref'];
			
		return @$_SERVER['HTTP_REFERER'];
	}

	function access_section() { return $this->object()->access_section(); }
}
