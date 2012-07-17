<?php

class bors_object_titles
{
	static function class_title_gen($object) { return self::_class_title_helper($object, 'gen'); }
	static function class_title_mult($object)
	{
		try
		{
			$mult = bors_lower(lingustics_morphology::case_rus($object->class_title(), 'nom,mult'));
		}
		catch(Exception $e) { }

		if(empty($mult))
			$mult = ec('объекты ').@get_class($object);

		return $mult;
	}

//	static function class_title_($object) { return bors_lower(lingustics_morphology::case_rus($object->class_title(), 'gen')); }

	private static function _class_title_helper($object, $case, $default)
	{
		try
		{
			$title = bors_lower(lingustics_morphology::case_rus($object->class_title(), $case));
		}
		catch(Exception $e) { }

		if(empty($title))
			$title = bors_lower(lingustics_morphology::case_rus(ec('объект'), $case)).' '.@get_class($object);

		return $title;
	}
}
