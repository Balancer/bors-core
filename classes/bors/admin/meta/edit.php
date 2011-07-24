<?php

class bors_admin_meta_edit extends bors_page
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

		return array_merge(
			$data,
			parent::body_data(),
			array(
				$this->item_name() => $this->id() ? $this->target() : NULL,
				'form_fields' => ($f=$this->get('form')) ? $f : 'auto',
			)
		);
	}
}
