<?php

class common_keyword_bind extends base_page_db
{
	function main_db_storage(){ return config('main_bors_db'); }
	function main_table(){ return 'bors_keywords_index'; }

    function main_table_fields()
	{
		return array(
			'id',
			'keyword_id',
			'target_class_name',
			'target_class_id',
			'target_object_id',
			'target_create_time',
			'target_modify_time',
			'target_owner_id',
			'target_forum_id',
			'target_container_class_name',
			'target_container_class_id',
			'target_container_object_id',
			'sort_order',
			'was_auto',
		);
	}

	static function add($object, $was_auto = false)
	{
		$db = new driver_mysql(config('main_bors_db'));

		$where = array(
			'target_class_id' => $object->class_id(),
			'target_object_id' => $object->id(),
		);

		if($was_auto) // Если это автоматическое добавление, то чистим тоже только автоматические.
			$where['was_auto'] = true;

		$db->delete('bors_keywords_index', $where);

		$container = object_property($object, 'container');
		if($container)
		{
			$target_container_class_name = $container->class_name();
			$target_container_class_id = $container->class_id();
			$target_container_object_id = $container->id();
		}
		else
		{
			$target_container_class_name = NULL;
			$target_container_class_id = NULL;
			$target_container_object_id = NULL;
		}

		foreach(explode(',', $object->keywords_string()) as $keyword)
		{
			$key = common_keyword::loader($keyword);

			$key->set_modify_time(time(), true);
			$key->set_targets_count(1 + $key->targets_count(), true);

			$new_bind = object_new_instance('common_keyword_bind', array('keyword_id' => $key->id(),
				'target_class_id' => $object->class_id(),
				'target_class_name' => $object->extends_class(),
				'target_object_id' => $object->id(),
				'target_create_time' => $object->create_time(),
				'target_modify_time' => $object->modify_time(),
				'target_owner_id' => $object->owner_id(),
				'target_forum_id' => object_property($object, 'forum_id'),
				'was_auto' => $was_auto,
				'target_container_class_name' => $target_container_class_name,
				'target_container_class_id' => $target_container_class_id,
				'target_container_object_id' => $target_container_object_id,
			));
		}
	}

	function auto_objects()
	{
		return array(
			'keyword' => 'common_keyword(keyword_id)',
			'target_forum' => 'balancer_board_forum(target_forum_id)',
		);
	}

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_id(target_object_id)',
		));
	}

//	function object() { return object_load($this->target_class_id(), $this->target_object_id()); }
}
