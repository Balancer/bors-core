<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	function can_be_empty() { return false; }
	function loaded() { return !!$this->target(); }

	// Класс отображаемого объекта
	function main_class()
	{
		bors_function_include('natural/bors_unplural');

		$main_class = preg_replace('/_view$/', '', $this->extends_class_name());
		if(class_include($main_class))
			return $main_class;

		$main_class = bors_unplural($main_class);
		if(class_include($main_class))
			return $main_class;

		bors_throw(ec('Не определён главный класс для представления ').$this->class_name());
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function referent_class() { return $this->main_class(); }

	function title($exact = false) { return $this->object()->title($exact); }
	function nav_name($exact = false) { return $this->object()->nav_name($exact); }
	function description() { return $this->object()->description(); }
	function create_time($exact = false) { return $this->object()->create_time($exact); }
	function modify_time($exact = false) { return $this->object()->modify_time($exact); }

	function object() { return $this->target(); } // Для совместимости

	function target_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'main_class(id)',
			$this->target_name() => 'main_class(id)',
		));
	}

	function body_data()
	{
		$target = $this->object();
		return array_merge(parent::body_data(), array(
			$this->item_name() => $target,
			'target' => $target,
		), $this->target()->data);
	}

	function url($page = NULL) { return $this->target()->url($page); }
	function admin_url() { return $this->target()->get('admin_url'); }
	static function object_type() { return $this->target()->object_type(); }
}
