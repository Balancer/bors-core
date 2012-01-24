<?php

// Выполнение задачи с помощью отдельного обработчика через Gearman

class bors_tasks_gearman
{
	function add($worker_class_name, $object = NULL)
	{
		$client= new GearmanClient();
		$client->addServer();

		$client->doBackground('bors.task', serialize(array('worker_class_name' => $worker_class_name, 'worker_data' => $object)));
		bors_function_include('debug/hidden_log');
		debug_hidden_log('__tasks', "Add $worker_class_name($object)", false);
	}
}
