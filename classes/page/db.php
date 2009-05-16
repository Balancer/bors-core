<?php

class page_db extends base_page_db
{
	function storage_engine() { return 'storage_db_mysql_smart'; }
	function render_engine(){ return 'render_page'; }
	function body_engine()	{ return 'body_source'; }
	function admin_engine()	{ return 'bors_admin_engine_page'; }

	function main_db() { return config('main_bors_db'); }
	function main_table() { return 'bors_pages'; }
	function main_table_fields()
	{
		return array(
			'id',
			'main_url',
			'title',
			'description',
			'description_html',
			'source',
			'source_html',
			'parents_string_db',
			'create_time',
			'modify_time',
			'owner_id',
			'last_editor_id',
			'visits',
			'first_visit_time',
			'last_visit_time',
		);
	}

	static function id_prepare($id)
	{
		if(!is_numeric(rtrim($id, '/')))
		{
			$db = new driver_mysql(self::main_db());
			$object = objects_first('page_db', array('main_url' => $id));
			$db->close();
			return $object;
		}

		return intval($id);
	}

	function parents()
	{
		if(!($p = explode("\n", $this->parents_string_db())))
		{
//			if($p = objects_array('bors_parent', array('child_class_id' => $this->class_id(), 'child_object_id', $this->id())))
//			$this->set_parents_string_db(join("\n", $p), true);
		}
		return $p ? $p : parent::parents();
	}

	function set_parents($parents, $db_up)
	{
		$this->set_parents_string_db(join("\n", str_replace("\r", "", $parents)), $db_up);
		return; // TODO: проблема в сохранении в parents-db страниц с нечисловыми ID (xml/flat/etc)
		$db = new driver_mysql(self::main_db());

		objects_delete('bors_parent', array(
			'child_class_id' => $this->class_id(),
			'child_object_id', $this->id(),
		));

		foreach($parents as $p)
		{
			if($pobj = object_load($p))
			{
				object_new_instance('bors_parent', array(
					'child_class_id' => $this->class_id(),
					'child_object_id', $this->id(),
					'parent_class_id' => $pobj->class_id(),
					'parent_object_id' => $pobj->object_id(),
				));
			}
		}
	}

	function url() { return $this->main_url(); }
}
