<?php

class bors_page extends base_page
{
	function page_template_class() { return config('page_template_class', 'bors_templates_smarty'); }
	// Можно не указывать, если оно равно page_template_class
	function body_template_class() { return config('body_template_class', $this->page_template_class()); }

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
}
