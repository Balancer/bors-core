<?php

class bors_object_titles
{
	static function class_title_gen($object) { return bors_lower(lingustics_morphology::case_rus($object->class_title(), 'gen')); }
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
}
