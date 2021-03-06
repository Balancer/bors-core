<?php

class bors_admin_meta_main extends bors_admin_paginated
{
	function _config_class_def() { return config('admin_config_class'); }
	function _access_name_def() { return bors_lib_object::get_static($this->main_admin_class(), 'access_name'); }

	function _title_def() { return ec('Управление ').bors_lib_object::get_foo($this->main_class(), 'class_title_tpm'); }
	function _nav_name_def() { return bors_lib_object::get_foo($this->main_class(), 'class_title_m'); }

	function _is_admin_list_def() { return true; }

	function _new_object_url_def() { return $this->url().'new/'; }

	function _have_page_search_def() { return $this->layout()->get('have_page_search'); }

	function _model_class_def()
	{
		return NULL;
	}

	function _main_class_def()
	{
		if($c = $this->model_class())
			return $c;

		$class_name = str_replace('_admin_', '_', $this->class_name());
		$class_name = str_replace('_main', '', $class_name);
		$class_name = str_replace('_search', '', $class_name);

		return blib_grammar::singular($class_name);
	}

	function _main_admin_class_def()
	{
		$class_name = str_replace('_main', '', $this->class_name());
		$class_name = str_replace('_search', '', $class_name);
		$admin_class_name = blib_grammar::singular($class_name);
		if(class_exists($admin_class_name))
			return $admin_class_name;

		return $this->main_class();
	}

	function pre_show()
	{
		if(!class_exists($this->main_class()))
			return bors_message("Class {$this->main_class()} not exists");

		return parent::pre_show();
	}

	function body_data()
	{
		$foo = bors_foo($this->main_class());

		$new_link_title = false;
		if(!$this->get('skip_auto_admin_new'))
			if(!$foo->get('skip_auto_admin_new'))
				$new_link_title = ec('Добавить ').$foo->class_title_vp();

		$new_link_url = $this->get('new_link_url', 'new/');

		$fields = $this->get('item_fields');

		if(!$fields)
			$fields = $foo->item_list_admin_fields();

		$parsed_fields = array();
		$sortable = array();
		foreach($fields as $p => $t)
		{
			if(is_numeric($p))
			{
				$p = $t;
				$x = bors_lib_orm::parse_property($this->main_class(), $p);
				$t = defval($x, 'title', $p);
				if(!empty($x['admin_sortable']))
					$sortable[] = $p;
			}

			$parsed_fields[$p] = $t;
		}

		$this->set_attr('_sortable_append', $sortable);

		$data = array();

        if($this->get('use_bootstrap'))
        {
            $data['tcfg'] = bors_load('balancer_board_themes_bootstrap', NULL);
            $data['pagination'] = $this->pages_links_list(array(
                'div_css' => 'pagination pagination-centered pagination-small',
                'li_current_css' => 'active',
                'li_skip_css' => 'disabled',
                'skip_title' => true,
            ));
			$data['bootstrap'] = true;
        }
        else
        {
            $data['tcfg'] = bors_load('balancer_board_themes_default', NULL);
            $data['pagination'] = $this->layout()->mod('pagination');
			$data['bootstrap'] = false;
        }

		$admin_search_url = $this->page() > 1 ? false : $this->get('admin_search_url');
		if($admin_search_url)
		{
			$admin_class = $this->get('model_admin_class');
			if($admin_class)
				$admin_foo = bors_foo($admin_class);
			else
				$admin_foo = $foo;

			if($admin_foo->admin_searchable_properties() != $admin_foo->admin_searchable_title_properties())
				$search_where = array('t' => 'в заголовках', 'a' => 'всюду', '*default' => 't');
			else
				$search_where = NULL;
		}

		return array_merge(parent::body_data(), $data, array(
			'query' => bors()->request()->data('q'),
			'new_link_title' => $new_link_title,
			'new_link_url' => $new_link_url,
			'item_fields' => $parsed_fields,
			'admin_search_url' => $admin_search_url,
			'search_where' => @$search_where,
		));
	}

	function _order_def()
	{
		if($current_sort = bors()->request()->data_parse('signed_names', 'sort'))
			return $current_sort;

		return parent::_order_def();
	}
}
