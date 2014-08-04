<?php

require_once('config.php');

config_set('tasks.manager_class_name', 'bors_tasks_gearman');

bors_task::add('bors_tests_task');
