<?php

class bors_task_index extends bors_object_simple
{
	function execute($data)
	{
		include_once('engines/search.php');
		return bors_search_object_index($this->id(), 'replace');
	}
}
