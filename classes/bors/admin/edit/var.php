<?php

class bors_admin_edit_var extends bors_admin_page
{
	function title() { return $this->variable()->title(); }

	function config_class() { return config('admin_config_class'); }

	function _template_def() { return 'xfile:default/popup.html'; }

	function variable()
	{
		$name = $this->id();
		$var = bors_var::get_var($name);

		if(!$var)
			$var = bors_var::set_var($name, NULL);

		return $var;
	}

	function pre_show()
	{
		if(!$this->variable())
			return bors_message(ec('Нет такой переменной'));

		if($this->variable()->type() == 'html')
			template_tinymce('textarea.tinymce', 'simple');

		return parent::pre_show();
	}

	function body_data()
	{
		return array(
			'var' => $this->variable(),
		);
	}

	function is_popup() { return bors()->request()->data('is_popup'); }

	function on_action_save($data)
	{
		$name = $data['name'];
		$var = bors_var::get_var($name);
		$var->set_value($data['value'], true);

		if(@$data['is_popup'])
		{
			echo "<script>window.close()</script>";
			return bors_message("Данные сохранены");
		}
	}
}
