<?php

class bors_link extends bors_object_db
{
	private $replace = false;

	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_cross'; }
	function table_fields()
	{
		return array(
			'id',
			'type_id',
			'is_auto_raw' => 'is_auto',
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

	//FIXME: наверное, не нужно. Ошибка дублирования ключа вылезала в
	//	http://admin2.aviaport.wrk.ru/_bors/admin/edit/crosslinks/?object=aviaport_digest_news__219001&edit_class=http://admin2.aviaport.wrk.ru/news/219001/
	//	при AJAX-обновлении связей
	function ignore_on_new_instance() { return true; }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'from_object'	=> 'from_class(from_id)',
			'target' 		=> 'target_class_id(target_object_id)',
		));
	}

	function auto_objects()
	{
		return array(
			'type' => 'bors_links_types(type_id)',
		);
	}

	function admin_url()
	{
		$from = $this->from_object();
		$to = $this->target();
		if(!$from || !$to)
			return NULL;

		return '/_bors/admin/cross_chtype?from='.$from->internal_uri_ascii()
			.'&to='.$to->internal_uri_ascii(); 
	}

	function is_auto() { return $this->is_auto_raw() || $this->owner_id() < 0; }
	function set_is_auto($flag, $up) { return $this->set('is_auto_raw', $flag, $up); }
	function is_about() { return $this->type_id() == bors_links_types::ABOUT; }
	function from_class_name() { return class_id_to_name($this->from_class()); }

	function set_from($obj_from)
	{
		if(!$obj_from)
			return;

		$this->set_from_class($obj_from->extends_class_id());
		$this->set_from_id   ($obj_from->id());
	}

	function set_target($target)
	{
		$this->set_target_class_id ($target->extends_class_id());
		$this->set_target_object_id($target->id());

		$this->set_target_create_time($target->create_time(true));
		$this->set_target_modify_time($target->modify_time(true));
		$this->set_target_time1($target->link_time1(true));
		$this->set_target_time2($target->link_time2(true));
	}

	function set_replace($bool) { $this->replace = $bool; }
	function replace_on_new_instance() { return $this->replace; }

	static function link_objects($obj1, $obj2, $params = array())
	{
		if(!$obj1 || !$obj2)
			return;

		self::link_object_to($obj1, $obj2, $params);
		self::link_object_to($obj2, $obj1, $params);
	}

	static function link($class1, $id1, $class2, $id2, $params = array())
	{
//		echo "link($class1, $id1, $class2, $id2, $params = array())<br/>";
		$obj1 = bors_load($class1, $id1);
		$obj2 = bors_load($class2, $id2);
		self::link_object_to($obj1, $obj2, $params);
		self::link_object_to($obj2, $obj1, $params);

		if(method_exists($obj2, 'set_parent_object') && !$obj2->parent_object())
			$obj2->set_parent_object($obj1);
	}

	static function link_object_to($from, $to, $params = array())
	{
		$link = object_new('bors_link');
		$link->set_from($from);
		$link->set_target($to);
		$link->set_sort_order(defval($params, 'sort_order', 0));

		if(empty($params['owner_id']))
			$params['owner_id'] = bors()->user_id();

		foreach($params as $k => $v)
			$link->{"set_$k"}($v, true);

		$link->new_instance();
		$link->store();

		if(method_exists($to, 'set_parent_object') && !$to->parent_object())
			$to->set_parent_object($from);
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
		$target_classes_skip = array();
		foreach(explode(',', $params['target_class']) as $tc)
		{
			$tc = trim($tc);
			if($tc[0] == '-')
				$target_classes_skip[] = class_name_to_id(substr($tc,1));
			else
				$target_classes[] = class_name_to_id($tc);
		}

		unset($params['target_class']);
		if($target_classes)
		{
			if(count($target_classes) == 1)
				$params['target_class_id'] = $target_classes[0];
			else
				$params['target_class_id IN'] = $target_classes;
		}

		if($target_classes_skip)
		{
			if(count($target_classes_skip) == 1)
				$params['target_class_id<>'] = $target_classes_skip[0];
			else
				$params['target_class_id NOT IN'] = $target_classes_skip;
		}
	}

	// Возвращает список ссылок (не самих объектов!) от данного объекта
	// Если Объект - имя класса, то от всех объектов данного класса.
	static function links($object, $params = array())
	{
		self::_target_class_parse($params);

		if(empty($params['order']))
			$params['order'] = 'sort_order';

		if(is_object($object))
		{
			$params['from_class'] = $object->extends_class_id();
			$params['from_id']    = $object->id();
		}
		else
		{
			$params['from_class'] = class_name_to_id($object);
		}

		if($to = @$params['to'])
		{
			if(is_object($to))
			{
				$params['to_class'] = $to->extends_class_id();
				$params['to_id']    = $to->id();
			}
			elseif($to)
			{
				$params['to_class'] = class_name_to_id($to);
			}

			unset($params['to']);
		}

		return bors_find_all('bors_link', $params);
	}

	static function links_each($object, $params = array())
	{
		self::_target_class_parse($params);

		if(empty($params['order']))
			$params['order'] = 'sort_order';

		if(is_object($object))
		{
			$params['from_class'] = $object->extends_class_id();
			$params['from_id']    = $object->id();
		}
		elseif($object)
		{
			$params['from_class'] = class_name_to_id($object);
		}

		if($to = @$params['to'])
		{
			if(is_object($to))
			{
				$params['to_class'] = $to->extends_class_id();
				$params['to_id']    = $to->id();
			}
			elseif($to)
			{
				$params['to_class'] = class_name_to_id($to);
			}

			unset($params['to']);
		}

		return bors_each('bors_link', $params);
	}

	// Возвращает список объектов, на которые ссылается данный объект.
	// Если Объект - имя класса, то от всех объектов данного класса.
	static function objects($object, $params = array())
	{
		$result = array();
		$objs = array();

		$links = bors_link::links($object, $params);

		foreach($links as $link)
			$objs[$link->target_class_id()][$link->target_object_id()] = true;

		foreach($objs as $class_id => $ids)
			objects_array($class_id, array('id IN' => array_keys($ids)));

		foreach($links as $link)
		{
			if(!($x = $link->target()))
			{
				if(config('bors_link.lost_auto_delete'))
					$link->delete();

				continue;
			}

			$x->_set_arg('is_special', $link->type_id() == 3);
			$x->set_link_type_abs_id($link->type_id(), false);

			if($link->owner_id() < 0)
				$x->set_link_type_id(-$link->type_id(), false);
			else
				$x->set_link_type_id($link->type_id(), false);

			$x->set_bors_link($link, false);

			$result[] = $x;
		}

		return $result;
	}

	static function object_ids($object, $params = array())
	{
		$result = array();
		$objs = array();

		$links = bors_link::links_each($object, $params);

		foreach($links as $link)
		{
			if(!($x = $link->target()))
			{
				if(config('bors_link.lost_auto_delete'))
					$link->delete();

				continue;
			}

			$result[] = $x->id();
		}

		return array_unique($result);
	}

	static function links_count($object, $where = array())
	{
//		if(!$object)
//			return 0;

		if(!is_array($where))
			$where = array('target_class' => $where);

		self::_target_class_parse($where);

		$where['from_class'] = $object->extends_class_id();
		$where['from_id'] = $object->id();
		$where[] = '(type_id IS NULL OR type_id<>4)';

		return objects_count('bors_link', $where);
	}

	static function drop_auto($object)
	{
		$dbh = new driver_mysql(config('main_bors_db'));
		$tc = $object->extends_class_id();
		$ti = $object->id();
		$dbh->delete(self::table_name(), array("owner_id < 0 AND ((from_class=$tc AND from_id=$ti) OR (to_class=$tc AND to_id=$ti))"));
	}

	static function drop_all($object)
	{
		if(!$object->id())
			return;

		$dbh = new driver_mysql(config('main_bors_db'));
		$tc = $object->extends_class_id();
		$ti = $object->id();
		$dbh->delete(self::table_name(), array("((from_class=$tc AND from_id=$ti) OR (to_class=$tc AND to_id=$ti))"));
	}

	static function drop_target($object, $where)
	{
		if(!$object->id())
			return;

		if(is_object($where))
			$where = array('target_class' => $where->extends_class_id(), 'target_id' => $where->id());

		$dbh = new driver_mysql(config('main_bors_db'));
		$fc = $object->extends_class_id();
		$fi = $object->id();

		$tc = class_name_to_id($where['target_class']);
		if(!$tc)
			return;

		if(!empty($where['target_id']) && ($ti = class_name_to_id($where['target_id'])))
			$dbh->delete(self::table_name(), array("((from_class=$fc AND from_id=$fi AND to_class=$tc AND to_id=$ti)
				OR (to_class=$fc AND to_id=$fi AND from_class=$tc AND from_id=$ti))"));
		else
			$dbh->delete(self::table_name(), array("((from_class=$fc AND from_id=$fi AND to_class=$tc) 
				OR (to_class=$fc AND to_id=$fi AND from_class=$tc))"));
	}

	static function drop($from_class, $from_id, $to_class, $to_id = NULL)
	{
		$from_id = intval($from_id);
		$to = intval($to_id);
		if(!$from_id)
			return;

		$dbh = new driver_mysql(config('main_bors_db'));

		$from_class = class_name_to_id($from_class);
		$to_class = class_name_to_id($to_class);
		if(!$to_class || !$from_class)
			return;

		if($to_id)
			$dbh->delete(self::table_name(), array("((from_class=$from_class AND from_id=$from_id AND to_class=$to_class AND to_id=$to_id)
				OR (to_class=$from_class AND to_id=$from_id AND from_class=$to_class AND from_id=$to_id))"));
		else
			$dbh->delete(self::table_name(), array("((from_class=$from_class AND from_id=$from_id AND to_class=$to_class)
				OR (to_class=$from_class AND to_id=$from_id AND from_class=$to_class))"));
	}

	function urls($type)
	{
		switch($type)
		{
			case 'unlink':
				return '/_bors/admin/cross_unlink?from='
					.object_property($this->from_object(), 'internal_uri')
					.'&to='.object_property($this->target(), 'internal_uri');
		}

		return parent::urls($type);
	}
}
