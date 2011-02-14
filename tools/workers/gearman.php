<?php

/**
	Скрипт, обработчик событий через Gearman
*/

require '../config.php';

//	Создаем воркера и подключаемся к серверу задач
$gmworker = new GearmanWorker();
$gmworker->addServer();

//	Регистрируем универсальный обработчик событий
$gmworker->addFunction("bors.task", "dispatcher");
$gmworker->setTimeout(1000);

$loop = 60;
while($loop-->0 && (@$gmworker->work() || $gmworker->returnCode() == GEARMAN_TIMEOUT))
{
	if($gmworker->returnCode() == GEARMAN_TIMEOUT)
	{
		// Normally one would want to do something useful here ...
		echo "\r[".date('r')."][{$loop}] mem usage = ".memory_get_usage()."; peak usage = ".memory_get_peak_usage()."          ";
		continue;
	}

	if($gmworker->returnCode() != GEARMAN_SUCCESS)
	{
		echo "Gearman return_code: " . $gmworker->returnCode() . "\n";
		break;
	}
}

exit("\n");

# Функция-диспетчер
# В аргументах ей передается объект задачи
function dispatcher($job)
{
	$workload = $job->workload();
	$data = unserialize($workload);
	if(empty($data['worker_class_name']))
		return;

	if($child_pid = pcntl_fork())
	{
		if($child_pid > 0)
		{
			// Это основная ветка. Был запущен форк. Возвращаемся за следующим заданием.
			echo "\nFork {$data['worker_class_name']}(".substr(str_replace("\n", ' ', print_r(@$data['worker_data'], true)), 0, 50).") running\n";
			return;
		}

		echo "\nCould not fork!!\nDying...\n";
		return;
	}

	// Это уже тело форка.

	// Создаём класс-обработчик
	$bors_worker = bors_load($data['worker_class_name'], NULL);
	if($bors_worker)
		$bors_worker->execute(@$data['worker_data']);
	else
		echo "Не могу инициализировать класс ".$data['worker_class_name']."\n";

	exit();
}
