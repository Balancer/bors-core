<?php

class cache_static extends base_object_db
{
	function main_db_storage() { return 'CACHE'; }
	function main_table_storage() { return 'cached_files'; }
	function fields()
	{
		return array(
			'id' => 'file',
			'uri',
			'original_uri',
			'last_compile',
			'expire_time',
			'class_name',
			'class_id',
			'object_id',
		);
	}

	function object() { return object_load($this->class_id(), $this->object_id()); }

	function drop()
	{
		@unlink($this->id());
		if(file_exists($this->id()))
			return;
		
		$this->delete();
	}
	
//	static function drop_by_url()
}
