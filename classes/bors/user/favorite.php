<?php

class bors_user_favorite extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'bors_favorites'; }
	function table_fields()
	{
		return array(
			'id',
			'user_class_name' => array('title' => ec('Имя класса владельца')),
			'user_id' => array('title' => ec('ID владельца')),
			'title' => array('name' => 'target_title', 'title' => ec('Название объекта')),
			'target_title' => array('title' => ec('Название объекта')),
			'target_class_name' => array('title' => ec('Имя класса объекта')),
			'target_object_id' => array('title' => ec('ID объекта')),
			'target_create_time' => array('title' => ec('Дата создания объекта'), 'type' => 'uint'),
			'create_time' => array('type' => 'uint'),
		);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_object_id)',
		));
	}

	static function add($user, $target)
	{
		if(!$user || !$target)
			return NULL;

		return bors_new('bors_user_favorite', array(
			'user_class_name' => $user->class_name(),
			'user_id' => $user->id(),
			'target_title' => $target->class_title().ec(' «').$target->title().ec('»'),
			'target_class_name' => $target->class_name(),
			'target_object_id' => $target->id(),
			'target_create_time' => $target->create_time(),
		));
	}

	static function find($user, $target)
	{
		if(!$user || !$target)
			return NULL;

		return objects_first('bors_user_favorite', array(
			'user_class_name' => $user->class_name(),
			'user_id' => $user->id(),
			'target_class_name' => $target->class_name(),
			'target_object_id' => $target->id(),
		));
	}

	static function remove($user, $target)
	{
		if(!$user || !$target)
			return NULL;

		if(($f = self::find($user, $target)))
			$f->delete();

		return $f;
	}
}
