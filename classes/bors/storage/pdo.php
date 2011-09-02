<?php

/**
	Бэкенд, использующий для инициализации объектов PHP PDO
*/

class bors_storage_pdo extends bors_storage implements Iterator
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

		$db_driver_name = $this->_db_driver_name();
		return $this->__dbh = new $db_driver_name($this->__db_name);
	}

	function load($object)
	{
		$select = array();
		$post_functions = array();
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];
			if($f['name'] != $f['property'])
				$x .= " AS `{$f['property']}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

		$where = array('`'.$object->id_field().'`=' => $object->id());

		$dummy = array();
		self::__join('inner', $object, $select, $where, $post_functions, $dummy);
		self::__join('left',  $object, $select, $where, $post_functions, $dummy);

		$dbh = new driver_pdo($object->db_name());
		$data = $dbh->select($object->table_name(), join(',', $select), $where);

		if(!$data)
			return $object->set_loaded(false);

		$object->data = $data;

		if(!empty($post_functions))
			self::post_functions_do($object, $post_functions);

		$object->set_loaded(true);

//		print_d($data);

		return true;
	}

	function load_array($object, $where)
	{
		$object->storage()->storage_create();

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

		$db_driver_name = $this->_db_driver_name();
		$dbh = new $db_driver_name($db_name);

		$datas = $dbh->select_array($table_name, join(',', $select), $where, $class_name);

		$objects = array();

		foreach($datas as $data)
		{
			$object->set_id($data['id']);
			$object->data = $data;
			$object->set_loaded(true);
			$objects[] = $object;
			$object = new $class_name(NULL);
		}

		return $objects;
	}

	function count($object, $where)
	{
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

		$dbh = new driver_pdo($db_name);

		$count = $dbh->select($table_name, 'COUNT(*)', $where, $class_name);
		if(!empty($where['group']))
			$count = intval($dbh->get('SELECT FOUND_ROWS()'));

		return $count;
	}

	static private function __query_data_prepare($object, $where)
	{
		$select = array();
		$post_functions = array();
		$db_driver_name = $object->storage()->_db_driver_name();

		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];

			if($load = $db_driver_name::load_sql_function($f['type']))
			{
				$x = sprintf($load, $f['name'])." AS {$f['property']}";
			}
			elseif($f['name'] != $f['property'])
			{
				$x .= " AS `{$f['property']}`";
				if(array_key_exists($f['property'], $where))
				{
					$where[$f['name']] = $where[$f['property']];
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

//		$dbh = new driver_pdo($object->db_name());
		return array($select, $where, $post_functions);
	}

	static private function __update_data_prepare($object, $where, $dbh = NULL)
	{
		$update = array();
		$db_name = $object->db_name();
		$table_name = $object->table_name();

		$db_driver_name = $object->storage()->_db_driver_name();

		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];

			if($save = $db_driver_name::save_sql_function($f['type']))
				$direct_sql = sprintf($save, $object->get($f['property']));
			else
				$direct_sql = false;

//			Сюда сунуть обратное преобразование
//			if(!empty($f['post_function']))
//				$post_functions[$f['property']] = $f['post_function'];

			if(preg_match('/^(\w+)\(([\w`]+)\)$/', $f['name'], $m))
			{
				$f['name'] = $m[2];
				$sql = $_back_functions[$m[1]];
			}
			else
				$sql = false;

			if(array_key_exists($f['property'], $object->changed_fields))
			{
				if($direct_sql)
					$update[$db_name][$table_name]['raw '.$f['name']] = $direct_sql;
				elseif($sql)
					$update[$db_name][$table_name][$f['name']] = $sql.'('.$object->get($f['property']).')';
				else
					$update[$db_name][$table_name][$f['name']] = $object->get($f['property']);
			}
		}

		$select = array(); // dummy
		self::__join('inner', $object, $select, $where, $post_functions, $update);
		self::__join('left',  $object, $select, $where, $post_functions, $update);

		if(empty($update[$db_name][$table_name][$object->id_field()]))
			$update[$db_name][$table_name][$object->id_field()] = $object->id();

//		$dbh = new driver_pdo($object->db_name());
		return array($update, $where);
	}

	function save($object)
	{
		$where = array($object->id_field() => $object->id());
		list($update, $where) = self::__update_data_prepare($object, $where);

		$update_plain = array();
		foreach($update as $db_name => $tables)
		{
			foreach($tables as $table_name => $fields)
			{
				unset($fields['*id_field']);
				$update_plain = array_merge($update_plain, $fields);
			}
		}

		unset($update_plain[$object->id_field()]);

		if(!$update_plain)
			return;

		$db_driver_name = $object->storage()->_db_driver_name();
		$dbh = new $db_driver_name($object->db_name());
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

		$iterator = new bors_storage_pdo();
		$iterator->object = $object;
		$iterator->__class_name = $class_name;
		$iterator->dbi = driver_pdo::factory($db_name)->each($table_name, join(',', $select), $where);
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
						$x = "$t.`{$field['name']}`";//FIXME: предусмотреть возможность подключать FUNC(`field`)
						if($field['name'] != $field['property'])
							$x .= " AS `{$field['property']}`";

						$select[] = $x;

						if(!empty($field['post_function']))
							$post_functions[$field['property']] = $field['post_function'];

						if(!empty($object->changed_fields) && array_key_exists($field['property'], $object->changed_fields))
							$update[$db_name][$table_name][$field['name']] = $object->get($field['property']);
					}
				}
			}
		}
	}

	function _db_driver_name() { return 'driver_pdo'; }

	function create($object)
	{
		$where = array();
		list($data, $where) = self::__update_data_prepare($object, $where);

		if(!$data)
			return;

		$main_table = true;
		$db_driver_name = $this->_db_driver_name();

		foreach($data as $db_name => $tables)
		{

			$dbh = new $db_driver_name($db_name);
			foreach($tables as $table_name => $fields)
			{
				if($main_table)
				{
					unset($fields[$object->id_field()]);
				}
				else
				{
					$id_field = $fields['*id_field'];
					unset($fields['*id_field']);
					$fields[$id_field] = $new_id;
				}

				$object->storage()->storage_create();

				$dbh->insert($table_name, $fields);
				if($main_table)
				{
					$main_table = false;
					$new_id = $dbh->last_id();
				}
			}
		}

		$object->set_id($new_id);
	}

	function delete($object)
	{
		$object->on_delete();

		$update = array();
		$where  = array();
		$select = array();
		$post_functions = array();
		self::__join('inner', $object, $select, $where, $post_functions, $update);
		self::__join('left',  $object, $select, $where, $post_functions, $update);

		foreach($update as $db_name => $tables)
		{
			$dbh = new driver_pdo($db_name);
			foreach($tables as $table_name => $fields)
				$dbh->delete($table_name, array($fields['*id_field'] => $object->id()));
		}

			$dbh = new driver_pdo($object->db_name());
			$dbh->delete($object->table_name(), array($object->id_field() => $object->id()));
	}

	function _fields_types()
	{
		return bors_throw("Undefined PDO fields map (dsn=$dsn) for class '$class_name'");
	}

	function create_table($class_name = NULL)
	{
/*			case 'mysql':
				$map = array(
					'string'	=>	'VARCHAR(255)',
					'text'		=>	'TEXT',
					'int'		=>	'INT',
					'uint'		=>	'INT UNSIGNED',
					'bool'		=>	'TINYINT(1) UNSIGNED',
					'float'		=>	'FLOAT',
					'enum'		=>	'ENUM(%)',

					'*autoinc'	=>	'AUTO_INCREMENT',
					'*primary_in_field'	=> '',
					'*primary_post_field'	=> 'PRIMARY KEY (%FIELD%)',
				);
				break; */

		$map = $this->_fields_types();

		if($class_name)
		{
			$object = new $class_name(NULL);
			$db_name = $object->db_name();
			$table_name = $object->table_name();
			$db_driver_name = $this->_db_driver_name();
			$db = new $db_driver_name($db_name);
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

		foreach($fields as $field)
		{
			$db_field = ''.$field['name'].' '.$map[$field['type']];
			if($field['property'] == 'id')
			{
				if($decl = $map['*id_field_declaration'])
					$db_field = sprintf($decl, $field['name']);
				 //$map['*primary_in_field'].' '.$map['*autoinc'];
				$primary = $field['name'];
			}

			$db_fields[$db_field] = $db_field;
		}

		if(empty($primary))
			return bors_throw(ec("Не найден первичный индекс для ").print_r($object_fields, true));

		if($map['*primary_post_field'])
			$db_fields[] = str_replace('%FIELD%', $primary, $map['*primary_post_field']);

		$query = "CREATE TABLE IF NOT EXISTS $table_name (".join(', ', array_values($db_fields)).");";

		$db_driver_name = $this->_db_driver_name();
		$db = new $db_driver_name($db_name);
		$db->exec($query);
//		$db->close();
	}

	static function drop_table($class_name)
	{
		if(!config('can-drop-tables'))
			return bors_throw(ec('Удаление таблиц запрещено'));

		$class = new $class_name(NULL);
		foreach($class->fields() as $db_name => $tables)
		{
			$db = new driver_pdo($db_name);

			foreach($tables as $table_name => $fields)
			{
				if(preg_match('/^(\w+)\((\w+)\)$/', $table_name, $m))
					$table_name = $m[1];

				$db->query("DROP TABLE IF EXISTS $table_name");
//				$db->close();
			}
		}
	}

	function storage_create()
	{
		if(config('pdo_tables_autocreate', true) && !$this->storage_exists())
			$this->create_table();
	}

	function storage_exists()
	{
		static $exists = array();
		$table = $this->__table_name;

		if(array_key_exists($table, $exists))
			return $exists[$table];

		$db = $this->db();
		//FIXME: осторожно! Нужно придумать универсальный способ pdo-escape для имён, не значений!

		try
		{
			$db->get("SELECT 1 FROM $table");
			$exists = true;
		}
		catch(Exception $e)
		{
			$exists = false;
		}

		return $exists;
	}
}
