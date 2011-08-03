<?php

class bors_template
{
	function render_page($object)
	{
		$template = $object->template();
		$data = $object->data;

		foreach(explode(' ', $object->template_vars()) as $var)
			$data[$var] = $object->$var();

		$data['this'] = $object;

		return $this->fetch($template, $data);
	}

	static function find_template($template_name, $object = NULL)
	{
//		echo "\nFind <b>$template_name</b> for '{$object}'".($object ? " defined in {$object->class_file()}" : '')."<br/>\n";
//		echo debug_trace();
//		echo $object->class_file();
		$template_name = preg_replace('!^xfile:!', '', $template_name);
		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
				return $file;
		}

		if($object)
		{
			$object_dirname = dirname($object->class_file());
			if(file_exists($file = $object_dirname.'/'.$template_name))
				return $file;

			$parent = get_parent_class($object);

			while($parent)
			{
				if(file_exists($file = dirname(class_include($parent)).'/'.$template_name))
					return $file;

				$parent = get_parent_class($parent);
			}
		}

		$trace = debug_backtrace();
		$called_file = @$trace[1]['file'];
		$called_dirname = dirname($called_file);
		if(file_exists($file = $called_dirname.'/'.$template_name))
			return $file;

		return $template_name;
	}
}
