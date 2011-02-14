<?php

class bors_task
{
	static function add($worker_name, $object = NULL, $priority = 0, $execute_time = NULL)
	{
		$task_manager_class_name = config('tasks.manager_class_name');
		$task_manager_class_name::add($worker_name, $object, $priority, $execute_time);
	}
}
