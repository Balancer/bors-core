<?php

/*
	Класс, выполняющий основные функции подготовки к выводу темы
*/

class bors_themes_meta extends bors_object
{
	var $__page_data = array();

	function object() { return $this->id(); }
	function render_class() { return 'self'; }

	function _page_data_def() { return array(); }

	function append_data($data = array())
	{
		$this->__page_data = array_merge($this->__page_data, $data);
	}

	function pre_show()
	{
		$body = $this->object()->body();
		$this->object()->template_data_fill();

		$this->__page_data = array_merge($this->__page_data, array(
			'self' => $this->object(),
			'body' => $body,
			'style' => array(),
			'javascript_post' => array(),
		), $this->page_data(), $this->object()->page_data());

		$this->object()->set_attr('layout_class', $this->get('layout_class'));
	}

	function template_files()
	{
		// Ищем файл .tpl.php рядом с .php файлом класса от текущего и вверх по родительским.
		$tpl_found = NULL;
		$css_found = array();
		$js_found = array();

		for($class_name = get_class($this); $class_name; $class_name = get_parent_class($class_name))
		{
			if(!($class_file = @$class_files[$class_name]))
			{
				$reflector = new ReflectionClass($class_name);
				$class_file = $reflector->getFileName();
			}

			if(!$tpl_found)
			{
				$template_file = str_replace('.php', '.tpl.php', $class_file);
				if(file_exists($template_file))
				{
					$templater = 'bors_templaters_php';
					$tpl_found = $template_file;
				}

				$template_file = str_replace('.php', '.tpl', $class_file);
				if(file_exists($template_file))
				{
					$templater = 'bors_templates_smarty';
					$tpl_found = $template_file;
				}
			}

			$css_file = str_replace('.php', '.inc.css', $class_file);
			if(file_exists($css_file))
				$css_found[] = $css_file;

			$js_file = str_replace('.php', '.inc.js', $class_file);
			if(file_exists($js_file))
				$js_found[] = $js_file;
		}

		return array('templater' => $templater, 'tpl' => $tpl_found, 'css' => $css_found, 'js' => $js_found);
	}

	function render()
	{
		$this->pre_show();
		$files = $this->template_files();

		if(!empty($files['css']))
		{
			foreach($files['css'] as $css)
				$this->__page_data['style'] = array_merge($this->__page_data['style'], array(file_get_contents($css)));
		}

		if(!empty($files['js']))
		{
			foreach($files['js'] as $js)
				$this->__page_data['javascript_post'] = array_merge($this->__page_data['javascript_post'], array(file_get_contents($js)));
		}

		$this->__page_data['this'] = $this->object();

		return call_user_func(array($files['templater'], 'fetch'), $files['tpl'], $this->__page_data);
	}
}
