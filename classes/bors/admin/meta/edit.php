<?php

class bors_admin_meta_edit extends bors_admin_page
{
	function can_be_empty() { return false; }
	function loaded() { return !$this->id() || $this->target(); }

	function _config_class_def() { return config('admin_config_class'); }

	function nav_name()
	{
		if($this->id()) // редактирование
			return preg_match('!/edit/?$!', $this->url()) ? ec('редактирование') : $this->target()->nav_name();

		return ec('добавление');
	}

	function title()
	{
		return $this->id() ?
			ec('Редактирование ').bors_lib_object::get_foo($this->main_class(), 'class_title_rp')
			: ec('Добавление ').bors_lib_object::get_foo($this->main_class(), 'class_title_rp')
		;
	}

	function target()
	{
		if(!class_include($this->main_class()))
			bors_throw("Can't find main class '{$this->main_class()}'");

		return $this->id() ? bors_load($this->main_class(), $this->id()) : NULL;
	}

	function main_class()
	{
		bors_function_include('natural/bors_chunks_unplural');
		$class_name = str_replace('_admin_', '_', $this->class_name());
		$class_name = str_replace('_edit', '', $class_name);
		return bors_chunks_unplural($class_name);
	}

	function main_admin_class()
	{
		bors_function_include('natural/bors_chunks_unplural');
		$class_name = str_replace('_edit', '', $this->class_name());
//		$admin_class_name = bors_chunks_unplural($class_name);
		$admin_class_name = $class_name;
		if(class_include($admin_class_name))
			return $admin_class_name;

		return $this->main_class();
	}

	function admin_target()
	{
		if(!$this->id())
			return NULL;

		if($this->__havefc())
			return $this->__lastc();

		if(method_exists($this->main_admin_class(), 'versioning_type'))
		{
//			var_dump(call_user_func(array($this->main_admin_class(), 'versioning_type')));
			switch(call_user_func(array($this->main_admin_class(), 'versioning_type')))
			{
				case 'premoderate':
					$obj = bors_objects_version::load($this->main_admin_class(), $this->id(), -1);
					if($obj && $obj->get('versioning_properties'))
						return $this->__setc($obj);

					return $this->__setc(bors_objects_version::load($this->main_class(), $this->id(), -1));
					break;
			}
		}

		return $this->__setc(bors_load($this->main_admin_class(), $this->id()));
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			$this->item_name() => $this->main_class().'(id)',
		));
	}

	function body_data()
	{
		if($this->id())
			$data = object_property($this->target(), 'data', array());
		else
		{
			$data = array();
			if($ref = bors()->request()->referer())
				$this->set_attr('go_new_url', $ref);
//var_dump($ref);
			if(preg_match('!/(\w+s)/(\d+)/?$!', $ref, $m)
				|| preg_match('!/(\w+s)/(\d+)/\w+/?$!', $ref, $m)
			)
			{
//				var_dump($m);
				set_session_var("form_value_".bors_unplural($m[1]).'_id', $m[2]);
			}

		}

		$target = $this->id() ? $this->target() : NULL;
		$admin_target = $this->id() ? $this->admin_target() : NULL;

		return array_merge(
			$data,
			parent::body_data(),
			array(
				$this->item_name() => $target,
				'admin_'.$this->item_name() => $admin_target,
				'target' => $target,
				'admin_target' => $admin_target,
				'form_fields' => ($f=$this->get('form')) ? $f : 'auto',
			)
		);
	}

	// Куда переходим после сохранения нового объекта
	// По умолчанию — на редактирование этого же объекта
	function _go_new_url_def() { return 'newpage_admin'; }
	// Куда переходим после сохранения изменённого старого объекта
	// По умолчанию — на страницу-родителя
	function go_edit_url() { return 'admin_parent'; }

	function owner_id() { return object_property($this->target(), 'owner_id'); }

//	function parents() { return $this->admin_target() ? $this->admin_target()->parents() : parent::parents(); }
	function admin_parent_url() { return $this->admin_target() ? $this->admin_target()->admin_url() : parent::admin_parent_url(); }

	// Нельзя так: возможна ситуация, когда объект читать можно, а вот редактировать — нет. Тогда он будет показан!
//	function access() { return $this->admin_target() ? $this->admin_target()->access() : parent::access(); }
}
