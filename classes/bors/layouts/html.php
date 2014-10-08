<?php

// Базовый, самый примитивный вариант элементов на голом HTML

class bors_layouts_html extends bors_object
{
	function object() { return $this->id(); }

	function mod($name, $args = array())
	{
		set_def($args, 'layout', $this);
		set_def($args, 'object', $this->object());

		for($class = get_class($this); $class; $class = get_parent_class($class))
			if(class_exists($mod_class = $class.'_'.$name))
				return bors_module::mod_html($mod_class, $args);

		return NULL;
	}
}
