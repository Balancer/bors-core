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
			'handler_class_name',
			'user_class_id', 'user_id', // Кому идёт сообщение. 0, если публичное.
			'title', 'text', 'color',
			'object_class_name',	'object_id', // Исходный объект действия, источник данных
			'target_class_name',	'target_id', // Целевой объект действия (топик, постинг, пользователь, если это репутация)
			'category_class_name',	'category_id',
			'folder_class_name',	'folder_id',
			'create_time' 	=> 'UNIX_TIMESTAMP(`create_timestamp`)',
			'modify_time'	=> 'UNIX_TIMESTAMP(`modify_timestamp`)',
		);
	}

	function replace_on_new_instance() { return true; }

	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'object' => 'object_class_name(object_id)',
			'target' => 'target_class_name(target_id)',
			'user' => 'user_class_id(user_id)',
		));
	}

	/**
		$action	— класс-обработчик
		$object — объект события
		$user	— пользователь, если событие персональное
		$target — целевой объект для объекта события. Например, если событие — выставление оценки,
					то объект — оценка, а цель — постинг, за который выставлялась оценка
	*/

	// Примеры вызова:
	// Нотификация пользователю:
	//	bal_event::add('balancer_board_actor_vote', $user, $vote)
	//	bal_event::add('balancer_board_actor_reputation', $target_user, $rep);
	//	bal_event::add('balancer_board_actor_topic', $answer_to_user, $topic);
	// Информация для всех:
	//	bal_event::add('balancer_board_actor_vote', NULL, $vote)
	//	bal_event::add('balancer_board_actor_reputation', NULL, $t);
	//	bal_event::add('balancer_board_actor_topic', NULL, $topic);

	function add($action, $user = NULL, $object = NULL, $target = NULL, $attrs = array())
	{
//		print_dd(compact('object', 'user', 'target'));
		if(!$target)
			$target		= object_property($object, 'target');

		$actor		= bors_load_ex($action, NULL, compact('object', 'user', 'attrs'));

		$category	= object_property($target, 'category');
		$folder		= object_property($target, 'folder');

		$data = array(
			'handler_class_name'=> $action,
			'color' => $actor->color(),

			'object_class_name'	=> object_property($object, 'class_name'),
			'object_id'			=> object_property($object, 'id'),
			'target_class_name'	=> object_property($target, 'class_name'),
			'target_id'			=> object_property($target, 'id'),

			'category_class_name' => object_property($category, 'class_name'),
			'category_id'		=> object_property($category, 'id'),
			'folder_class_name'	=> object_property($folder, 'class_name'),
			'folder_id'			=> object_property($folder, 'id'),
		);

		if(empty($attrs['personal_only']) && ($title = $actor->public_title()))
		{
			$data['title']	= $title;
			$data['text']	= $actor->public_text();
			// Кому идёт сообщение. 0, если публичное.
			$data['user_class_id']	= 0;
			$data['user_id'] = 0;
			bors_new(config('default_events_class', __CLASS__), $data);
		}

		if($user && ($title = $actor->personal_title()))
		{
			$data['title']	= $title;
			$data['text']	= $actor->personal_text();
			// Кому идёт сообщение. 0, если публичное.
			$data['user_class_id']	= $user->class_id();
			$data['user_id']		= $user->id();
			bors_new(config('default_events_class', __CLASS__), $data);
		}
	}
}
