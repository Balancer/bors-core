<?php

class bors_storage_mongo extends bors_storage
{
	function load($object)
	{
		$m = new Mongo();
		$db = $m->selectDB($object->get('db_name', 'BORS'));
		$c = $db->createCollection($object->get('table_name', 'objects'));
		$object->data = $c->findOne(array('_id' => $object->id()));

		return $object;
	}

	function save($object)
	{
		$m = new Mongo();
		$db = $m->selectDB($object->get('db_name', 'BORS'));
		$c = $db->createCollection($object->get('table_name', 'objects'));
		$c->insert(array_merge(array(
			'_id' => $object->id(),
			'class_name' => $object->class_name(),
			'object_id' => $object->id()
		), $object->data));
	}

	function find_first($class_name, $where = array())
	{
		if(!is_array($where))
			$where = array('object_id' => $where);

		$where['class_name'] = $class_name;

		$obj = new $class_name(NULL);

		$m = new Mongo();
		$db = $m->selectDB('BORS');
		$c = $db->selectCollection('objects');

		$obj->data = $c->findOne($where);

		return $obj;
	}

	function create($object)
	{
		$m = new Mongo();
		$db = $m->selectDB($object->get('db_name', 'BORS'));
		$c = $db->createCollection($object->get('table_name', 'objects'));

		$data = array_merge($object->data, array(
//			'_id' => $object->internal_uri_ascii(),
			'class_name' => $object->class_name(),
			'object_id' => $object->id(),
			'create_time' => new MongoDate(),
			'modify_time' => new MongoDate(),
		));

		$c->insert($data);
		echo $data['_id']->{'$id'}."\n";
		echo $object->set_id($data['_id']->{'$id'}, false), "\n";

		return $object;
	}
}
