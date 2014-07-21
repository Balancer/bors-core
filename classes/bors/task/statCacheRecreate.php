<?php

class bors_task_statCacheRecreate extends bors_object_simple
{
	function execute()
	{
		bors_object_create($this->id());
	}
}
