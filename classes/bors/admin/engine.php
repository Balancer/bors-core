<?php

class bors_admin_engine extends bors_object
{
	function object() { return $this->id(); }
	function real_object()
	{
		$obj = $this->object();
		if($obj->class_name() != $obj->extends_class_name())
			$obj = object_property($this->object(), 'real_object', $this->object());

		return $obj;
	}

	function url($page = NULL)
	{
		$url = $this->admin_url();
		if($page && $page != 1)
			$url .= "$page/";

		return $url;
	}

	function admin_url()
	{
		$obj = $this->real_object();

		if($url = $obj->get('admin_url'))
			return $url;

		if($url = $obj->get('edit_url'))
			return $url;

		return '/_bors/admin/edit-smart/?object='.$obj->internal_uri_ascii();
	}

	function delete_url()
	{
		if(method_exists($obj = $this->real_object(), 'delete_url'))
			return $obj->delete_url();

		//TODO: придумать лучший вариант определения. Отказаться от has_smart_field.
		if(method_exists($obj, 'fields') && $obj->has_smart_field('is_deleted'))
			return '/_bors/admin/mark/delete/?object='.$obj->internal_uri().'&ref='.urlencode($obj->admin()->parent_delete_url());
		else
			return '/_bors/admin/delete/?object='.$obj->internal_uri().'&ref='.urlencode($obj->admin()->parent_delete_url());
	}

	function parent_delete_url()
	{
		$ps1 = object_property(bors_load_uri($this->admin_url()), 'parents');
		$p1 = $ps1[0];
		$ps2 = object_property(bors_load_uri($p1), 'parents');
		$p2 = $ps2[0];

		return $p2;
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

	function imaged_titled_link($title = NULL)
	{
		$obj = $this->real_object();
		if(is_null($title))
			$title = $obj->title();

		if(!$title)
			$title = ec('[без имени]');

		$res = "<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\">{$title}</a>";

		try
		{
			if($obj->url())
				$res .= "&nbsp;<a rel=\"nofollow\" href=\"{$obj->url()}\" target=\"_blank\"><img src=\"/_bors/i/look-16.gif\" width=\"16\" height=\"16\" alt=\"View\" title=\"".ec('Посмотреть на сайте')."\" style=\"vertical-align:middle\" /></a>";
		}
		catch(Exception $e) { }

		return $res;
	}

	function imaged_direct_titled_link($title = NULL)
	{
		$obj = $this->real_object();
		if(is_null($title))
			$title = $obj->title();

		if(!$title)
			$title = ec('[без имени]');

		$res = "<a rel=\"nofollow\" href=\"{$obj->url()}\">{$title}</a>";

		$popup = config('titles.imaged_direct_titled_link.popup', ec('Посмотреть на сайте'));

		try
		{
			//FIXME: подключить проверку доступа
			if($obj->url() && $obj->access()->can_edit())
				$res .= "&nbsp;<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\"><img src=\"/_bors/i16/edit.png\" width=\"16\" height=\"16\" alt=\"View\" title=\"".htmlspecialchars($popup)."\" style=\"vertical-align:middle\" /></a>";
		}
		catch(Exception $e) { }

		return $res;
	}

	function imaged_link($type, $image, $title=NULL)
	{
		require_once('inc/images.php');
		$url = $this->object()->urls($type);
		return bors_icon($image, array('url' => $url, 'title' => $title));
	}

	function titled_link($title = NULL)
	{
		$obj = $this->real_object();
		if(is_null($title))
			$title = $obj->title();
		return "<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\">{$title}</a>\n";
	}

	function imaged_nav_named_link()
	{
		$obj = $this->real_object();
		return "<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\">{$obj->nav_name()}</a>&nbsp;<a rel=\"nofollow\" href=\"{$obj->url()}\" target=\"_blank\"><img src=\"/_bors/i/look-16.gif\" width=\"16\" height=\"16\" alt=\"View\" title=\"".ec('Посмотреть на сайте')."\" style=\"vertical-align:middle\" /></a>";
	}

	function imaged_edit_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Редактировать ')
				.bors_lower($obj->class_title_vp())
				.ec(' «').$obj->title().ec('»');

		$x = $title ? '&nbsp;' : '';
		$url = $this->admin_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->admin_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/edit-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
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
			return "<a href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/new-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/new-16.png\" width=\"16\" height=\"16\" alt=\"edit\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function imaged_property_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		if(is_null($title))
			$title = ec('Свойства ')
				.bors_lower($obj->class_title_rp())
				.' '
				.$obj->title();

		$x = $title ? '&nbsp;' : '';
		$url = $this->property_url();

		if(is_null($popup))
			$popup = $title;

		if(!bors()->main_object() || $unlink_in_admin && preg_match('!'.preg_quote($obj->admin()->property_url(), '!').'!', bors()->main_object()->url()))
			$url = '';

		if($url)
			return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/property-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
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
			.bors_lower($obj->class_title())
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
			return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/$img-16.gif\" width=\"16\" height=\"16\" alt=\"$alt\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i/$img-16.gif\" width=\"16\" height=\"16\" alt=\"$alt\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
	}

	function imaged_delete_link($title = NULL, $popup = NULL, $unlink_in_admin = true)
	{
		$obj = $this->real_object();

		$delete_text = ec('Удаление ')
			.bors_lower($obj->class_title_rp())
			.ec(' «').$obj->get('title').ec('»');

		if($title === true)
			$title = $delete_text;

		$x = $title ? '&nbsp;' : '';
		$url = $this->delete_url();

		if(is_null($popup))
			$popup = $delete_text;

		if(!bors()->main_object() || 
			($unlink_in_admin 
				&& preg_match('!'.preg_quote($obj->admin()->delete_url(), '!').'!', 
					bors()->main_object()->url()))
		)
			$url = '';

		if($url)
			return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i16/delete.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
		else
			return "<img src=\"/_bors/i16/delete.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/>{$x}{$title}";
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
			return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/login-16.gif\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
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

		return "<a rel=\"nofollow\" href=\"{$url}\" style=\"text-decoration: none\"><img src=\"/_bors/i/logout-16.png\" width=\"16\" height=\"16\" alt=\"prop\" title=\"$popup\" style=\"vertical-align:middle\"/></a>{$x}".($title?"<a rel=\"nofollow\" href=\"{$url}\" title=\"$popup\">{$title}</a>":'');
	}

	function imaged_set_default_link($item, $title = NULL, $popup = NULL)
	{
		if(is_null($title))
			$title = ec('Сделать изображением по умолчанию');
		if(is_null($popup))
			$popup = ec('Сделать изображением по умолчанию');

		if($title)
			$title = "&nbsp;$title";

		return "<a rel=\"nofollow\" href=\"".$this->object()->setdefaultfor_url($item)."\"><img src=\"/_bors/i/set-default-16.gif\" width=\"16\" height=\"16\" alt=\"def\" title=\"$popup\"/>{$title}</a>";
	}

	function edit_links()
	{
		return "/admin/edit/crosslinks/?object={$this->real_object()->internal_uri()}&edit_class={$this->real_object()->admin()->url()}";
	}

	function urls($type = NULL)
	{
		$object = $this->object();

		if(method_exists($object, 'urls') && ($object_url = $object->urls($type)) && !is_object($object_url))
			return $object_url;

		switch($type)
		{
			case 'links':
				return config('admin_host_url')."/_bors/admin/edit/crosslinks/?real_object={$this->real_object()->internal_uri_ascii()}&object={$this->object()->internal_uri_ascii()}&edit_class={$this->real_object()->admin()->url()}";
			case 'synonyms':
				return config('admin_host_url')."/_bors/admin/edit/synonyms/?real_object={$this->real_object()->internal_uri_ascii()}&object={$this->object()->internal_uri_ascii()}&edit_class={$this->real_object()->admin()->url()}";
			case 'new':
				return config('admin_host_url')."/{$object->section_name()}/new/";
		}

		return $this->object()->admin()->url().$type.'/';
	}
}
