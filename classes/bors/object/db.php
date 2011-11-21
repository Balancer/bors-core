<?php

// Автоопределялка параметров из описаний таблиц БД

class bors_object_db extends base_object_db
{
	private $__auto_objects = array();

	function _project_name() { return array_shift(explode('_', $this->class_name())); }
	function _access_name() { return bors_plural(array_pop(explode('_', $this->class_name()))); }

	function _item_name() { return array_pop(explode('_', $this->class_name())); }
	function _item_name_m() { return bors_plural($this->_item_name()); }

	function table_fields()
	{
		$dbh = new driver_mysql($this->db_name());
		$info = $dbh->get("SHOW CREATE TABLE ".$this->table_name());
//		var_dump($info);
//		var_dump($this->_access_name());

		$project = $this->_project_name();

		$fields = array();
		foreach(explode("\n", $info['Create Table']) as $s)
		{
			$is_null = !preg_match('/ NOT NULL /', $s);
			$args = array();

			// FOREIGN KEY (`head_id`) REFERENCES `persons` (`id`)
			if(preg_match('/FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`id`\)/', $s, $mm))
			{
				$item = bors_unplural($mm[2]);
				$item_class = "{$project}_".$item;
				$fields[$mm[1]]['class'] = $item_class;
				$fields[$mm[1]]['have_null'] = "true";
				$this->__auto_objects[preg_replace('/_[a-z]+$/', '', $mm[1])] = "$item_class({$mm[1]})";
				continue;
			}

			if(preg_match('/^\s+`(\w+)`(.*)$/', $s, $m))
			{
				$field = $m[1];
				$type = trim($m[2]);
				if(preg_match('/^(\w+)/', $type, $mm))
					$type = $mm[1];

				$args = array();

				if(preg_match("/COMMENT '(.+?)'/", $s, $mm))
					$args['title'] = $mm[1];

				switch($type)
				{
					case 'timestamp':
					case 'date':
						$args['name'] = "UNIX_TIMESTAMP(`$field`)";
						if($is_null)
							$args['can_drop'] = true;
						break;
					case 'text':
						$args['type'] = 'bbcode';
						break;
				}

				if(empty($fields[$field]))
					$fields[$field] = array();

				if(in_array($field, array('id', 'create_time', 'modify_time', 'owner_id', 'last_editor_id')))
					$args['is_editable'] = false;

				$fields[$field] = array_merge($fields[$field], $args);
			}
		}

//		var_dump($fields);

		return $fields;
	}

	function url() { return config('main_site_url').'/'.$this->_item_name_m().'/'.$this->id().'/'; }
	function admin_url() { return config('admin_site_url').'/'.$this->_item_name_m().'/'.$this->id().'/'; }

	function auto_objects()
	{
		return array_merge(parent::auto_objects(), $this->__auto_objects);
	}

	function __toString() { return $this->title(); }
}
