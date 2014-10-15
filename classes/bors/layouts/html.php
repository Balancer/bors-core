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
		{
			if(class_exists($mod_class = $class.'_'.$name))
				return bors_module::mod_html($mod_class, $args);
			else
			{
				$foo = new $class(NULL);
				if(!method_exists($foo, 'class_file'))
					break;

				$file = $foo->class_file();

				if(file_exists($tpl = str_replace('.php', "/$name.tpl.php", $file)))
					return bors_templaters_php::fetch($tpl, $args);

				if(file_exists($tpl = str_replace('.php', "/$name.tpl", $file)))
					return bors_templates_smarty::fetch('xfile:'.$tpl, $args);
			}
		}

		return NULL;
	}

	function forms_template_class() { return 'bors_forms_templates_default'; }
}
