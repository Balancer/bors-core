<?php

class bors_admin_edit_synonyms extends bors_admin_edit
{
	function title() { return ($this->object() ? $this->object()->title() : '---').ec(': Синонимы'); }
	function nav_name() { return ec('синонимы'); }
	function object() { return ($this->__havec('object') && $this->__lastc()) ? $this->__lastc() : $this->__setc(object_load(@$_GET['object'])); }
	function real_object() { return ($this->__havec('real_object') && $this->__lastc()) ? $this->__lastc() : $this->__setc(object_load(@$_GET['real_object'])); }
	function parents() { return array($_GET['edit_class']); }

	function admin_object() { return $this->object(); }

	function body_data()
	{
		if(!$this->object() || !$this->object()->id())
			return parent::body_data();

		return array_merge(parent::body_data(), array(
			'list' => bors_synonym::synonyms($this->object()),
		));
	}

	function pre_show()
	{
		template_jquery();
		return parent::pre_show();
	}

	function on_action_add($data)
	{
		$target = object_load($data['target_class_name'], $data['target_object_id']);
		if(!$target)
			return bors_message(ec('Вы пытаетесь создать синоним для несуществующего объекта'));

		if(empty($data['title']))
			return bors_message(ec('Не задан синоним'));

		bors_synonym::add_object($data['title'], $target, array(
			'is_auto' => 0,
			'is_disabled' => 0,
			'is_exactly' => defval($data, 'is_exactly'),
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

	function on_action_disable($data)
	{
		$syn = object_load($data['obj']);
		$syn->set_is_disabled(true, true);
		$syn->set_is_auto(false, true);

		return go_ref($this->url());
	}

	function on_action_enable($data)
	{
		$syn = object_load($data['obj']);
		$syn->set_is_disabled(false, true);
		$syn->set_is_auto(false, true);

		return go_ref($this->url());
	}

	function on_action_check($data)
	{
		$syn = object_load($data['obj']);
		$syn->set_is_auto(false, true);

		return go_ref($this->url());
	}

	function on_action_locked($data)
	{
		$syn = object_load($data['obj']);
		$syn->set_is_exactly(true, true);
		return go_ref($this->url());
	}

	function on_action_unlocked($data)
	{
		$syn = object_load($data['obj']);
		$syn->set_is_exactly(false, true);
		return go_ref($this->url());
	}
}
