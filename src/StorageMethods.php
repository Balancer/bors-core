<?php

namespace B2;

trait StorageMethods
{
    /**
     * @param int|string $id
     * @return bors_object|b2_null
     */

	static function load($id)
	{
		require_once(__DIR__.'/../engines/bors.php');

		$object = bors_load(get_called_class(), $id);

		if(!$object)
			$object = new Null(NULL);

		return $object;
	}

	static function find($conditions = [])
	{
		$class_name = get_called_class();
		$finder = new \b2_core_find($class_name);

		if($conditions)
			$finder->where($conditions);

		return $finder;
	}

	static function find_all($conditions = [])
	{
		return self::find($conditions)->all();
	}

	static function find_first($conditions = [])
	{
		return self::find($conditions)->first();
	}
}
