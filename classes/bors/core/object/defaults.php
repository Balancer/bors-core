<?php

class bors_core_object_defaults
{
	static function project_name($object)
	{
		$class_file = $object->class_file();
		// "/data/var/www/ru/wrk/ucrm/bors-site/classes/ucrm/projects/main.yaml"
		$name = preg_replace('!^.+/(\w+)/bors-site/.+$!', '$1', $class_file);

		if(!preg_match('/^\w+$/', $name))
			bors_throw(ec('Не задано имя проекта и не получилось его вычислить через class_file=').$class_file);

		return $name;
	}

	static function section_name($object)
	{
		$class_file = $object->class_file();
		// "/data/var/www/ru/wrk/ucrm/bors-site/classes/ucrm/projects/main.yaml"
		$name = preg_replace('!^.+/bors-site/classes/'.$object->project_name().'(/admin)?/(\w+)/.+$!', '$2', $class_file);

		if(!preg_match('/^\w+$/', $name))
			bors_throw(ec('Не задано имя раздела сайта и не получилось его вычислить через class_file=').$class_file);

		return $name;
	}

	static function config_class($object)
	{
		return $object->project_name().'_'.$object->section_name().'_config';
	}
}
