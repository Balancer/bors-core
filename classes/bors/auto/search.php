<?php

/**
	@nav_name = поиск
*/

class bors_auto_search extends bors_paginated
{
	function _title_def()
	{
		return ec('Поиск по ').call_user_func(array($this->main_class(), 'class_title_dpm'));
	}

	function _item_type_def()
	{
		if($class = $this->args('class'))
			return bors_unplural($class);

		bors_throw(ec("Не задан тип искомых объектов и его не удаётся вычислить"));
	}

	function _project_name_def()
	{
		if($project = $this->args('project'))
			return $project;

		return config('project.name');
	}

	function _main_class_def()
	{
//		echo "project='{$this->project_name()}', item='{$this->item_type()}'<br/>";
		return $this->project_name().'_'.$this->item_type();
	}

	function body_data()
	{
//		var_dump($this->args());
/*
array
  'match' => 
    array
      0 => string 'http://ucrm.wrk.ru/persons/search/' (length=34)
      1 => string 'ucrm.wrk.ru' (length=11)
      2 => string 'persons' (length=7)
  'called_url' => string 'http://ucrm.wrk.ru/persons/search/' (length=34)
  'project' => string 'ucrm' (length=4)
*/
		return parent::body_data();
	}

	function query()
	{
		return urldecode(bors()->request()->data('q'));
	}

	function where()
	{
		return array('0');
	}

	function action_url()
	{
		return config('main_site_url').'/'.bors_plural($this->item_type()).'/search/';
	}

	function url($page = NULL)
	{
		$url = $this->action_url();

		if($q = $this->query())
			$url = bors_lib_urls::replace_query($url, 'q', $q);

		if($page > 1)
			$url = bors_lib_urls::replace_query($url, 'p', $page);

		return $url;
	}

	function page() { return bors()->request()->data('p', $this->default_page()); }

	function url_skip_keys() { return 'q'; }
}
