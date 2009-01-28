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
	function from_internal_uri() { return $this->from() ? $this->from()->internal_uri() : @$_GET['from']; }
	function to() { return object_load(@$_GET['to']); }
	function to_internal_uri() { return $this->to() ? $this->to()->internal_uri() : @$_GET['to']; }

	function from_titled_url()
	{
		if($this->from())
			return "{$this->from()->class_title()} &laquo;{$this->from()->titled_url()}&raquo;";
		else
			return @$_GET['from'];
	}

	function to_titled_url()
	{
		if($this->to())
			return "{$this->to()->class_title()} &laquo;{$this->to()->titled_url()}&raquo;";
		else
			return @$_GET['to'];
	}


	function on_action_unlink()
	{
		$from = $this->from();
		$to = $this->to();

		if(is_object($to) && is_object($from))
			bors_remove_cross_pair($from->class_id(), $from->id(), $to->class_id(), $to->id());
		else
		{
			list($from_cid, $from_oid) = bors_parse_internal_uri(@$_GET['from']);
			list($to_cid, $to_oid) = bors_parse_internal_uri(@$_GET['to']);
			bors_remove_cross_pair($from_cid, $from_oid, $to_cid, $to_oid);
		}

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
