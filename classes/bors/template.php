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

		return $this->fetch(self::find_template($template), $data);
	}

	static function find_template($template_name, $object = NULL)
	{
		$template_name = preg_replace('!^xfile:!', '', $template_name);
		foreach(bors_dirs(true) as $dir)
		{
			if(is_file($file = $dir.'/templates/'.$template_name))
				return 'xfile:'.$file;

			if(is_file($file = $dir.'/templates/'.$template_name.'/index.html'))
				return 'xfile:'.$file;
		}

		if($object)
		{
			$object_dirname = dirname($object->class_file());
			if(file_exists($file = $object_dirname.'/'.$template_name))
				return 'xfile:'.$file;

			$parent = get_parent_class($object);

			while($parent)
			{
				if(file_exists($file = dirname(bors_class_loader::load($parent)).'/'.$template_name))
					return 'xfile:'.$file;

				$parent = get_parent_class($parent);
			}
		}

		$trace = debug_backtrace();
		$called_file = @$trace[1]['file'];
		$called_dirname = dirname($called_file);
		if(file_exists($file = $called_dirname.'/'.$template_name))
			return 'xfile:'.$file;

		return $template_name;
	}

	function page_data()
	{
		$data = $GLOBALS['cms']['config'];
		if(!empty($GLOBALS['cms']['templates']['data']))
			$data = array_merge($data, $GLOBALS['cms']['templates']['data']);

		return $data;
	}
}
