<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	// Класс отображаемого объекта
	function main_class() { return bors_throw(ec('Вы не переопределили класс отображаемого объекта')); }

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
}
