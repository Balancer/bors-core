<?php

class bors_objects_visit extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

//	function db_name() { return 'BORS'; }
	function table_name() { return 'bors_views_count'; }
	function table_fields()
	{
		return array(
			'*no_defaults',
//			'id' => 'CONCAT(`target_class_name`,":",`target_id`)',
			'id',
			'target_class_name',
			'target_id',
			'target_page',
			'views_count',
			'first_visit' => 'UNIX_TIMESTAMP(`first_visit`)',
			'last_visit' => 'UNIX_TIMESTAMP(`last_visit`)',
			'views_average_per_day',
		);
	}

	static function per_day($object)
	{
		$count = bors_find_first(__CLASS__, array(
			'target_class_name' => $object->extends_class_name(),
			'target_id' => $object->id(),
		));

		if($count)
		{
			$now = time();
			if($now == $count->first_visit())
				return $count->set_views_average_per_day(1, true);

			return $count->set_views_average_per_day(86400*$count->views_count()/($now - $count->first_visit()), true);
		}

		return 0;
	}

	static function inc($object, $inc = 1)
	{
		$now = time();

		$count = bors_find_first(__CLASS__, array(
			'target_class_name' => $object->extends_class_name(),
			'target_id' => $object->id(),
			'target_page' => $object->page(),
		));

		if($count)
		{
			$count->set_last_visit($now, true);
			$count->set_views_count($count->views_count() + $inc, true);
			if($now == $count->first_visit())
				$count->set_views_average_per_day(1, true);
			else
				$count->set_views_average_per_day(86400*$count->views_count()/($now - $count->first_visit()), true);
		}
		else
		{
			$count = bors_new(__CLASS__, array(
				'target_class_name' => $object->extends_class_name(),
				'target_id' => $object->id(),
				'target_page' => $object->page(),
				'first_visit' => $now,
				'last_visit' => $now,
				'views_count' => $inc,
				'views_average_per_day' => 1,
			));
		}
	}
}
