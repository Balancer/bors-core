<?php

class bors_admin_cross_chtype extends bors_admin_page
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

	function title() { return ec('Смена типа привязки'); }
	function from() { return object_load(@$_GET['from']); }
	function from_internal_uri() { return $this->from() ? $this->from()->internal_uri_ascii() : @$_GET['from']; }
	function to() { return object_load(@$_GET['to']); }
	function to_internal_uri() { return $this->to() ? $this->to()->internal_uri_ascii() : @$_GET['to']; }

	function link() { $links = bors_link::links($this->from(), array('to' => $this->to())); return $links[0]; }

	function type_id() { return $this->link()->type_id(); }
	function sort_order() { return $this->link()->sort_order(); }
//	function sort_order() { return bors_cross_sort_order($this->from(), $this->to()); }

	function local_data()
	{
		return array(
			'type_id' => abs($this->type_id()),
		);
	}

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

	function on_action_chtype($data)
	{
		$from = $this->from();
		$to = $this->to();

		bors_link::link_objects($from, $to, array(
			'type_id' => $data['type_id'],
			'sort_order' => $data['sort_order'],
			'owner_id' => bors()->user_id(),
			'replace' => true,
		));

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
