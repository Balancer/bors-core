<?php

class bors_admin_meta_edit_parts extends bors_admin_meta_edit
{
	var $__skip_delete = true;

	function nav_name()
	{
		if($this->target())
			return preg_match('!/edit/?$!', $this->url()) ? ec('редактирование') : $this->target()->nav_name();

		return ec('добавление');
	}

	function title()
	{
		if($this->target())
			return ec('Редактирование ').bors_lib_object::get_foo($this->main_class(), 'class_title_rp') . ' ' . $this->target();

		return ec('Добавление ').bors_lib_object::get_foo($this->main_class(), 'class_title_rp');
	}

	function body_data()
	{
		$target = $this->target();
		$admin_target = $this->admin_target();

		$data = object_property($target, 'data', array());

		return array_merge(
			parent::body_data(),
			$data,
			array(
				$this->item_name() => $target,
				'view' => $this,
				'admin_'.$this->item_name() => $admin_target,
				'target' => $target,
				'admin_target' => $admin_target,
				'form_fields' => $this->edit_fields(),
				'step' => bors()->request()->data('step', 1),
			)
		);
	}
}
