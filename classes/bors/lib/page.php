<?php

class bors_lib_page
{
	static function body($object)
	{
		if(config('debug.execute_trace'))
			debug_execute_trace("{$object->debug_title_short()}->body() begin...");

		if($body = @$object->attr['body'])
			return $body;

		if($body_class_name = $object->body_class())
		{
			$body_engine = bors_load($body_class_name, NULL);

			if(!$body_engine)
				bors_throw("Can't load body engine '{$body_class_name}' for class {$object}");

			if(config('debug.execute_trace'))
				debug_execute_trace("Go ".get_class($body_engine)."->debug_title_short()->body(object)...");

			return $object->attr['body'] = $body_engine->body($object);
		}

		bors_throw("Not defined body engine for class '{$object}'");
	}

	static function smart_body_template_check($object, $suffix = '')
	{
		if(!empty($object->attr['__smart_body_template_checked'][$suffix]))
			return;

		$object->attr['__smart_body_template_checked'][$suffix] = true;

		$current_class = get_class($object);
		$class_files = $GLOBALS['bors_data']['classes_included'];
		$ext = $suffix ? $suffix : $object->body_template_ext();
		$is_smart = $suffix ? false : $object->is_smart();

		while($current_class)
		{
			$base = preg_replace("!(.+/\w+)\..+?$!", "$1.", $class_files[$current_class]);

			if($suffix)
			{
				if(file_exists($template_file = $base.$suffix))
				{
					$object->attr['body_template'] = $template_file;
					$object->attr['body_template_class'] = 'bors_templates_smarty';
					return;
				}
			}
			elseif($is_smart)
			{
				if(file_exists($bt = $base.'tpl.php'))
				{
					$object->attr['body_template'] = $bt;
					$object->attr['body_template_class'] = 'bors_templates_php';
					return;
				}
				if(file_exists($bt = $base.'haml') && class_exists('bors_templates_phaml'))
				{
					$object->attr['body_template'] = $bt;
					$object->attr['body_template_class'] = 'bors_templates_phaml';
					return;
				}
				if(file_exists($bt = $base.'html'))
				{
					$object->attr['body_template'] = $bt;
					$object->attr['body_template_class'] = 'bors_templates_smarty';
					return;
				}
				if(file_exists($bt = $base.$ext))
				{
					$object->attr['body_template'] = $bt;
					$object->attr['body_template_class'] = $object->body_template_class();
					return;
				}
			}
			else
			{
				if(file_exists($template_file = $base.$ext))
				{
					$object->attr['body_template'] = $template_file;
					$object->attr['body_template_class'] = 'bors_templates_smarty';
					return;
				}
			}

			$current_class = get_parent_class($current_class);
		}
	}
}
