<?php

class bors_common_event extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'BORS'; }
	function table_name() { return 'common_events'; }
	function table_fields()
	{
		return array(
			'id',
			'title' => 'public_title',
			'public_title',
			'personal_title',
			'public_text',
			'personal_text',
			'handler_class_name',
			'target_user_class_name',		'target_user_id',
			'actor_user_class_name',		'actor_user_id',
			'object_class_name',	'object_id',
			'target_class_name',	'target_id',
			'category_class_name',	'category_id',
			'folder_class_name',	'folder_id',
			'create_time' 	=> 'UNIX_TIMESTAMP(create_timestamp)',
			'modify_time'	=> 'UNIX_TIMESTAMP(modify_timestamp)',
		);
	}

	/**
		$action	— класс-обработчик
		$object — объект события
		$user	— пользователь, если событие персональное
		$target — целевой объект для объекта события. Например, если событие — выставление оценки,
					то объект — оценка, а цель — постинг, за который выставлялась оценка
	*/

	function add($action, $object = NULL, $target = NULL, $target_user = NULL, $actor_user = NULL)
	{
//		print_dd(compact('object', 'user', 'target'));
		$actor		= bors_load_ex($action, NULL, compact('object', 'actor_user', 'target_user', 'target'));
		$category	= object_property($target, 'category');
		$folder		= object_property($target, 'folder');
		return bors_new(config('default_events_class', __CLASS__), array(
			'handler_class_name'=> $action,
			'public_title'	=> $actor->public_title(),
			'public_text'	=> $actor->public_text(),
			'personal_title'	=> $actor->personal_title(),
			'personal_text'	=> $actor->personal_text(),
			'target_user_class_name'	=> object_property($target_user, 'class_name'),
			'target_user_id'			=> object_property($target_user, 'id'),
			'actor_user_class_name'	=> object_property($actor_user, 'class_name'),
			'actor_user_id'			=> object_property($actor_user, 'id'),
			'object_class_name'	=> object_property($object, 'class_name'),
			'object_id'			=> object_property($object, 'id'),
			'target_class_name'	=> object_property($target, 'class_name'),
			'target_id'			=> object_property($target, 'id'),
			'category_class_name' => object_property($category, 'class_name'),
			'category_id'		=> object_property($category, 'id'),
			'folder_class_name'	=> object_property($folder, 'class_name'),
			'folder_id'			=> object_property($folder, 'id'),
		));
	}
}
