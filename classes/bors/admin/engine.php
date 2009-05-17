<?php

class bors_admin_engine extends base_empty
{
	function object() { return $this->id(); }
	function real_object() { return method_exists($this->object(), 'object') ? $this->object()->object() : $this->object(); }

	function edit_url()
	{
		if(method_exists($obj = $this->real_object(), 'edit_url'))
			return $obj->edit_url();

		return '/admin/edit-smart/?object='.urlencode($obj->internal_uri());
	}

	function append_child_url()
	{
		if(method_exists($obj = $this->real_object(), 'admin_append_child_url'))
			return $obj->admin_append_child_url();

		return '/_bors/admin/append/child?object='.urlencode($obj->internal_uri());
	}

	function property_url()
	{
		if(method_exists($obj = $this->real_object(), 'manage_url'))
			return $obj->property_url();

		return '/_bors/admin/property?object='.urlencode($obj->internal_uri());
	}

	function imaged_edit_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Редактировать ')
				.strtolower($obj->class_title_vp())
				.ec(' «').$obj->title().ec('»');

		$x = $title ? '&nbsp;' : '';
		$url = $this->edit_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->edit_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}<a href=\"{$url}\" title=\"$popup\">{$title}</a>";
		else
			return "<img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function imaged_append_child_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Добавить дочерний объект');

		$x = $title ? '&nbsp;' : '';
		$url = $this->append_child_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->append_child_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/new-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}<a href=\"{$url}\" title=\"$popup\">{$title}</a>";
		else
			return "<img src=\"/_bors/i/new-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function imaged_property_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Свойства ')
				.strtolower($obj->class_title_rp())
				.' '
				.$obj->title();

		$x = $title ? '&nbsp;' : '';
		$url = $this->property_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->property_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/property-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}<a href=\"{$url}\" title=\"$popup\">{$title}</a>";
		else
			return "<img src=\"/_bors/i/property-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}
}
