<?php
	
class common_keyword_bind extends base_page_db
{
	function main_db_storage(){ return 'BORS'; }
	function main_table_storage(){ return 'keywords_map'; }
	
    function main_table_fields()
	{
		return array(
			'id',
			'keyword_id',
			'target_class_id',
			'target_object_id',
			'target_create_time',
			'target_modify_time',
			'target_owner_id',
			'sort_order',
		);
	}

	static function add($object)
	{
		$db = new driver_mysql('BORS');
		$db->delete('keywords_map', array('target_class_id' => $object->class_id(), 'target_object_id' => $object->id()));

		foreach(explode(',', $object->keywords_string()) as $keyword)
		{
			$key = common_keyword::loader($keyword);
			
			$key->set_modify_time(time(), true);
			$key->set_targets_count(1 + $key->targets_count(), true);
		
			$new_bind = object_new_instance('common_keyword_bind', array('keyword_id' => $key->id(),
				'target_class_id' => $object->class_id(),
				'target_object_id' => $object->id(),
				'target_create_time' => $object->create_time(),
				'target_modify_time' => $object->modify_time(),
				'target_owner_id' => $object->owner_id(),
			));
		}
	}
}
