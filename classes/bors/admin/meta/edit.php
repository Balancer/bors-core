<?php

class bors_admin_meta_edit extends bors_admin_page
{
	function _config_class_def() { return config('admin_config_class'); }

	function _nav_name_def()
	{
		if($this->id()) // редактирование
			return preg_match('!/edit/?$!', $this->url()) ? ec('редактирование') : $this->target()->nav_name();

		return ec('добавление');
	}

	function _title_def()
	{
		return $this->id() ?
			ec('Редактирование ').bors_lib_object::get_foo($this->model_class(), 'class_title_rp')
			: ec('Добавление ').bors_lib_object::get_foo($this->model_class(), 'class_title_rp')
		;
	}

	function pre_show()
	{
		if($this->id() && !$this->target())
			return bors_throw("Can't load editor: have id ({$this->id()}) but have not target");

		return parent::pre_show();
	}

	function target()
	{
		if(!class_include($this->model_class()))
			return NULL;
//			bors_throw("Can't find main class '{$this->model_class()}'");

		return $this->id() ? bors_load($this->model_class(), $this->id()) : NULL;
	}

	function _main_class_def() { return $this->model_class(); }

	function _model_class_def()
	{
		static $enter = false;

		if($enter)
			return NULL;

		$enter = true;

		if($c = $this->main_class())
		{
			$enter = false;
			return $c;
		}

		$class_name = $this->class_name();
		$class_name = str_replace('_admin_', '_', $class_name);
		$class_name = str_replace('_edit', '', $class_name);

		$cn_test = blib_grammar::chunk_singular($class_name);

		$enter = false;

		if(class_exists($cn_test))
			return $cn_test;

		return blib_grammar::singular($class_name);
	}

	function _main_admin_class_def() { return $this->model_admin_class(); }

	function _model_admin_class_def()
	{
		static $enter = false;
		if($enter)
			return NULL;

		$enter = true;

		if($c = $this->main_admin_class())
		{
			$enter = false;
			return $c;
		}

		$project_class = preg_quote($this->project()->class_name(), '/');
		$admin_class_name = str_replace("/^({$project_class})_(.+)$/", "$1_admin_$2", $this->model_class());

		$enter = false;

		if(class_exists($admin_class_name))
			return $admin_class_name;

		bors_function_include('natural/bors_chunks_unplural');
		$class_name = str_replace('_edit', '', $this->class_name());
//		$admin_class_name = bors_chunks_unplural($class_name);
		$admin_class_name = $class_name;
		if(class_include($admin_class_name))
			return $admin_class_name;

		return $this->model_class();
	}

	function admin_target()
	{
		if(!$this->id())
			return NULL;

		if($this->__havefc())
			return $this->__lastc();

		if(method_exists($this->model_admin_class(), 'versioning_type'))
		{
//			var_dump(call_user_func(array($this->model_admin_class(), 'versioning_type')));
			switch(call_user_func(array($this->model_admin_class(), 'versioning_type')))
			{
				case 'premoderate':
					$obj = bors_objects_version::load($this->model_admin_class(), $this->id(), -1);
					if($obj && $obj->get('versioning_properties'))
						return $this->__setc($obj);

					return $this->__setc(bors_objects_version::load($this->model_class(), $this->id(), -1));
					break;
			}
		}

		return $this->__setc(bors_load($this->model_admin_class(), $this->id()));
	}

	function _item_name_def()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->model_class());
	}

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), array(
			$this->item_name() => $this->model_class().'(id)',
		));
	}

	function body_data()
	{
		if($this->id())
		{
			$data = object_property($this->target(), 'data', array());
			if(!is_array($data))
				$data = array();
		}
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
				set_session_var("form_value_".blib_grammar::singular($m[1]).'_id', $m[2]);
			}

		}

		$target = $this->id() ? $this->target() : NULL;
		$admin_target = $this->id() ? $this->admin_target() : NULL;

		$form_fields = ($f=$this->get('form')) ? $f : 'auto';
		if($ff = $this->get('form_fields'))
			$form_fields = $ff;

		if($form_fields == 'auto' && ($section = $this->get('admin_edit_section')) && $target)
		{
			$form_fields = bors_lib_orm::fields($target);
			$form_fields = array_filter($form_fields, function($x) use ($section) {
				if(!defval($x, "is_editable", true) && !defval($x, "is_admin_editable", false))
					return false;

				if(empty($x['admin_edit_section']) && $section == 'main')
					return true;

				if(($s = @$x['admin_edit_section']) == $section)
					return true;

				return false;
			});

			$form_fields = array_keys($form_fields);
		}

		return array_merge(
			$data,
			parent::body_data(),
			array(
				$this->item_name() => $target,
				'admin_'.$this->item_name() => $admin_target,
				'target' => $target,
				'admin_target' => $admin_target,
				'form_fields' => $form_fields,
			)
		);
	}

	// Куда переходим после сохранения нового объекта
	// По умолчанию — на редактирование этого же объекта
	function _go_new_url_def() { return 'newpage_admin'; }
	// Куда переходим после сохранения изменённого старого объекта
	// По умолчанию — на страницу-родителя
	function _go_edit_url_def() { return 'admin_parent'; }

	function submit_button_title() { return $this->id() ? ec('Сохранить') : ec('Добавить'); }

	function owner_id() { return object_property($this->target(), 'owner_id'); }

//	function parents() { return $this->admin_target() ? $this->admin_target()->parents() : parent::parents(); }
	function admin_parent_url() { return $this->admin_target() ? $this->admin_target()->admin_url() : parent::admin_parent_url(); }

	// Нельзя так: возможна ситуация, когда объект читать можно, а вот редактировать — нет. Тогда он будет показан!
//	function access() { return $this->admin_target() ? $this->admin_target()->access() : parent::access(); }
}
