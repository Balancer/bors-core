<?php

class bors_link extends base_object_db
{
	private $replace = false;

	function main_table() { return 'bors_cross'; }
	function main_table_fields()
	{
		return array(
			'id',
			'type_id',
			'from_class',
			'from_id',
			'target_class_id' => 'to_class',
			'target_object_id' => 'to_id',
			'target_create_time',
			'target_modify_time',
			'target_time1',
			'target_time2',
			'sort_order',
			'create_time',
			'modify_time',
			'owner_id',
			'comment',
		);
	}

	function from_object() { return object_load($this->from_class(), $this->from_id()); }
	function target()   { return object_load($this->target_class_id(),   $this->target_object_id()); }
	function type() { return object_load('bors_cross_types', $this->type_id()); }

	function set_from($obj_from, $db_up)
	{
		$this->set_from_class($obj_from->class_id(), $db_up);
		$this->set_from_id   ($obj_from->id(), $db_up);
	}

	function set_target($target, $db_up)
	{
		$this->set_target_class_id ($target->class_id(), $db_up);
		$this->set_target_object_id($target->id(),       $db_up);

		$this->set_target_create_time($target->create_time(true), $db_up);
		$this->set_target_modify_time($target->modify_time(true), $db_up);
		$this->set_target_time1($target->link_time1(true), $db_up);
		$this->set_target_time2($target->link_time2(true), $db_up);
	}

	function set_replace($bool) { $this->replace = $bool; }
	function replace_on_new_instance() { return $this->replace; }

	static function link_objects($obj1, $obj2, $params = array())
	{
		self::link_object_to($obj1, $obj2, $params);
		self::link_object_to($obj2, $obj1, $params);
	}

	static function link_object_to($from, $to, $params = array())
	{
		$link = object_new('bors_link');
		$link->set_from($from, true);
		$link->set_target($to, true);

		if(empty($params['owner_id']))
			$params['owner_id'] = bors()->user_id();

		foreach($params as $k => $v)
			$link->{"set_$k"}($v, true);

		$link->new_instance();
		$link->store();
	}

	private static function _target_class_parse(&$params)
	{
		if(!is_array($params))
			$params = array('target_class' => $params);

		if(empty($params['target_class']))
		{
			unset($params['target_class']); // На случай, если подсунули пустую строку
			return;
		}

		$target_classes = array();
		foreach(explode(',', $params['target_class']) as $tc)
			$target_classes[] = class_name_to_id(trim($tc));

		unset($params['target_class']);
		if(count($target_classes) == 1)
			$params['target_class_id'] = $target_classes[0];
		else
			$params['target_class_id IN'] = $target_classes;
	}

	// Возвращает список ссылок (не самих объектов!) от данного объекта
	static function links($object, $params = array())
	{
		self::_target_class_parse($params);

		if(empty($params['order']))
			$params['order'] = 'sort_order';

		$params['from_class'] = $object->class_id();
		$params['from_id']    = $object->id();

		if(!empty($params['to']))
		{
			$params['to_class'] = $params['to']->class_id();
			$params['to_id']    = $params['to']->id();
			unset($params['to']);
		}

		return objects_array('bors_link', $params);
	}

	// Возвращает список объектов, на которые ссылается данный объект.
	static function objects($object, $params = array())
	{
		$result = array();
		$objs = array();

		$links = bors_link::links($object, $params);

		foreach($links as $link)
			$objs[$link->target_class_id()][$link->target_object_id()] = 1;

		foreach($objs as $class_id => $ids)
			objects_array($class_id, array('id IN' => $ids));

		foreach($links as $link)
		{
			$x = $link->target();
			$x->_set_arg('is_special', $link->type_id() == 3);
			$x->set_link_type_abs_id($link->type_id(), false);

			if($link->owner_id() < 0)
				$x->set_link_type_id(-$link->type_id(), false);
			else
				$x->set_link_type_id($link->type_id(), false);

			$result[] = $x;
		}

		return $result;
	}

	static function links_count($object, $where = array())
	{
//		if(!$object)
//			return 0;

		if(!is_array($where))
			$where = array('target_class' => $where);

		self::_target_class_parse($where);

		$where['from_class'] = $object->class_id();
		$where['from_id'] = $object->id();
		$where['type_id<>'] = 4;

		return objects_count('bors_link', $where);
	}

	static function drop_auto($object)
	{
		$dbh = new driver_mysql('WWW');
		$tc = $object->class_id();
		$ti = $object->id();
		$dbh->delete(self::main_table(), array("owner_id < 0 AND ((from_class=$tc AND from_id=$ti) OR (to_class=$tc AND to_id=$ti))"));
	}
}
