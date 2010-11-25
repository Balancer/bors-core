<?php

class bors_tests_object_db2 extends base_object_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }

	function fields()
	{
		return array(
			config('unit-test.mysql.db') => array(
				'object_db2' => array(
					'id' => 'topic_id',
					'title',
					'create_time' => array('type' => 'int'),
					'modify_time' => array('type' => 'int'),
					'owner_id',
				),

				'object_db2_2(parent_id)' => array(
					'additional',
					'additional_remap' => 'additional',
					'topic_id' => 'tid',
					'time' => 'unixtime',
					'date' => 'FROM_UNIXTIME(unixtime)',
					'date_md5' => 'unixtime|md5',
					'html_raw' => 'html',
					'html' => 'html|stripslashes',
				),
			)
		);
	}
}
