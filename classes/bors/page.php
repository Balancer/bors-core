<?php

/**
	Основной класс простых двухкомпонентных (общая страница и её тело) HTML-страниц.
	Фактически обёртка над устаревшим base_page. Идёт процесс переноса функционала
	base_page в этот класс.

	• 22.09.2011 Реализовано smart-распознавание шаблонизаторов, PHamlP и чистого PHP.
*/

class bors_page extends base_page
{
	function page_template_class() { return config('page_template_class', 'bors_templates_smarty'); }
	// Можно не указывать, если оно равно page_template_class
	function body_template_class()
	{
		if($this->is_smart())
		{
			$this->__smart_body_template_check();
			if(!empty($this->attr['body_template_class']))
				return $this->attr['body_template_class'];
		}

		return config('body_template_class', $this->page_template_class());
	}

	function body_template()
	{
		if($this->is_smart())
		{
			$this->__smart_body_template_check();
			if(!empty($this->attr['body_template']))
				return $this->attr['body_template'];
		}

		return parent::body_template();
	}

	function __smart_body_template_check()
	{
		if(!empty($this->attr['__smart_body_template_checked']))
			return;

		$this->attr['__smart_body_template_checked'] = true;

		$current_class = get_class($this);
		$class_files = $GLOBALS['bors_data']['classes_included'];
		$ext = $this->body_template_ext();
		$is_smart = $this->is_smart();

		while($current_class)
		{
			$base = preg_replace("!(.+/\w+)\..+?$!", "$1.", $class_files[$current_class]);
			if($is_smart)
			{
				if(file_exists($bt = $base.'tpl.php'))
				{
					$this->attr['body_template'] = $bt;
					$this->attr['body_template_class'] = 'bors_templates_php';
					return;
				}
				if(file_exists($bt = $base.'haml') && class_exists('bors_templates_phaml'))
				{
					$this->attr['body_template'] = $bt;
					$this->attr['body_template_class'] = 'bors_templates_phaml';
					return;
				}
			}
			else
			{
				if(file_exists($template_file = $base.$ext))
					return "xfile:{$template_file}";
			}

			$current_class = get_parent_class($current_class);
		}
	}

	// Возвращает общий шаблон страницы
	//TODO: со временем перенести все упоминания из base_object. Оно не нужно для всех видов объектов.
	function page_template()
	{
		return $this->template(); // Пока, для совместимости, используем старый API.
//		return defval($this->data, 'template', defval($this->attr, 'template', config('default_template')));
	}

	function renderer_class() { return 'bors_renderers_page'; }

	function body_class() { return $this->body_engine(); }
	// Для совместимости
	function body_engine() { return 'bors_bodies_page'; }

	function is_smart() { return true; }
/*
	function body_template_ext()
	{
		if(config('is_developer')) echo preg_replace("!\.php$!", "$1.bbh", $this->class_file());
		if($this->is_smart() && file_exists(preg_replace("!\.php$!", "$1.bbh", $this->class_file()))
			return 'bbh';

		return parent::body_template_ext();
	}
*/
	function pre_show()
	{
		if($this->is_smart())
		{
			$class_file_base = str_replace('.php', '', $this->class_file());
			if(file_exists($f="$class_file_base.inc.css"))
				$this->add_template_data_array('style', ec(file_get_contents($f)));
			if(file_exists($f="$class_file_base.inc.js"))
				$this->add_template_data_array('javascript', ec(file_get_contents($f)));
		}

		return parent::pre_show();
	}

	static function object_type() { return 'page'; }

	function body_data()
	{
		$data = parent::body_data();
		if($bdc = $this->get('body_data_engine'))
		{
			$bde = bors_load($bdc, $this);
			return $bde->body_data($data);
		}

		return $data;
	}
}
