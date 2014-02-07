<?php

/*
	Класс текущего шаблона.
	Объект доступен внутри шаблонов в переменной $template
	Рыба. Пока принудительная загрузка в:
		— forexpf_admin_polls_edit
	Нужно перенести в инициализацию bors_page, а в страницах выше ручную загрузку убрать
*/

class b2_template extends bors_object
{
	function view() { return $this->id(); }

	function parent()
	{
		$view_parent_class_name = get_parent_class($this->view()->class_name());
		$foo = bors_load($view_parent_class_name, $this->view()->id());
//		$foo = bors_foo($parent_class_name);
		return 'xfile:'.$foo->body_template_file();
	}
}
