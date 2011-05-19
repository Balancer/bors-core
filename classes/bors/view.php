<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	// Класс отображаемого объекта
	function main_class()
	{
		return bors_unplural(preg_replace('/_view$/', '', $this->extends_class_name()));
	}

	function item_name()
	{
		return preg_replace('/^.+_(.+?)$/', '$1', $this->main_class());
	}

	function referent_class() { return $this->main_class(); }

	function title($exact = false) { return $this->object()->title($exact); }
	function create_time($exact = false) { return $this->object()->create_time($exact); }
	function modify_time($exact = false) { return $this->object()->modify_time($exact); }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'object' => 'main_class(id)',
		));
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			$this->item_name() => $this->object(),
		), $this->object()->data);
	}
}
