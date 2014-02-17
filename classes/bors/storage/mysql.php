<?php

class bors_storage_mysql extends bors_storage implements Iterator
{
	function __construct($object = NULL)
	{
		if($object)
		{
			$this->__object = $object;
			$this->__db_name = $object->get('db_name');
			$this->__table_name = $object->get('table_name');
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
		$fields = popval($where, '*fields');
//		if(config('is_developer')) { bors_use('debug/print_dd'); echo "<b>Load: {$object->class_name()}</b><br/>"; print_dd($where); print_dd(bors_lib_orm::main_fields($object)); }
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$field_name = $f['name'];

			if(!empty($fields) && !in_array($field_name, $fields))
				continue;

			if(!empty($f['sql_function']))
				$x = $f['sql_function']."(`{$table}`.`{$field_name}`)";
			elseif(preg_match('/^(\w+)\((\w+)\)$/', $field_name, $m))
				$x = $m[1].'('.$table.'.'.$m[2].')';
			elseif(preg_match('/^\w+\(.+\)$/', $field_name)) // id => CONCAT(keyword,":",keyword_id)
				$x = $field_name;
			elseif(preg_match('/^[\w`]+$/', $field_name))
				$x = $table.'.'.$field_name;
			else
				$x = $field_name;

			if($field_name != $f['property'] || !empty($f['sql_function']))
			{
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

		$dummy = array();
		self::__join('inner', $object, $select, $where, $post_functions, $dummy);
		self::__join('left',  $object, $select, $where, $post_functions, $dummy);

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
		$db_name = $object->get('db_name');
		$table_name = $object->get('table_name');

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
				$sql = @$_back_functions[$m[1]];
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
//			self::__join('inner', $object, $select, $where, $post_functions, $update);
//			self::__join('left',  $object, $select, $where, $post_functions, $update);
//			if(($idf = $object->id_field()) && empty($update[$db_name][$table_name][$idf]))
//				$update[$db_name][$table_name][$idf] = $object->id();
			if($lefts = $object->get('left_join_fields'))
			{
				foreach($lefts as $db_name => $tables)
				{
					foreach($tables as $tab => $fields)
					{
						if(!preg_match('/^(\w+)\((\w+)\)$/', $tab, $m))
							bors_throw("Unknown left join field with tab ".$tab);

						$table_name = $m[1];
						$id_field = $m[2];

						$update[$db_name][$table_name][$id_field] = $object->id();
						$update[$db_name][$table_name]['*id_field'] = $id_field;

						foreach($fields as $key => $desc)
						{
							$f = bors_lib_orm::field($key, $desc);
							$field_name = $f['name'];

							if(!empty($f['sql_function']))
								$sql = $_back_functions[$f['sql_function']];
							elseif(preg_match('/^(\w+)\(([\w`]+)\)$/', $field_name, $m))
							{
								$field_name = $m[2];
								$sql = @$_back_functions[$m[1]];
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
					}
				}
			}
		}

//		echo "====== UPDATE ========\n";
//		print_r($object->changed_fields);
//		print_r($update);
//		$dbh = new driver_mysql($object->db_name());
		return array($update, $where);
	}

	static private function __join($type, $object, &$select, &$where, &$post_functions, &$update)
	{
		$where['*class_name'] = $object->class_name();

		$main_db = $object->get('db_name');
		$main_table = $object->get('table_name');
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
		foreach($post_functions as $property => $function)
			$object->set_attr($property, call_user_func($function, $object->data[$property]));
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

//		if(config('is_developer')) { bors_use('debug/print_dd'); echo "<b>Load: {$object->class_name()}</b><br/>"; print_dd($where); print_dd(bors_lib_orm::main_fields($object)); }
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			if(preg_match('/^\w+$/', $sql_name = $f['sql_name']))
				$x = '`'.$sql_name.'`'; // убирать апострофы нельзя, иначе тупо не работабт поля с некорректными именами
			elseif(preg_match('/^(\w+)\+(\w+)$/', $f['sql_name'], $m)) // id => forum_id+group_id
				// http://forums.airbase.ru/2008/06/t62054,12--poslednij-pokhod-unikalnogo-korablya.html
				$x = "CONCAT({$m[1]},':',{$m[2]})";
			else
				$x = $sql_name;

			if($f['sql_name'] != ($property = $f['property']))
				$x .= " AS `{$property}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

//		if(config('is_developer')) { bors_use('debug/print_dd'); echo "<b>Load: {$object->class_name()}</b><br/>"; print_dd($select); print_dd($where); }

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
			return $object->set_is_loaded(false);

		$object->data = $data;

		if($must_be_configured)
			$object->_configure();

		if(!empty($post_functions))
			self::post_functions_do($object, $post_functions);

		$object->set_is_loaded(true);
		save_cached_object($object);

		return true;
	}

	/**********************************************************

		Загрузка массива объектов

	***********************************************************/

	function load_array($object, $where)
	{
		if(!$by_id  = popval($where, 'by_id'))
			$by_id  = popval($where, '*by_id');

		if(!($select = popval($where, '*select')))
			$select = popval($where, 'select');

		$target_info = popval($where, '*join_object');

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
			foreach(preg_split('/\s*,\s*/', $set) as $s)
				$select[] = $s;

		$datas = $dbh->select_array($table_name, join(',', $select), $where, $class_name);
		$objects = array();

		foreach($datas as $data)
		{
			$object->set_id(@$data['id']);
			$object->data = $data;

			if($target_info)
			{
				foreach($target_info as $target_class_name => $info)
				{
					$target = new $target_class_name(NULL);
					foreach($info['target_properties'] as $p)
					{
						$value = $data[$p];
						$p = preg_replace('/^\w+\.(.+)$/', '$1', $p);
						if(preg_match('/^(\w+)\|(\w+)$/', $p, $m))
						{
							$p = $m[1];
							$value = $m[2]($value);
						}

						$target_data[$p] = $value;
						unset($data[$p]);
					}

					$target->set_id(@$target_data['id']);
					$target->data = $target_data;
					$object->set_attr($info['property_for_target'], $target);
				}
			}

			if($must_be_configured)
				$object->_configure();

			$object->set_is_loaded(true);

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

	/**********************************************************

		Загрузка массива смешанных объектов

	***********************************************************/

	static function load_multi_array($class_names, $fields, $where)
	{
		$union	= array();
		$post	= array();
		foreach($class_names as $class_name)
		{
			$foo = new $class_name(NULL);
			$db_name = $foo->db_name();
			$dbh = new driver_mysql($db_name);
			$table_name = $foo->table_name();
			$class_file = $foo->class_file();
			$where['*fields'] = $fields;
			list($select, $where, $post_functions) = self::__query_data_prepare($foo, $where);

			if(count($select) != count($fields))
				bors_throw(ec('У класса ').$class_name
					.ec(' не хватает нужных полей. В наличии только ').join(', ', $select)
					.ec(' при необходимых ').join(', ', $fields));

			$select[] = "'$class_name' AS `class_name`";
			$post[$class_name] = $post_functions;
			$union[] = array($table_name, join(',', $select), $where, $class_name);
		}

		$datas = $dbh->union_select_array($union);
		$objects = array();

		foreach($datas as $data)
		{
			$class_name = $data['class_name'];
			$object = new $class_name(NULL);
//			$object->set_class_file($class_file);
			$object->set_id(@$data['id']);
			$object->data = $data;

			$object->set_is_loaded(true);

			if(!empty($post[$class_name]))
				self::post_functions_do($object, $post[$class_name]);

			$objects[] = $object;

			save_cached_object($object);
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

		$dbh = new driver_mysql($db_name);
		if(empty($where['group']))
			$count = $dbh->select($table_name, 'COUNT(*)', $where, $class_name);
		else
		{
//			var_dump($where['group']); exit();
			$select = array('COUNT(*)');
			$grouped = false;
			// 'group' => '*BYMONTHS(create_time)*',
			if(preg_match('/^\*BY([A-Z]+)\((\w+)\)\*$/', @$where['group'], $m))
			{
//				var_dump($m, $class_name); exit();
				// тестировать на http://dev.forexpf.ru/news_arch/2010/10/01/
				$property = $m[2];
				$field = bors_lib_orm::property_to_field($class_name, $property);
				switch($m[1])
				{
					case 'DAYS':
						$where['group'] = "YEAR(FROM_UNIXTIME({$field})),MONTH(FROM_UNIXTIME({$field})),DAY(FROM_UNIXTIME({$field}))";
						$select[] = "DATE(FROM_UNIXTIME({$field})) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
					case 'MONTHS':
						$where['group'] = "YEAR(FROM_UNIXTIME({$field})),MONTH(FROM_UNIXTIME({$field}))";
						$select[] = "CONCAT(YEAR(FROM_UNIXTIME({$field})),'-',LPAD(MONTH(FROM_UNIXTIME({$field})),2,'0')) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
				}
			}
			elseif(preg_match('/^\*BY([A-Z]+)\(UNIX_TIMESTAMP\((`\w+`)\)\)\*$/', @$where['group'], $m))
			{
				$property = $m[2];
				$field = bors_lib_orm::property_to_field($class_name, $property);
				switch($m[1])
				{
					case 'DAYS':
						$where['group'] = "YEAR({$field}),MONTH({$field}),DAY({$field})";
						$select[] = "DATE({$field}) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
					case 'MONTHS':
						$where['group'] = "YEAR({$field}),MONTH({$field})";
						$select[] = "CONCAT(YEAR({$field}),'-',LPAD(MONTH({$field}),2,'0')) AS group_date";
						$where['*select_index_field*'] = 'group_date';
						$grouped = true;
						break;
				}
			}

			if($grouped)
				return $dbh->select_array($table_name, join(',',$select), $where, $class_name);

			$where['*fake_select'] = true;
			$dbh->select_array($table_name, join(',',$select), $where, $class_name);
			$count = intval($dbh->get('SELECT FOUND_ROWS()'));
		}

		return $count;
	}

	function save($object)
	{
//		var_dump($object->data);
//		var_dump($object->id_field());
		$idf = $object->id_field();
		if(preg_match('/\)$/', $idf))
			$where = array($idf.'=' => $object->id());
		else
			$where = array($idf => $object->id());
//		var_dump($where);
		list($update, $where) = self::__update_data_prepare($object, $where);
//		print_dd($update);

		$main_table = $object->table_name();

		foreach($update as $db_name => $tables)
		{
			$dbh = new driver_mysql($db_name);
			foreach($tables as $table_name => $fields)
			{
				if($table_name == $main_table)
					$dbh->update($table_name, $where, $fields);
				else
				{
					$id_field = $fields['*id_field'];
					unset($fields['*id_field']);
					$where = array($id_field => $object->id());
					$dbh->insert_ignore($table_name, $where);
					$dbh->update($table_name, $where, $fields);
				}
			}
		}
	}

	private $data;
	private $dbi;
	private $object;
	private $__class_name;

	static function each($class_name, $where)
	{
		$set    = popval($where, '*set');

		$object = new $class_name(NULL);
		list($select, $where) = self::__query_data_prepare($object, $where);
		$db_name = $object->db_name();
		$table_name = $object->table_name();

		// формат: array(..., '*set' => 'MAX(create_time) AS max_create_time, ...')
		if($set)
			foreach(preg_split('/,\s*/', $set) as $s)
				$select[] = $s;

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
		$object->set_is_loaded(true);
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

				// Закомментировано, так как не позволяет аплоадить изображения с ignore.
				if($main_table && !$object->get('insert_delayed_on_new_instance')/* && !$object->ignore_on_new_instance()*/)
				{
					$main_table = false;
					$new_id = $dbh->last_id();
					if(!$new_id)
						$new_id = $object->id();
					if(!$new_id && ($idf = $object->id_field()))
						$new_id = $object->get($idf);
					if(!$new_id && !$object->ignore_on_new_instance())
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
			'timestamp'	=>	'TIMESTAMP NULL',
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
			$db = $this->db();
		}

		$db_fields = array();
		$primary = false;

		$fields = bors_lib_orm::main_fields($object);

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

		if($lefts = $object->get('left_join_fields'))
		{
			foreach($lefts as $db_name => $tables)
			{
				foreach($tables as $tab => $fields)
				{
					if(!preg_match('/^(\w+)\((\w+)\)$/', $tab, $m))
						bors_throw("Unknown left join field with tab ".$tab);

					$db_fields = array();
					$table_name = $m[1];
					$id_field = $m[2];

					array_unshift($fields, $id_field);

					foreach($fields as $prop => $desc)
					{
						$f = bors_lib_orm::field($prop, $desc);
						$db_field = '`'.$f['name'].'` '.$map[$f['type']];

						if(@$f['index'])
							$db_fields[] = "KEY (`{$f['name']}`)";

						$db_fields[$db_field] = $db_field;
					}

					$db_fields[] = "PRIMARY KEY (`$id_field`)";

					$query = "CREATE TABLE IF NOT EXISTS `$table_name` (".join(', ', array_values($db_fields)).");";
					$db = new driver_mysql($db_name);
					$db->query($query);
				}

			}
		}
//		$db->close();
	}

	static function drop_table($class_name)
	{
		if(!config('can-drop-tables'))
			return bors_throw(ec('Удаление таблиц запрещено'));

		$class = bors_foo($class_name);
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

		if($lefts = $class->get('left_join_fields'))
		{
			foreach($lefts as $db_name => $tables)
			{
				$db_fields = array();

				foreach($tables as $table_name => $fields)
				{
					if(preg_match('/^(\w+)\((\w+)\)$/', $table_name, $m))
						$table_name = $m[1];

					$db->query("DROP TABLE IF EXISTS $table_name");
				}
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

	static function add_field($class_name, $field_name, $type = NULL)
	{
		$class = new $class_name(NULL);
		$db = new driver_mysql($class->db_name());

		if(!$type)
			$type = bors_lib_orm::property_type_autodetect($field_name);

		$q = "ALTER TABLE `{$class->table_name()}` ADD `$field_name` ".self::bors_type_to_sql($type)." NULL";
		echo $q.PHP_EOL;
		$db->query($q);
		bors_function_include('cache/clear_global_key');
		clear_global_key('bors_lib_orm_class_fields-0', $class_name);
		clear_global_key('bors_lib_orm_class_fields-1', $class_name);
		$class->clear_table_fields_cache();
	}

	static function del_field($class_name, $field_name, $confirm = false)
	{
		$class = new $class_name(NULL);
		$db = new driver_mysql($class->db_name());

		$q = "ALTER TABLE `{$class->table_name()}` DROP `$field_name`";
		echo $q.PHP_EOL;
		$db->query($q);
		bors_function_include('cache/clear_global_key');
		clear_global_key('bors_lib_orm_class_fields-0', $class_name);
		clear_global_key('bors_lib_orm_class_fields-1', $class_name);
		$class->clear_table_fields_cache();
	}

	static function check_properties($class_name, $properties) // ucrm_stat_counts
	{
		foreach($properties as $field_name)
			if(!self::field_exists($class_name, $field_name))
				self::add_field($class_name, $field_name);
	}

	function field_exists($class_name, $field_name)
	{
		$class = new $class_name(NULL);
		$db = new driver_mysql($class->db_name());
//		$table_fields = mysql_list_fields($class->db_name(), $class->table_name());
		$fields = $db->get_array("SHOW COLUMNS FROM `{$class->table_name()}`");
		/*
array(2) {
  [0]=> array(6) {
    ["Field"]=>	string(4) "date"
    ["Type"]=>	string(4) "date"
    ["Null"]=>  string(2) "NO"
    ["Key"]=>   string(3) "PRI"
    ["Default"]=> NULL
    ["Extra"]=> string(0) ""
  }
  [1]=>  array(6) { ...   }
}
		*/
		foreach($fields as $x)
			if($x['Field'] == $field_name)
				return $x;

		return false;
	}

	static function bors_type_to_sql($type)
	{
		static $map = array(
			'string'	=>	'VARCHAR(255)',
			'str2'		=>	'VARCHAR(2)',
			'str3'		=>	'VARCHAR(3)',
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

	static function condition_optimize($condition)
	{
		static $_php_back_functions = array(
			'UNIX_TIMESTAMP' => 'date_format_mysqltime',
		);

		if(preg_match("/^(UNIX_TIMESTAMP)\((.+?)\) BETWEEN '?(\d+)'? AND '?(\d+)'?$/i", trim($condition), $m))
		{
			if($bf = @$_php_back_functions[bors_upper($m[1])])
				$condition = "{$m[2]} BETWEEN ".$bf($m[3])." AND ".$bf($m[4]);
		}

		return $condition;
	}
}
