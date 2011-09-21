<?php

class bors_tools_delete extends base_page
{
	function config_class() { return config('admin_config_class'); }
	function access() { return $this->object()->access(); }

	function editor() { return bors_load_uri($_GET['edit_class']); }

	function parents()
	{
		if(!empty($_GET['edit_class']))
			return array($_GET['edit_class']);

		$obj_admin = $this->object()->admin_url();
		return $obj_admin ? array($obj_admin) : array($this->object()->internal_uri());
	}

	function title() { return $this->object()->class_title() . ec(': подтверждение удаления'); }
	function nav_name() { return ec('удаление'); }

	function object() { return object_load($this->id()); }

	function pre_show()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());

		if(!method_exists($obj->access(), 'can_delete'))
			return bors_message(ec('Не определён доступ на удаление ').$obj->class_title_rp().' '.$obj->titled_url()."
				<!-- class_name = ".get_class($obj)."
				access = {$obj->access()}
				-->");

		if(!$obj->access()->can_delete())
			return bors_message(ec('Недостаточно прав для удаления ').$obj->class_title_rp().' '.$obj->titled_url()."
				<!-- class_name = ".get_class($obj)."
				access = {$obj->access()}
				-->");


		if($obj->get('can_delete_immediately'))
			return $this->on_action_delete();

		return false;
	}

	function on_action_delete()
	{
		$obj = $this->object();
		if(!$obj)
			return bors_message(ec('Не найден объект ').$this->id());

		if(!$obj->access()->can_delete())
			return bors_message(ec('Недостаточно прав для удаления ').$obj->class_title_rp().' '.$obj->titled_url());

		$obj->delete(!config('skip_remove_cross'));
		if($r = urldecode($_GET['ref']))
			return go($r);

		if($e = $this->editor())
			return go($e->url());

		return go(urldecode($_GET['ref']));
	}

	function url_engine() { return 'url_getp'; }

	function ref()
	{
		if(!empty($_GET['ref']))
			return $_GET['ref'];

		return @$_SERVER['HTTP_REFERER'];
	}

	function access_section() { return $this->object()->access_section(); }
}
