<?php

class bors_auto_search_result extends bors_auto_search
{
	function title()
	{
		return ec('Результат поиска по ').call_user_func(array($this->main_class(), 'class_title_dpm')).ec(' по запросу «').$this->query().ec('»');
	}

	function nav_name() { return ec('«').$this->query().ec('»'); }

	function where()
	{
		$q = "(title LIKE '%".addslashes($this->query())."%'";
		if($id = intval($this->query()))
			$q .= " OR id=".$id;
		$q .= ")";

		return array($q);
	}

	function result_fields()
	{
		$class_name = $this->main_class();
		$foo = new $class_name(NULL);
		if($data = $foo->get('search_result_fields'))
			return $data;

		if($data = $foo->get('item_list_fields'))
			return $data;

		return array(
			'title' => ec('Название'),
			'id' => ec('ID'),
		);
	}
}