<?php

class bors_admin_links_checklist extends bors_admin_page
{
	function parents() { return array($this->object->admin()->url()); }

	function title()
	{
		return ec('Привязки ')
			.$this->object->class_title_rp().' «'.$this->object->title().'» к '
			.$this->target_foo->class_title_dpm();
	}

	function nav_name() { return ec('привязки'); }

//	function config_class() { return 'bors_admin_links_config'; }

	function pre_show()
	{
		list($this->object, $this->target_class_name) = explode('/', $this->page());
		$this->object = bors_load_uri($this->object);
		$this->target_foo = new $this->target_class_name(NULL);

		config_set('nav_name_lower', false);
		return parent::pre_show();
	}

	function body_data()
	{
		$items = bors_link::objects($this->object, array('to_class' => class_name_to_id($this->target_class_name)));

		bors_function_include('natural/bors_plural');

		$targets = bors_find_all($this->target_class_name, array('order' => 'title', 'by_id' => true));

		$linked_ids = bors_field_array_extract($items, 'id');

		foreach($targets as $x)
			$x->set_attr('linked', in_array($x->id(), $linked_ids));

		return array_merge(parent::body_data(), compact('targets'));
	}

	function from_class_name() { return array_shift(explode('/', $this->page())); }
	function to_class_name() { return array_pop(explode('/', $this->page())); }

	function where($cond)
	{
		return array_merge(array(
				'from_class' => class_name_to_id($this->from_class_name()),
				'to_class' => class_name_to_id($this->to_class_name()),
		), $cond);
	}

	function total_items() { return objects_count('bors_link', $this->where(array())); }
	function items_around_page() { return 100; }

	function url($page=NULL) { return $this->called_url(); }

	function on_action_do($data)
	{
		extract($data);
		$object = bors_load_uri($object_uri);
		if(empty($object))
			return go_ref_message(ec('Объект не найден'));
		if(empty($target_class_name))
			return go_ref_message(ec('Не указан целевой класс'));

		bors_link::drop_target($object, array('target_class' => $target_class_name));

		foreach($targets as $tid)
			bors_link::link($object->extends_class_name(), $object->id(), $target_class_name, $tid);

		return go_ref_message(ec('Привязка успешно проведена'), array('type' => 'success'));
	}
}
