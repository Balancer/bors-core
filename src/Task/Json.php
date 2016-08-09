<?php

namespace B2\Task;

class Json
{
	function task_dir() { return COMPOSER_ROOT.'/data/b2-tasks'; }

	static function add($worker, $data=NULL)
	{
		$tasker = new self;
		mkpath($dir = $tasker->task_dir(), 0777);
		if(is_numeric($data) && preg_match('/^(\w+)->(\w+)$/', $worker, $m))
			$file = $dir."/{$m[1]}-{$m[2]}-$data.json"; // balancer_board_forum-update_counts-3.json
		else
			$file = $dir.'/'.date('Ymd-His-').uniqid().'.json';

		file_put_contents($file, json_encode(['worker' => $worker, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
		chmod($file, 0666);
	}

/*
	{
		"worker": "balancer_board_forum->update_counts",
		"data": 6
	}
*/

	static function do_works()
	{
		$tasker = new self;
		$tasks = glob($tasker->task_dir().'/*.json');
		shuffle($tasks);
		foreach($tasks as $file)
		{
			$content = file_get_contents($file);
			$task_data = json_decode($content, true);
			if(!$task_data)
			{
				\bors_debug::syslog('error-tasks-airbase', "Can't decode json in '$file': '".$content."'");
				continue;
			}

			$worker = $task_data['worker'];
			$data	= $task_data['data'];

			if(preg_match('/^(\w+)->(\w+)$/', $worker, $m))
			{
				list($worker_class, $worker_method) = [$m[1], $m[2]];

				$worker_method = 'do_work_'.$worker_method;

				if(is_array($data))
					$id = @$data['id'];
				else
					$id = $data;

				$worker = bors_load($worker_class, $id);

				if(!$worker)
				{
					\bors_debug::syslog('tasks-airbase-error', "Can't load $worker_class($id) for run ->$worker_method()");
					continue;
				}

				$error = $worker->$worker_method($data);

				if($error)
					\bors_debug::syslog('tasks-airbase-error', "Task error in '$file': $worker_class->$worker_method returned ".$error);
				else
					unlink($file);

				continue;
			}

			\bors_debug::syslog('tasks-airbase-error', "Unknown worker format in '$file': ".$worker);
		}
	}
}
