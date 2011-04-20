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

	function render_class() { return 'bors_render_page'; }
}
