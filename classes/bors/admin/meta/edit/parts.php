<?php

class bors_admin_meta_edit_parts extends bors_admin_meta_edit
{
	var $__skip_delete = true;

	function nav_name()
	{
		return preg_match('!/edit/?$!', $this->url()) ? ec('редактирование') : $this->target()->nav_name();
	}

	function title()
	{
		return ec('Редактирование ').bors_lib_object::get_foo($this->main_class(), 'class_title_rp') . ' ' . $this->target();
	}

	function body_data()
	{
		$target = $this->target();
		$admin_target = $this->admin_target();

		$data = object_property($target, 'data', array());

		return array_merge(
			$data,
			array(
				$this->item_name() => $target,
				'admin_'.$this->item_name() => $admin_target,
				'target' => $target,
				'admin_target' => $admin_target,
				'form_fields' => $this->edit_fields(),
			)
		);
	}
}
