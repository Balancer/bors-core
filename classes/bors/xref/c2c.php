<?php

// Class-to-class. Оба объекта связи задаются только по ID. Классы фиксированы.

class bors_xref_c2c extends bors_object_db
{
	function class_title() { return ec('Связь'); }
	function access() { return $this->foo_object()->access(); }
	function foo_object()
	{
		$class_name = $this->object_class_name();
		return new $class_name(NULL);
	}

	function _object_class_name_def() { return preg_replace('/^([\w]+)_xref_\w+?$/', '$1', $this->class_name()); }

	static function add($object, $target, $args = array(), $xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		$object_field_name = blib_grammar::singular(self::object_name($xref_class_name)).'_id';
		$target_field_name = blib_grammar::singular(self::target_name($xref_class_name)).'_id';

		$args[$object_field_name] = is_object($object) ? $object->id() : $object;
		$args[$target_field_name] = is_object($target) ? $target->id() : $target;

		bors_new($xref_class_name, $args);
	}

	static function _object_field_name_def($xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		return blib_grammar::singular(self::object_name($xref_class_name)).'_id';
	}

	static function object_name($xref_class_name)
	{
		if(preg_match('/^\w+?_([a-z]+)_xref_/', $xref_class_name, $m))
			return $m[1];

		return NULL;
	}

	static function target_name($xref_class_name = NULL)
	{
		if(is_null($xref_class_name))
			$xref_class_name = get_called_class();

		if(preg_match('/_xref_([a-z0-9]+)$/', $xref_class_name, $m))
			return $m[1];

		return NULL;
	}

	function ignore_on_new_instance() { return true; }

	function named_list($xref_class_name = NULL)
	{
		require_once('inc/bors/lists.php');

		if($this)
		{
			// http://matf.aviaport.ru/companies/1/edit/
			// bors-core/classes/bors/forms/checkbox/list.php:25
			return bors_named_list_db($this->target_class_name());
		}

		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		return bors_named_list_db($xref_class_name::target_class_name());
	}

	function name($object, $xref_class_name = NULL)
	{
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		return self::target_name($xref_class_name).'_ids';
	}

	// Отладка на http://pfo.wrk.ru/
	// И на http://www.aviaport.ru/directory/aviafirms/727/ — социальные сети
	function find_targets($where)
	{
		$xref_class_name = popval($where, 'xref_class_name');
		if(!$xref_class_name)
			$xref_class_name = $this->class_name();
		$target_class_name = popval($where, 'target_class_name');
		if(!$target_class_name)
			$target_class_name = $this->target_class_name();
		$target_field_name = popval($where, 'target_field_name');
		if(!$target_field_name)
			$target_field_name = $this->target_field_name();
		$target_where = popval($where, 'target', array());
		$xrefs = bors_find_all($xref_class_name, $where);
		$ids = bors_field_array_extract($xrefs, $target_field_name);
		$target_where['id IN'] = $ids;
		return bors_find_all($target_class_name, $target_where);
	}

	function xrefs($where = array())
	{
		return bors_find_all($this->class_name(), $where);
	}

	function objects($xrefs_where = array(), $objects_where = array())
	{
		$xrefs = bors_find_all($this->class_name(), $xrefs_where);
		$object_ids = bors_field_array_extract($xrefs, $this->object_field_name());
		$objects_where['id IN'] = $object_ids;
		return bors_find_all($this->object_class_name(), $objects_where);
	}

	function targets($xrefs_where = array(), $targets_where = array())
	{
		$xrefs = bors_find_all($this->class_name(), $xrefs_where);
		$target_ids = bors_field_array_extract($xrefs, $this->target_field_name());
		$targets_where['id IN'] = $target_ids;
		return bors_find_all($this->target_class_name(), $targets_where);
	}

	function count($where)
	{
		$xref_class_name = popval($where, 'xref_class_name');
		$target_class_name = popval($where, 'target_class_name');
		$target_field_name = popval($where, 'target_field_name');
		return bors_count($xref_class_name, $where);
	}

	// Отладка на:
	//	— http://admin.aviaport.wrk.ru/projects/1/
	//	— http://admin.aviaport.ru/directory/aviafirms/6/groups/
	static function target_ids($where)
	{
		$xref_class_name = popval($where, 'xref_class_name');
		if(!$xref_class_name)
			$xref_class_name = get_called_class();

		$target_field_name = popval($where, 'target_field_name');
		if(!$target_field_name)
			$target_field_name = bors_foo($xref_class_name)->target_field_name();

		$xrefs = bors_find_all($xref_class_name, $where);

		return bors_field_array_extract($xrefs, $target_field_name);
	}

	function object() { return bors_load($this->object_class_name(), $this->get($this->object_field_name())); }
	function target() { return bors_load($this->target_class_name(), $this->get($this->target_field_name())); }
	function target_field_name() { return blib_grammar::singular($this->target_name()).'_id'; }

	function admin_parent_url() { return $this->object()->admin()->urls(bors_plural($this->target_name())); }
}
