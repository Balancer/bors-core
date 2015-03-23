<?php

class bors_forms_mod extends bors_forms_element
{
	function class_file()
	{
		$reflector = new ReflectionClass($this);
		return $reflector->getFileName();
	}

	function tpl_file() { return 'xfile:'.str_replace('.php', '.tpl', $this->class_file()); }

	function html()
	{
		return bors_templates_smarty::fetch($this->tpl_file(), $this->body_data());
	}

	function body_data()
	{
		return array();
	}
}
