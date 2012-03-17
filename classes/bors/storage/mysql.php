<?php

class bors_storage_mysql extends bors_storage implements Iterator
{
	function __construct($object = NULL)
	{
		if($object)
		{
			$this->__object = $object;
			$this->__db_name = $object->db_name();
			$this->__table_name = $object->table_name();
		}
	}

	function db()
	{
		if($this->__dbh)
			return $this->__dbh;

		return $this->__dbh = new driver_mysql($this->__db_name);
	}

	static private function __query_data_prepare($object, $where)
	{
		$select = array();
		$post_functions = array();

		$table = $object->table_name();
//		if(config('is_developer')) { echo "<b>Load: {$object->class_name()}</b><br/>"; print_dd($where); print_dd(bors_lib_orm::main_fields($object)); }
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			if(!empty($f['sql_function']))
				$x = $f['sql_function']."(`{$table}`.`{$f['name']}`)";
			elseif(preg_match('/^(\w+)\((\w+)\)$/', $field_name = $f['name'], $m))
				$x = $m[1].'('.$table.'.'.$m[2].')';
			elseif(preg_match('/^\w+\(.+\)$/', $field_name)) // id => CONCAT(keyword,":",keyword_id)
				$x = $field_name;
			elseif(preg_match('/^[\w`]+$/', $field_name))
				$x = $table.'.'.$field_name;
			else
				$x = $field_name;

			if($field_name != $f['property'])
			{
//				var_dump($where);
//				echo "{$f['property']} -> {$f['name']}<br/>";
				$x .= " AS `{$f['property']}`";
				if(array_key_exists($f['property'], $where))
				{
					$where[$field_name] = $where[$f['property']];
					unset($where[$f['property']]);
				}
			}

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}
//		var_dump($select);
		$dummy = array();
		self::__join('inner', $object, $select, $where, $post_functions, $dummy);
		self::__join('left',  $object, $select, $where, $post_functions, $dummy);

//		$dbh = new driver_mysql($object->db_name());
		return array($select, $where, $post_functions);
	}

	static private function __update_data_prepare($object, $where)
	{
		$_back_functions = array(
			'html_entity_decode' => 'htmlspecialchars',
			'bors_entity_decode' => 'htmlspecialchars',
			'UNIX_TIMESTAMP' => 'FROM_UNIXTIME',
			'FROM_UNIXTIME' => 'UNIX_TIMESTAMP',
			'aviaport_old_denormalize' => 'aviaport_old_normalize',
			'stripslashes' => 'addslashes',
		);

		$update = array();
		$db_name = $object->db_name();
		$table_name = $object->table_name();

		$skip_joins = false;

		foreach(bors_lib_orm::main_fields($object) as $f)
		{
//			echo "{$f['property']} => {$f['name']}: ".$object->get($f['property'])."<br/>\n";
			$field_name = $f['name'];

//			Сюда сунуть обратное преобразование
//			if(!empty($f['post_function']))
//				$post_functions[$f['property']] = $f['post_function'];

			if(!empty($f['sql_function']))
				$sql = $_back_functions[$f['sql_function']];
			elseif(preg_match('/^(\w+)\(([\w`]+)\)$/', $field_name, $m))
			{
				$field_name = $m[2];
				$sql = $_back_functions[$m[1]];
			}
			elseif(preg_match('/^\w+\(.+\)$/', $field_name)) // id => CONCAT(keyword,":",keyword_id)
			{
				$skip_joins = true;
				continue;
			}
			else
				$sql = false;

			if(!empty($object->changed_fields) && array_key_exists($f['property'], $object->changed_fields))
			{
				if($sql)
					$update[$db_name][$table_name]["raw $field_name"] = $sql.'("'.addslashes($object->get($f['property'])).'")';
				elseif(@$f['type'] == 'float')
					$update[$db_name][$table_name]["float $field_name"] = $object->get($f['property']);
				else
					$update[$db_name][$table_name][$field_name] = $object->get($f['property']);
			}
		}

		$select = array(); // dummy
		if(!$skip_joins)
		{
			self::__join('inner', $object, $select, $where, $post_functions, $update);
			self::__join('left',  $object, $select, $where, $post_functions, $update);
			if(($idf = $object->id_field()) && empty($update[$db_name][$table_name][$idf]))
				$update[$db_name][$table_name][$idf] = $object->id();
		}


//		$dbh = new driver_mysql($object->db_name());
		return array($update, $where);
	}

	static private function __join($type, $object, &$select, &$where, &$post_functions, &$update)
	{
		$where['*class_name'] = $object->class_name();

		$main_db = $object->db_name();
		$main_table = $object->table_name();
		$main_id_field = $object->id_field();

		$join = $object->get("{$type}_join_fields");
		if($join)
		{
			foreach($join as $db_name => $tables)
			{
				foreach($tables as $table_name => $fields)
				{
					if(!preg_match('/^(\w+)\((\w+)\)$/', $table_name, $m))
						throw new Exception('Unknown field format '.$table_name.' for class '.$object->class_name());

					$id_field = $m[2];
					$table_name = $m[1];

					$t = '';
					if($db_name != $main_db)
						$t = "`$db_name`.";

					$t .= "`$table_name`";

					$j = "$t ON `$main_table`.`$main_id_field` = $t.`$id_field`";
					$where[$type.'_join'][] = $j;

					$update[$db_name][$table_name]['*id_field'] = $id_field;
					foreach($fields as $property => $field)
					{
						$field = bors_lib_orm::field($property, $field);

						$x = "$t.`{$field['name']}`";

						if(!empty($field['sql_function']))
						// Если у нас это SQL-функция 'modify_time' => 'UNIX_TIMESTAMP(`modify_time`)'
							$x = $field['sql_function']."({$t}.`{$field['name']}`) AS `{$field['property']}`";
						elseif($field['name'] != $field['property'])
							$x .= " AS `{$field['property']}`";

						$select[] = $x;

						if(!empty($field['post_function']))
							$post_functions[$field['property']] = $field['post_function'];

//						echo "{$field['property']} => {$field['name']}: ".$object->get($field['property'])."<br/>\n";
						if(!empty($object->changed_fields) && array_key_exists($field['property'], $object->changed_fields))
							$update[$db_name][$table_name][$field['name']] = $object->get($field['property']);
					}
				}
			}
		}
	}

	static function post_functions_do(&$object, $post_functions)
	{
//		var_dump($post_functions);
		foreach($post_functions as $property => $function)
			$object->set_attr($property, call_user_func($function, $object->$property()));
	}

	/**********************************************************

		Загрузка одиночного объекта

	***********************************************************/

	function load($object)
	{
		$set    = popval($where, '*set');
		$must_be_configured = $object->get('must_be_configured');
		$select = array();
		$post_functions = array();

		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			if(preg_match('/^\w+$/', $sql_name = $f['sql_name']))
				$x = '`'.$sql_name.'`'; // убирать апострофы нельзя, иначе тупо не работабт поля с некорректными именами
			else
				$x = $sql_name;

			if($f['sql_name'] != ($property = $f['property']))
				$x .= " AS `{$property}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

		$where = array('`'.$object->id_field().'`=' => $object->id());

		$dummy = array();
		self::__join('inner', $object, $select, $where, $post_functions, $dummy);
		self::__join('left',  $object, $select, $where, $post_functions, $dummy);

		// формат: array(..., '*set' => 'MAX(create_time) AS max_create_time, ...')
		if($set)
			foreach(preg_split('/,\s*/', $set) as $s)
				$select[] = $s;

		$dbh = new driver_mysql($object->db_name());
		$data = $dbh->select($object->table_name(), join(',', $select), $where);

		if(!$data)
			return $object->set_loaded(false);

		$object->data = $data;

		if($must_be_configured)
			$object->_configure();

		if(!empty($post_functions))
			self::post_functions_do($object, $post_functions);

		$object->set_loaded(true);
		save_cached_object($object);

		return true;
	}

	/**********************************************************

		Загрузка массива объектов

	***********************************************************/

	function load_array($object, $where)
	{
		$by_id  = popval($where, 'by_id');
		if(!($select = popval($where, '*select')))
			$select = popval($where, 'select');

		$set    = popval($where, '*set');

		$must_be_configured = $object->get('must_be_configured');

		if(is_null($object))
		{
			$db_name = $where['*db'];
			$table_name = $where['*table'];
			unset($where['*db'], $where['*table']);
			$select = array('*');
			$class_name = 'base_object_db';
			$object = new base_object_db(NULL);
			$post_functions = array();
		}
		else
		{
			$db_name = $object->db_name();
			$table_name = $object->table_name();
			$class_name = $object->class_name();
			$class_file = $object->class_file();
			list($select, $where, $post_functions) = self::__query_data_prepare($object, $where);
		}

		$dbh = new driver_mysql($db_name);

		// формат: array(..., '*set' => 'MAX(create_time) AS max_create_time, ...')
		if($set)
			foreach(preg_split('/,\s*/', $set) as $s)
				$select[] = $s;

		$datas = $dbh->select_array($table_name, join(',', $select), $where, $class_name);
		$objects = array();

		foreach($datas as $data)
		{
			$object->set_id(@$data['id']);
			$object->data = $data;

			if($must_be_configured)
				$object->_configure();

			$object->set_loaded(true);

			if(!empty($post_functions))
				self::post_functions_do($object, $post_functions);

			if($by_id === true)
				$objects[$object->id()] = $object;
			elseif($by_id)
				$objects[$object->$by_id()] = $object;
			else
				$objects[] = $object;

			save_cached_object($object);

			$object = new $class_name(NULL);
			$object->set_class_file($class_file);
		}

		return $objects;
	}

	function count($object, $where)
	{
		$set    = popval($where, '*set'); // Не используется

		if(is_null($object))
		{
			$db_name = $where['*db'];
			$table_name = $where['*table'];
			unset($where['*db'], $where['*table']);
			$select = array('*');
			$class_name = 'base_object_db';
			$object = new base_object_db(NULL);
		}
		else
		{
			$db_name = $object->db_name();
			$table_name = $object->table_name();
			$class_name = $object->class_name();
			list($select, $where) = self::__query_data_prepare($object, $where);
		}
//var_dump($where);
		$dbh = new driver_mysql($db_name);
		if(empty($where['group']))
			$count = $dbh->select($table_name, 'COUNT(*)', $where, $class_name);
		else
		{
			$select = array('COUNT(*)');
			$grouped = false;
			if(preg_match('/^\*BY([A-Z]+)\((\w+)\)\*$/', @$where['group'], $m))
			{
				// тестировать на http://dev.forexpf.ru/news_arch/2010/10/01/
				switch($m[1])
				{
					case 'DAYS':
						$where['group'] = "YEAR(FROM_UNIXTIME({$m[2]})),MONTH(FROM_UNIXTIME({$m[2]})),DAY(FROM_UNIXTIME({$m[2]}))";
						$select[] = "DATE(FROM_UNIXTIME({$m[2]})) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
					case 'MONTHS':
						$where['group'] = "YEAR(FROM_UNIXTIME({$m[2]})),MONTH(FROM_UNIXTIME({$m[2]}))";
						$select[] = "CONCAT(YEAR(FROM_UNIXTIME({$m[2]})),'-',MONTH(FROM_UNIXTIME({$m[2]}))) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
				}
			}
			elseif(preg_match('/^\*BY([A-Z]+)\(UNIX_TIMESTAMP\((`\w+`)\)\)\*$/', @$where['group'], $m))
			{
				switch($m[1])
				{
					case 'DAYS':
						$where['group'] = "YEAR({$m[2]}),MONTH({$m[2]}),DAY({$m[2]})";
						$select[] = "DATE({$m[2]}) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
					case 'MONTHS':
						$where['group'] = "YEAR({$m[2]}),MONTH({$m[2]})";
						$select[] = "CONCAT(YEAR({$m[2]}),'-',MONTH({$m[2]})) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
				}
			}

			if($grouped)
				return $dbh->select_array($table_name, join(',',$select), $where, $class_name);

			$dbh->select_array($table_name, join(',',$select), $where, $class_name);
			$count = intval($dbh->get('SELECT FOUND_ROWS()'));
		}

		return $count;
	}

	function save($object)
	{
//		var_dump($object->data);
//		var_dump($object->id_field());
		$where = array($object->id_field() => $object->id());
//		var_dump($where);
		list($update, $where) = self::__update_data_prepare($object, $where);
//		print_dd($update);
		$update_plain = array();
		foreach($update as $db_name => $tables)
			foreach($tables as $table_name => $fields)
			{
				unset($fields['*id_field']);
				$update_plain = array_merge($update_plain, $fields);
			}

		if(!$update_plain)
			return;

		$dbh = new driver_mysql($object->db_name());
		$dbh->update($object->table_name(), $where, $update_plain);
	}

	private $data;
	private $dbi;
	private $object;
	private $__class_name;

	static function each($class_name, $where)
	{
		$object = new $class_name(NULL);
		list($select, $where) = self::__query_data_prepare($object, $where);
		$db_name = $object->db_name();
		$table_name = $object->table_name();

		$iterator = new bors_storage_mysql();
		$iterator->object = $object;
		$iterator->__class_name = $class_name;
		$iterator->dbi = driver_mysql::factory($db_name)->each($table_name, join(',', $select), $where);
		return $iterator;
	}

    public function key() { } // Not implemented

    public function current() { return $this->object; }

    public function next()
    {
		$this->data = $this->dbi->next();
		return $this->__init_object();
    }

    public function rewind()
    {
		$this->data = $this->dbi->rewind();
		return $this->__init_object();
    }

    public function valid() { return $this->data != false; }

	private function __init_object()
	{
		$data = $this->data;
		$class_name = $this->__class_name;
		$object = new $class_name($data['id']);
//		$object->set_id($data['id']);
		$object->data = $data;
		$object->set_loaded(true);
		return $this->object = $object;
	}

	function create($object)
	{
		$where = array();
		list($data, $where) = self::__update_data_prepare($object, $where);

		if(!$data)
			return;

		$main_table = true;
		$new_id = NULL;

		foreach($data as $db_name => $tables)
		{
			$dbh = new driver_mysql($db_name);
			foreach($tables as $table_name => $fields)
			{
				if(!$main_table)
				{
					$id_field = $fields['*id_field'];
					unset($fields['*id_field']);
					$fields[$id_field] = $new_id;
				}

//				debug_hidden_log("inserts", "insert $table_name, ".print_r($fields, true));

				$object->storage()->storage_create();

				if($object->replace_on_new_instance() || $object->attr('__replace_on_new_instance'))
					$dbh->replace($table_name, $fields);
				elseif($object->ignore_on_new_instance())
					$dbh->insert_ignore($table_name, $fields);
				else
				{
					if($object->get('insert_delayed_on_new_instance'))
						$fields['*DELAYED'] = true;

					$dbh->insert($table_name, $fields);
				}

				if($main_table && !$object->get('insert_delayed_on_new_instance'))
				{
					$main_table = false;
					$new_id = $dbh->last_id();
					if(!$new_id)
						$new_id = $object->id();
					if(!$new_id && ($idf = $object->id_field()))
						$new_id = $object->get($idf);
					if(!$new_id)
						debug_hidden_log('_orm_error', "Can't get new id on new instance for ".$object->debug_title()."; data=".print_r($object->data, true));
				}
			}
		}

		$object->set_id($new_id);
//		echo "New id=$new_id, {$object->id()}<br/>";
//		exit();
	}

	function delete($object)
	{
		if(method_exists($object, 'on_delete'))
			$object->on_delete();

		$update = array();
		$where  = array();
		$select = array();
		$post_functions = array();
		self::__join('inner', $object, $select, $where, $post_functions, $update);
		self::__join('left',  $object, $select, $where, $post_functions, $update);

		foreach($update as $db_name => $tables)
		{
			$dbh = new driver_mysql($db_name);
			foreach($tables as $table_name => $fields)
				$dbh->delete($table_name, array($fields['*id_field'] => $object->id()));
		}

			$dbh = new driver_mysql($object->db_name());
			$dbh->delete($object->table_name(), array($object->id_field() => $object->id()));
	}

	function create_table($class_name = NULL)
	{
		$map = array(
			'string'	=>	'VARCHAR(255)',
			'text'		=>	'TEXT',
			'timestamp'	=>	'TIMESTAMP',
			'int'		=>	'INT',
			'uint'		=>	'INT UNSIGNED',
			'bool'		=>	'TINYINT(1) UNSIGNED',
			'float'		=>	'FLOAT',
			'enum'		=>	'ENUM(%)',
		);

		if($class_name)
		{
			$object = new $class_name(NULL);
			$db_name = $object->db_name();
			$table_name = $object->table_name();
			$db = new driver_mysql($db_name);
		}
		else
		{
			$object = $this->__object;
			$db_name = $this->__db_name;
			$table_name = $this->__table_name;
			$db =$this->__dbh;
		}

		$db_fields = array();
		$primary = false;

		$fields = bors_lib_orm::main_fields($object);
//		var_dump($fields);
		foreach($fields as $field)
		{
			$db_field = '`'.$field['name'].'` '.$map[$field['type']];
			if($field['property'] == 'id')
			{
				$db_field .= ' AUTO_INCREMENT';
				$primary = $field['name'];
			}

			if(@$field['index'])
				$db_fields[] = "KEY (`{$field['name']}`)";

			$db_fields[$db_field] = $db_field;
		}

		if(empty($primary))
			return bors_throw(ec("Не найден первичный индекс для ").print_r($fields, true));

		$db_fields[] = "PRIMARY KEY (`$primary`)";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (".join(', ', array_values($db_fields)).");";

		$db->query($query);
//		$db->close();

	}

	static function drop_table($class_name)
	{
		if(!config('can-drop-tables'))
			return bors_throw(ec('Удаление таблиц запрещено'));

		$class = new $class_name(NULL);
		foreach($class->fields() as $db_name => $tables)
		{
			$db = new driver_mysql($db_name);

			foreach($tables as $table_name => $fields)
			{
				if(preg_match('/^(\w+)\((\w+)\)$/', $table_name, $m))
					$table_name = $m[1];

				$db->query("DROP TABLE IF EXISTS $table_name");
//				$db->close();
			}
		}
	}

	function storage_exists()
	{
		static $exists = array();
		$table = $this->__table_name;

		if(array_key_exists($table, $exists))
			return $exists[$table];

		$db = $this->db();
		return $exists[$table] = count($db->get_array("SHOW TABLES LIKE '".$db->escape($table)."'")) > 0;
	}

	function storage_create()
	{
		if(config('mysql_tables_autocreate') && !$this->storage_exists())
			$this->create_table();
	}

	static function add_field($class_name, $field_name)
	{
		$class = new $class_name(NULL);
		$db = new driver_mysql($class->db_name());
		$x = bors_lib_orm::parse_property($class_name, $field_name);
		$db->query("ALTER TABLE `{$class->table_name()}` ADD `$field_name` ".self::bors_type_to_sql($x['type']));
	}

	static function bors_type_to_sql($type)
	{
		static $map = array(
			'string'	=>	'VARCHAR(255)',
			'text'		=>	'TEXT',
			'timestamp'	=>	'TIMESTAMP',
			'int'		=>	'INT',
			'uint'		=>	'INT UNSIGNED',
			'bool'		=>	'TINYINT(1) UNSIGNED',
			'float'		=>	'FLOAT',
			'enum'		=>	'ENUM(%)',
		);

		return $map[$type];
	}
}
