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

	function url() { return $this->url_ex($this->page()); }

	function url_ex($page)
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

	function edit_url()
	{
		$obj = $this->real_object();

		if($url = $obj->get('edit_url'))
			return $url;

		if($url = $obj->get('admin_url'))
			return $url;

		return '/_bors/admin/edit-smart/?object='.$obj->internal_uri_ascii();
	}

	function delete_url()
	{
		if(method_exists($obj = $this->real_object(), 'delete_url'))
			return $obj->delete_url();

		$ref = urlencode($obj->admin()->parent_delete_url());

		//TODO: придумать лучший вариант определения. Отказаться от has_smart_field.
		if(method_exists($obj, 'fields') && $obj->has_smart_field('is_deleted'))
			return '/_bors/admin/mark/delete/?object='.$obj->internal_uri().'&ref='.$ref;
		else
			return '/_bors/admin/delete/?object='.$obj->internal_uri().'&ref='.$ref;
	}

	function parent_delete_url()
	{
		$obj = $this->real_object();
		$obj_admin_url = $obj->admin()->url();

		$request_url = bors()->request()->url();
		// Хак для edit-smart: http://admin.aviaport.ru/_bors/admin/edit-smart/?object=aviaport_directory_airline_xref_plane__3
		if($x = $this->get('object'))
		{
			$obj = $x;
			$obj_admin_url = $obj->admin()->url();
			$request_url = $x->get('url');
		}

		// Если ссылка главного объекта не равна ссылке на удаляемый файл
		// то родительской страницей (реферером для возврата) является главная страница
		// Тестировать на: http://ipotek-bank.wrk.ru/admin/dbpages/10
//		var_dump($request_url, $obj->get('url'), $obj_admin_url);
		if(!blib_urls::in_array($request_url, array(
			$obj->get('url'),
			$obj_admin_url,
		)))
			return bors()->request()->url();

		// Если у объекта есть метод admin_parent_url, то берём оттуда:
		if($url = $obj->get('admin_parent_url'))
			return $url;

		// Иначе берём первого родителя из ссылки (если есть)
		$ps1 = object_property(bors_load_uri($obj_admin_url), 'parents');
		if($p1 = $ps1[0])
			return $p1;

//WTF?
//		$ps2 = object_property(bors_load_uri($p1), 'parents');
//		if($p2 = $ps2[0])
//			return $p2;

		// Иначе смотрим на реферер
		// Если он не равен текущей странице, то он нам и нужен
		if($ref = bors()->request()->referer())
		{
			if(!blib_urls::in_array($ref, array(
				$obj->url(),
				$obj->admin()->url(),
			)))
				return $ref;
		}

		return NULL;
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

		if($obj->access()->can_edit())
			$res = "<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\">{$title}</a>";
		else
			$res = "{$title}";

		try
		{
			if($obj->url())
				$res .= "&nbsp;<a rel=\"nofollow\" href=\"{$obj->url()}\" target=\"_blank\"><img src=\"/_bors/i/look-16.gif\" width=\"16\" height=\"16\" alt=\"View\" title=\"".ec('Посмотреть на сайте')."\" style=\"vertical-align:middle\" /></a>";
		}
		catch(Exception $e) { }

		return $res.$del;
	}

	function imaged_titled_link_ex($params)
	{
		if(is_array($params))
			$mode = popval($params, 'mode');
		else
		{
			$mode = $params;
			$params = array();
		}

		$obj = $this->real_object();

		if(array_key_exists('title', $params))
		{
			$title = defval($params, 'title');
		}
		else
		{
			$title = $obj->title();

			if(!$title)
				$title = ec('[без имени]');
		}

		if($title)
		{
			if($obj->access()->can_edit())
				$html = "<a rel=\"nofollow\" href=\"{$obj->admin()->url()}\">{$title}</a>";
			else
				$html = "{$title}";
		}

		try
		{
			if(stripos($mode, 'v') !== false && $obj->url())
				$html .= "&nbsp;<a rel=\"nofollow\" href=\"{$obj->url()}\" target=\"_blank\"><img src=\"/_bors/i/look-16.gif\" width=\"16\" height=\"16\" alt=\"View\" title=\"".ec('Посмотреть на сайте')."\" style=\"vertical-align:middle\" /></a>";
		}
		catch(Exception $e) { }

		if(stripos($mode, 'e') !== false && $obj->access()->can_delete())
			$html .= '&nbsp;' . $this->imaged_edit_link('');

		if(stripos($mode, 'd') !== false && $obj->access()->can_delete())
			$html .= '&nbsp;' . $this->imaged_delete_link('');

		return $html;
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
		$url = $this->edit_url();

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
		if(!$obj->access()->get('can_delete'))
			return '';

		// http://admin.aviaport.ru/digest/origins/3516/ — внизу страницы
		$delete_text = ec('Удаление ')
			.bors_lower($obj->class_title_rp())
			.ec(' «').$obj->get('title').ec('»');

		// Справа в http://admin.aviaport.ru/_bors/admin/edit/synonyms/?real_object=aviaport_directory_aviafirms_firm__1519&object=aviaport_admin_directory_aviafirms_firm__1519&edit_class=http://admin.aviaport.ru/directory/aviafirms/1519/
		if($title===false)
			$title = '';
		// Внизу http://admin.aviaport.ru/digest/origins/3516/
		elseif(is_null($title))
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
