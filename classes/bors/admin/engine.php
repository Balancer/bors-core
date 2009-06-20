<?php

class bors_admin_engine extends base_empty
{
	function object() { return $this->id(); }
	function real_object() { return method_exists($this->object(), 'object') ? $this->object()->object() : $this->object(); }

	function edit_url()
	{
		if(method_exists($obj = $this->real_object(), 'edit_url'))
			return $obj->edit_url();

		return '/_bors/admin/edit-smart/?object='.urlencode($obj->internal_uri());
	}

	function delete_url()
	{
		if(method_exists($obj = $this->real_object(), 'delete_url'))
			return $obj->delete_url();

		if($obj->has_smart_field('is_deleted'))
			return '/_bors/admin/mark/delete/?object='.$obj->internal_uri().'&ref='.$obj->admin_parent_url(); 
		else
			return '/_bors/admin/delete/?object='.$obj->internal_uri().'&ref='.$obj->admin_parent_url(); 
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
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
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
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/new-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
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
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/property-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/property-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function hide_url()
	{
		if(method_exists($obj = $this->object(), 'hide_url'))
			return $obj->hide_url();

		return '/_bors/admin/visibility?act=' . ( $obj->is_hidden() ? 'show' : 'hide') . '&object='.urlencode($obj->internal_uri());
	}
	function imaged_hide_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->object();

		$full_title = ($obj->is_hidden() ? ec('Показать ') : ec('Скрыть '))
			.strtolower($obj->class_title())
			.' '
			.$obj->title();

		if(is_null($title))
			$title = $full_title;

		$x = $title ? '&nbsp;' : '';
		$url = $this->hide_url();

		if(is_null($popup))
			$popup = $full_title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->hide_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		$img = $obj->is_hidden() ? 'visible' : 'hidden';
		$alt = $obj->is_hidden() ? 'show' : 'hide';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/$img-16.gif\" width=\"16\" height=\"16\" alt=\"$alt\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/$img-16.gif\" width=\"16\" height=\"16\" alt=\"$alt\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function imaged_delete_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Удаление ')
				.strtolower($obj->class_title_rp())
				.' '
				.$obj->title();

		$x = $title ? '&nbsp;' : '';
		$url = $this->delete_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->delete_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/delete-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/delete-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function login_url() { return $this->real_object()->url().'?login'; }
	function imaged_login_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Вход в систему');

		$x = $title ? '&nbsp;' : '';
		$url = $this->login_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || preg_match('!\?login$!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/login-16.gif\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/login-16.gif\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function logout_url() { return $this->real_object()->url().'?logout'; }
	function imaged_logout_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		if(is_null($title))
			$title = ec('Выход из системы');

		$x = $title ? '&nbsp;' : '';
		$url = $this->logout_url();

		if(is_null($popup))
			$popup = $title;

		return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/logout-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
	}

}
