<?php

class bors_task_statCacheRecreate extends base_empty
{
	function execute()
	{
		bors_object_create($this->id());
	}
}
