<?php

/**
	Типовой класс для раздельного view объектов
*/

class bors_view extends bors_page
{
	// Класс отображаемого объекта
	function main_class() { return bors_throw(ec('Вы не переопределили класс отображаемого объекта')); }

	function referent_class() { return $this->main_class(); }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'object' => 'main_class(id)',
		));
	}
}
