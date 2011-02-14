<?php

// Выполнение задачи с помощью отдельного обработчика через Gearman

class bors_tasks_gearman
{
	function add($worker_class_name, $object = NULL)
	{
		$client= new GearmanClient();
		$client->addServer();

		$client->doBackground('bors.task', serialize(array('worker_class_name' => $worker_class_name, 'worker_data' => $object)));
//		debug_hidden_log('_talks', "BalaBOT: $message", false);
	}
}
