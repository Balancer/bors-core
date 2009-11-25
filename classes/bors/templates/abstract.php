<?php

class bors_templates_abstract extends base_null
{
	private $object;
	protected $template;
	protected $data = array();

//	function &data() { return $this->data; }

	function object() { return $this->object; }
	function set_object($object)     { return $this->object  = $object; }
	function set_template($template) { return $this->template = $template; }
	function set_data($data) { $this->data = $data; }

	function full_path()
	{
		$template_name = trim($this->template);

		if(!$template_name)
			$template_name = 'default';

		if(preg_match("!xfile:(/.+)$!", $template_name, $m))
			return $m[1];

		if(preg_match("!^\w+$!", $template_name))
			$template_name .= "/index.html";

//		if(@file_exists($file = dirname($caller_file) . '/' . $template_name))
//			return $file;

		foreach(bors_dirs(true) as $dir)
		{
			if(file_exists($file = $dir.'/templates/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/'.$template_name))
				return $file;

			if(file_exists($file = $dir.'/templates/'.$template_name.'/index.html'))
				return $file;
		}

		return false; // config('default_template', BORS_CORE.'/templates/default/index.html');

//		return '/var/www/bors/bors-core/templates/';
	}
}
