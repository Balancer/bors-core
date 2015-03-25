<?php

class url_auto extends url_base
{
	function url_ex($page)
	{
		$obj = $this->id();
		$class_name = $obj->class_name();

		$path = NULL;
		if($routers = @$GLOBALS['bors_data']['routers'])
		{
//			var_dump($routers);
			foreach($routers as $base_url => $x)
			{
				$base_class = $x['base_class'];
				if(strpos($class_name, $base_class) === 0)
				{
					$class_base = $base_class.'_';
					$class_name = str_replace($base_class, '', $class_name);
					$path = $base_url . str_replace('_', '/', bors_plural($class_name)) . '/';
//					var_dump($base_class, $base_url, $class_name, $path);
				}
			}
		}

		$site_url = config('main_site_url');
		$class_name = $obj->class_name();
//		$admin_class_name = $obj->class_name();

		if(preg_match('/_admin_/', $class_name))
		{
			$site_url = config('admin_site_url');
//			$admin_class_name = $class_name;
//			$class_name = str_replace('_admin_', '_', $class_name);
		}

		if(!$path)
		{
			// aviaport_directory_airline -> directory_airline
			$rel_class_name = str_replace(config('classes_auto_base'), '', $class_name);
			// directory_airline -> /directory/airlines/
//			$path = str_replace('_', '/', bors_plural($rel_class_name)).'/';
			$rel_class_name = ltrim($rel_class_name, '_');

			$project_name = NULL;
			if($project = $obj->get('project'))
				$project_name = $project->get('class_prefix');

			if(!$project_name)
				$project_name = $obj->project_name();

//			echo $obj->debug_title().', /^'.$project_name.'_(\w+)$/i == '. $class_name.'<br/>';

			if(preg_match('/^'.$project_name.'_(\w+)$/i', $class_name, $m))
				return $obj->project()->url().'/'.join('/', array_map('bors_plural', explode('_', bors_lower($m[1])))).'/'.$obj->id().'/';

			$path = '/'.blib_grammar::plural($rel_class_name, '/').'/';
		}

		$path .= $obj->id().'/';

		if($page && ($page != $obj->default_page()))
			$path .= '/'.$page.'.html';

		return $path;
	}
}
