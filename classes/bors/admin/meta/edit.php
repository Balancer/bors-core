<?php

class bors_admin_meta_edit extends bors_admin_page
{
	function config_class() { return config('admin_config_class'); }

	function nav_name() { return $this->id() ? $this->target()->nav_name() : ec('добавление'); }
	function title()
	{
		return $this->id() ?
			ec('Редактирование ').call_user_func(array($this->main_class(), 'class_title_rp'))
			: ec('Добавление ').call_user_func(array($this->main_class(), 'class_title_rp'))
		;
	}

	function target() { return $this->id() ? bors_load($this->main_class(), $this->id()) : NULL; }

	function main_class()
	{
		$class_name = str_replace('_admin_', '_', $this->class_name());
		$class_name = str_replace('_edit', '', $class_name);
		return bors_unplural($class_name);
	}

	function main_admin_class()
	{
		$class_name = str_replace('_edit', '', $this->class_name());
		$admin_class_name = bors_unplural($class_name);
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

	function body_data()
	{
		if($this->id())
			$data = object_property($this->target(), 'data', array());
		else
			$data = array();

		$target = $this->id() ? $this->target() : NULL;

		return array_merge(
			$data,
			parent::body_data(),
			array(
				$this->item_name() => $target,
				'target' => $target,
				'form_fields' => ($f=$this->get('form')) ? $f : 'auto',
			)
		);
	}

	// Куда переходим после сохранения нового объекта
	// По умолчанию — на редактирование этого же объекта
	function go_new_url() { return 'newpage_admin'; }
	// Куда переходим после сохранения изменённого старого объекта
	// По умолчанию — на страницу-родителя
	function go_edit_url() { return 'admin_parent'; }
}
