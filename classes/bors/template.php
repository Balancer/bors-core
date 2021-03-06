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
		$original_template_name = $template_name;
		$template_name = preg_replace('!^xfile:!', '', $template_name);

		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name) && !is_dir($file))
				return 'xfile:'.$file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.tpl'))
				return 'xfile:'.$file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
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
				if(file_exists($file = dirname(bors_class_loader::load_file($parent)).'/'.$template_name))
					return 'xfile:'.$file;

				$parent = get_parent_class($parent);
			}
		}

		foreach(debug_backtrace() as $trace)
		{
			$called_file = @$trace['file'];
			$called_dirname = dirname($called_file);
			if($called_dirname && file_exists($file = $called_dirname.'/'.$template_name))
				return 'xfile:'.$file;
		}

		return $original_template_name;
	}

	static function page_data($args = NULL)
	{
		$data = array(
			'now' => $GLOBALS['now'],
		);

		if(is_array($args) && ($obj = @$args['this']))
			$object = $obj;
		else
			$object = NULL;

		if(!empty($GLOBALS['cms']['templates']['data']))
			$data = array_merge($data, $GLOBALS['cms']['templates']['data']);

		if(is_array($args))
			$data = array_merge($data, $args);

		return $data;
	}
}
