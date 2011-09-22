<?php

class bors_admin_edit_crosslinks extends bors_admin_edit
{
	function title() { return ($this->object() ? $this->object()->title() : '---').ec(': связи'); }
	function nav_name() { return ec('связи'); }

	function object()
	{
		if($this->__havec('object') && ($obj = $this->__lastc()))
			return $obj;
		$obj = object_load(@$_GET['object']);
		if(!$obj)
			$obj = object_load(@$_GET['real_object']);

		return $this->__setc($obj);
	}

	function admin_object() { return $this->object(); }
	function real_object() { return $this->object(); }

	function parents() { return array(empty($_GET['edit_class']) ? $this->real_object()->admin_url() : $_GET['edit_class']); }

	function page() { return max(1, @$_GET['p']); }
	function url_use_keys() { return 'p,object'; }
	function items_per_page() { return 50; }
	function total_items() { return bors_link::links_count($this->object()); }

	function body_data()
	{
		if(!$this->object() || !$this->object()->id())
			return parent::body_data();

//		$cross = $this->object()->cross_objects();
//		usort($cross, create_function('$x, $y', 'return $y->create_time() - $x->create_time();'));

		return array_merge(parent::body_data(), array(
			'object' => $this->object(),
			'object_uri' => $this->object()->internal_uri_ascii(),
			'cross' => bors_link::objects($this->object(), array(
				'order' => '-target_create_time',
				'page' => $this->page(),
				'per_page' => $this->items_per_page(),
			)),
		));
	}

	function pre_show()
	{
		template_jquery();
		return parent::pre_show();
	}

	function on_action_link(&$data)
	{
		$target = object_load($data['link_class_name'], $data['link_object_id']);
		if(!$target)
			return bors_message(ec('Вы пытаетесь привязать несуществующий объект'));

		$target = object_property($target, 'real_object', $target);

		bors_link::link_objects($this->object(), $target, array(
			'comment' => ec('Ручная привязка'),
			'type_id' => $data['link_type_id'],
			'replace' => true,
		));

		return go_ref($this->url());
	}

	function url_engine() { return 'url_getp'; }

	function parent_admin() { return object_load(@$_GET['edit_class']); }

	function config_class()
	{
		if(($p = $this->parent_admin()))
			return $p->config_class();
		else
			return parent::config_class();
	}

	function on_action_theme($data)
	{
//		echo $this->real_object();
		bors_link::drop_target($this->real_object(), array('target_class' => 'aviaport_common_theme'));
		if($data['common_themes'])
			foreach($data['common_themes'] as $theme_id)
				bors_link::link($this->real_object()->class_name(), $this->real_object()->id(), 'aviaport_common_theme', $theme_id);

		return go($data['go']);
	}
}
