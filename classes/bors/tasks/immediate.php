<?php

// Немедленное выполнение задачи без всяких менеджеров фоновой обработки

class bors_tasks_immediate
{
	function add($worker_class_name, $object = NULL)
	{
		$worker_class_name::execute($object);
	}
}
