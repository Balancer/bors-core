<?php

class bors_tools_tasks extends base_object_db
{
	function main_db_storage() { return config('bors_core_db'); }
	function main_table_storage() { return 'bors_tasks'; }
	function main_table_fields()
	{
		return array('id', 'target_class_id', 'target_object_id', 'working_class_id', 'create_time', 'execute_time', 'priority');
	}

	function target() { return object_load($this->target_class_id(), $this->target_object_id()); }
	function work()   { return object_load($this->working_class_id(), $this->target()); }
	
	static function execute_task()
	{
		$task = objects_first('bors_tools_tasks', array('target_class_id<>0 AND working_class_id<>0', 'order'=>'-priority, execute_time'));

		if(!$task)
			return false;
		
		$task->work()->execute();
		
		$task->delete();
		
		return true;
	}

	static function add_task($target_object, $worker_class_name, $execute_time = 0, $priority = 0)
	{
		$db = &new driver_mysql(bors_tools_tasks::main_db_storage());
		$db->insert_ignore(bors_tools_tasks::main_table_storage(), array(
			'target_class_id' => $target_object->class_id(),
			'target_object_id' => $target_object->id(),
			'target_object_page' => $target_object->page(),
			'working_class_id' => class_name_to_id($worker_class_name),
			'execute_time' => $execute_time,
			'priority' => $priority,
		));
	}
}
