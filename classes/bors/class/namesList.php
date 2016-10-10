<?php

class bors_class_namesList extends base_list
{
	function named_list()
	{
		$result = array();
		foreach(explode(' ', \B2\Cfg::get('class.names.createable.list', 'page_fs_xml page_fs_separate')) as $class_name)
			$result[$class_name] = $class_name;

		return $result;
	}
}
