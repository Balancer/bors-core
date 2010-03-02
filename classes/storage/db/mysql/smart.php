<?php

class storage_db_mysql_smart extends base_null
{
	function load(&$object, $common_where = NULL, $only_count = false, $args=array())
	{
		if(!($common_where || $only_count) && (!$object->id() || is_object($object->id())))
			return false;

		$oid = addslashes(isset($args['object_id']) ? $args['object_id'] : $object->id());
		$by_id = @$args['by_id'];

		$result = array();

		global $stdbms_cache;

		$hash = md5(join('!', array($object->class_name(), $common_where, $only_count)));

		$need_convert = $object->db_charset() != $object->internal_charset();

		foreach($object->fields() as $db => $tables)
		{
			$tab_count = 0;
			$select = array();
			$from = '';
			$where = $common_where;
			$first_name = '';
			$added = array();
			$main_id_name = '';

			$dbh = new driver_mysql($db);

			$dbhash = $hash.$db;
			if(empty($stdbms_cache[$dbhash]))
			{
			  $is_one_table = (count($tables) == 1) && !preg_match('!JOIN!i', $common_where);

			  foreach($tables as $table_name => $fields)
			  {
				if(strpos($table_name, '(') && preg_match('!^(\w+)\((\w+)\)$!', $table_name, $m)) // table(id)
				{
					$table_name	= $m[1];
					$def_id		= $m[2];
				}
				else
					$def_id		= 'id';

				if(empty($main_id_name))
					$main_id_name = $def_id;

				$on = '';
				if(preg_match('!^(.*?(\w+))\((\w+)\)`?$!', $table_name, $m))
				{
					$table_name = $m[1];
					$on = "{$m[2]}.$def_id = $main_tab.{$m[3]}";
				}
				
				if(preg_match('!^inner\s+(.+?)$!', $table_name, $m))
				{
					$table_name = $m[1];
					$join = ' INNER JOIN `';
				}
				else
					$join = ' LEFT JOIN `';
					
				foreach($fields as $property => $field)
				{
					if(is_numeric($property))
						$property = $field;

					if($property == 'id')
						$main_id_name = $def_id = $field;
					
/*					// Если у нас после поля идёт его описание.
					// 
					if(preg_match('!^(\S+)\s+(.+)$!', $field, $m))
					{
						$field = $m[1];
						$object->set_property_description($property, $m[2]);
					}
*/					

					// Выделяем имя функции постобработки, передаваемом в виде
					// 'WWW.News.Header(ID)|html_entity_decode($str)'
					// --------------------^^^^^^^^^^^^^^^^^^^^^^^^^-
					if(preg_match('!^(.+)(\|.+)$!', $field, $m))
					{
						$field		= $m[1];
						$php_func	= $m[2];
					}
					else
						$php_func 	= '';

					// Выделяем имя SQL-функции, передаваемом в виде
					// 'UNIX_TIMESTAMP(WWW.News.Date(ID))
					// -^^^^^^^^^^^^^^^-----------------^
					$sql_func	= false;

					// XXX(xxx.xxx(...))
					if(preg_match('!^(\w+) \( ([\w\.]+\(.+\)) \)$!x', $field, $m))
					{
						$field		= $m[2];
						$sql_func	= $m[1];
					}
					elseif(preg_match('!^(\w+) \( ([\w\.]+) \)$!x', $field, $m))
					{
						// XXX(xxx.xxx)
						$field		= $m[2];
						$sql_func	= $m[1];
					}

					if(preg_match('!^(\w+)\(([^\(\)]+)\)$!x', $field, $m))
					{
						$id_field = $m[2];
						$field = $m[1];
					}
					else
						$id_field = $def_id;


					if(empty($added[$table_name.'-'.$id_field]))
					{
						$added[$table_name.'-'.$id_field] = true;
						
						if($is_one_table)
						{
							$current_tab = '';
							$current_tab_prefix = '';
						}
						else
						{
							$current_tab = '`'.$table_name.'`';
							$ids[$current_tab] = $def_id;
							$tab_names[$tab_count++] = $current_tab;
							if(empty($main_tab))
								$main_tab = $current_tab;
							$current_tab_prefix = "{$current_tab}.";
						}
						
						if(empty($from))
						{
							$from = ($is_one_table || "`{$table_name}`" == $current_tab) ? "FROM `{$table_name}`" : "FROM `{$table_name}` AS {$current_tab}";
							if(!$where && !$only_count)
								$where = 'WHERE '.make_id_field($current_tab, $id_field);
						}
						else
						{
							if($common_where !== NULL)
							{
								if(!$on)
									$on = "$current_tab.$id_field = $main_tab.`".$ids[$main_tab]."`";
							}
						 	else
								$on	= make_id_field($current_tab, $id_field);

							if($table_name != $current_tab)
								$from .= $join.$table_name.'` AS '.$current_tab.' ON ('.$on.')';
							else
								$from .= $join.$table_name.'` ON ('.$on.')';
						}
					}

					$qfield = $field;
					if(preg_match('!^\w+$!', $field))
						$qfield = "`{$field}`";

					if($sql_func)
						$select[] = "{$sql_func}({$current_tab_prefix}{$qfield}) AS `{$property}{$php_func}`";
					else
						$select[] = $current_tab_prefix.($field == $property && !$php_func? $qfield : "{$field} AS `{$property}{$php_func}`");
				}
			  }

			  if($common_where !== NULL)
			  {
			  	$sel = NULL;
			 	if(@$ids[$main_tab] && $ids[$main_tab] != 'id')
				{
					if($is_one_table)
						$sel = $ids[$main_tab];
					else
						$sel = "$main_tab.{$ids[$main_tab]}";
				}

				if($sel)
					$select[] = $sel.' AS id';
			  }
			  else
				$where .= ' LIMIT 1';

				if(preg_match("/^\w+$/", $main_id_name))
				{
				  $stdbms_cache[$dbhash]['select'] = $select;
				  $stdbms_cache[$dbhash]['from'] = $from;
				  $stdbms_cache[$dbhash]['where'] = $where;
				  $stdbms_cache[$dbhash]['id_field'] = @$id_field;
				}
			}
			else
			{
			  $select = $stdbms_cache[$dbhash]['select'];
			  $from = $stdbms_cache[$dbhash]['from'];
			  $where = $stdbms_cache[$dbhash]['where'];
			  $id_field = $stdbms_cache[$dbhash]['id_field'];
			}

			$from  = str_replace('%MySqlStorageOID%', $oid, $from);
			$where = str_replace('%MySqlStorageOID%', $oid, $where);

			if($by_id && !preg_match('/^[a-z_]+$/', $by_id))
				$by_id = 'id';

			$q = $from.' '.$where;
			if(preg_match('/^(.*FROM.*) (LEFT JOIN.*) (USE INDEX.*) (WHERE.*)$/', $q, $m))
				$q = "{$m[1]} {$m[3]} {$m[2]} $m[4]";

			if($only_count)
			{
				$cnt = intval($dbh->get('SELECT COUNT(*) '.$q, false));
				$dbh->close();
				return $cnt;
			}
			else
			{
				if(!$select)
					return NULL;

				if(preg_match("/^(.+WHERE )additional_fields='(.+?)' AND (.+)$/", $q, $m))
				{
					$select = array_merge($select, explode(',', $m[2]));
					$q = $m[1].$m[3];
				}

				$dbh->query('SELECT '.join(',', $select).' '.$q, false);
			}

			$was_loaded = false;
			while($row = $dbh->fetch_row())
			{
				foreach($row as $name => $value)
				{
					if($pos = strpos($name, '|'))
					{
						list($name, $fn) = explode('|', $name);
						$value = $this->do_func($fn, $value);
					}

//					if(is_numeric($value) && "".($x = intval($value)) === "$value")
//						$value = $x;

					if($need_convert && $value)
						$value = $object->cs_d2i($value);

//					echo "$object -> set_{$name}($value)<br/>";
//					$object->data[$name] = $value;
					$object->{"set_$name"}($value, false, true);
//					$object->set($name, "$value", false, true);

					$was_loaded = true;
				}

				$object->set_loaded($was_loaded);
				save_cached_object($object);

				if($common_where)
				{
					if($object->loaded()) // метод может переопределяться для проверки данных
						if($by_id)
							$result[$object->$by_id()] = $object;
						else
							$result[] = $object;

					$class = get_class($object);
					$object = &new $class(NULL);
				}
			}

			$dbh->close();
		}

		return $common_where ? $result : $was_loaded;
	}

	function do_func($func, $str)
	{
		if(!$func)
			return $str;

		if(function_exists($func))
			return $func($str);

		debug_hidden_log('func-str', "f='$func', s='$str'");
		$func = str_replace('$$$', '$str', $func);
		eval("\$value = $func;");
		return $value;
	}

	function save($object)
	{
		global $back_functions;

//		echo "Save ".get_class($object)."({$object->id()})<br/>";

		if(!$object->id() || is_object($object->id()) || empty($object->changed_fields))
			return false;

		$oid = addslashes($object->id());

//		$need_convert = $object->db_charset() != $object->internal_charset();

		foreach($object->fields() as $db => $tables)
		{
			$dbh = &new driver_mysql($db);

			foreach($tables as $table_name => $fields)
			{
				$set = array();
				$id_field = false;

				if(preg_match('!^(\w+)\((\w+)\)$!', $table_name, $m))
				{
					$table_name	= $m[1];
					$def_id		= $m[2];
				}
				else
					$def_id		= 'id';

				if(preg_match('!^inner\s+(.+?)$!', $table_name, $m))
				{
					$table_name = $m[1];
					$join = ' INNER JOIN `';
				}
				else
					$join = ' LEFT JOIN `';

				foreach($fields as $property => $field)
				{
					if(is_numeric($property))
						$property = $field;

					if($property == 'id')
						$def_id = $field;

					if(empty($object->changed_fields[$property]))
						continue;

					$value = $object->$property();

					// Выделяем имя функции постобработки, передаваемом в виде
					// 'WWW.News.Header(ID)|html_entity_decode($str)'
					// --------------------^^^^^^^^^^^^^^^^^^^^^^^^^-

					if(preg_match('!^(.+)\|(.+)$!', $field, $m))
					{
						$field		= $m[1];
						$value	= $back_functions[$m[2]]($value);
					}

//					echo "=== p: $field =|= $php_func ===</br>";

					// Выделяем имя SQL-функции, передаваемом в виде
					// 'UNIX_TIMESTAMP(WWW.News.Date(ID))
					// -^^^^^^^^^^^^^^^-----------------^
					$sql_func	= false;

					if(preg_match('!^(\w+) \( ([\w\.]+\(.+\)) \)$!x', $field, $m))
					{
						$field		= $m[2];
						$sql_func	= $back_functions[$m[1]];
					}

					if(preg_match('!^(\w+) \( ([\w\.]+) \)$!x', $field, $m))
					{
						$field		= $m[2];
						$sql_func	= $back_functions[$m[1]];
					}

//					echo "=== s: $field sf: $sql_func ===</br>\n";

					if(preg_match('!^(\w+) \( ([^\(\)]+) \)$!x', $field, $m))
					{
						$id_field = $m[2];
						$field = $m[1];
					}
					else
						$id_field = $def_id;

//					if(empty($added[$table_name.'-'.$id_field]))
//					{
//						$added[$table_name.'-'.$id_field] = true;
//						$current_tab = "`tab".($tab_count++)."`";
//						if(empty($update))
//						{
//							$update = 'UPDATE `'.$table_name.'` AS '.$current_tab;
//							$where = 'WHERE '.make_id_field($current_tab, $id_field, $oid);
//						}
//						else
//							$update .= $join.$table_name.'` AS '.$current_tab.' ON ('.make_id_field($current_tab, $id_field, $oid).')';
//					}

					if($sql_func)
						$set["raw {$field}"] = "{$sql_func}('".addslashes($value)."')";
					else
						$set["{$field}"] = $value;
				}
				
				// Закончили сбор обновляемых полей. Обновляем таблицу.
				if($id_field)
					$dbh->update($table_name, array($id_field => $oid), $set);
			}
		}

		$object->changed_fields = array();
	}

	function create($object)
	{
		global $back_functions;

		$oid = $object->id();
		$data = array();
		$replace = $object->replace_on_new_instance();

//		$need_convert = $object->db_charset() != $object->internal_charset();

		foreach($object->fields() as $db => $tables)
		{
//			echo "Database: $db; tables="; print_r($tables); echo "<br />\n";
			$dbh = new driver_mysql($db);

			$data = array();

			foreach($tables as $table_name => $fields)
			{
//				echo "Table: $table_name<br />\n";
				if(preg_match('!^(\w+)\((\w+)\)$!', $table_name, $m))
				{
					$table_name	= $m[1];
					$def_id		= $m[2];
				}
				else
					$def_id		= 'id';

				foreach($fields as $property => $field)
				{
					if(is_numeric($property))
						$property = $field;

					if($property == 'id')
						$main_id_name = $def_id = $field;

					if(empty($object->changed_fields[$property]))
						continue;

					$value = isset($data[$property]) ? $data[$property] : $object->$property();

					// Выделяем имя функции постобработки, передаваемом в виде
					// 'WWW.News.Header(ID)|html_entity_decode($str)'
					// --------------------^^^^^^^^^^^^^^^^^^^^^^^^^-
					if(preg_match('!^(.+)\|(.+)$!', $field, $m))
					{
						$field		= $m[1];
						$value	= $back_functions[$m[2]]($value);
					}

//					echo "=== p: $field == $value ===</br>\n";

					// Выделяем имя SQL-функции, передаваемом в виде
					// 'UNIX_TIMESTAMP(WWW.News.Date(ID))
					// -^^^^^^^^^^^^^^^-----------------^
					$sql_func	= false;

					if(preg_match('!^(\w+) \( ([\w\.]+\(.+\)) \)$!x', $field, $m))
					{
						$field		= $m[2];
						$sql_func	= $back_functions[$m[1]];
					}

					if(preg_match('!^(\w+) \( ([\w\.]+) \)$!x', $field, $m))
					{
						$field		= $m[2];
						$sql_func	= $back_functions[$m[1]];
					}

//					echo "=== s: $field sf: $sql_func ===</br>";

					if(preg_match('!^(\w+) \( ([^\(\)]+) \)$!x', $field, $m))
					{
						$id_field = $m[2];
						$field = $m[1];
					}
					else
						$id_field = $def_id;


					if($sql_func)
					{
						$value = $sql_func."('".addslashes($value)."')";
						$field = "raw ".$field;
					}

//					if($need_convert)
//						$value = $object->cs_i2d($value);

					$data[$table_name][$field] = $value;
				}

				if($oid)
					$data[$table_name][$def_id] = $oid;

				$tab_data = @$data[$table_name];
				if(!$tab_data)
					$tab_data = array();

				if($replace)
					$dbh->replace($table_name, $tab_data);
				else
					$dbh->insert_ignore($table_name, $tab_data);

				if(empty($oid))
					$object->set_id($oid = $dbh->last_id());
			}
		}

		$object->changed_fields = array();
	}
}

global $back_functions;
$back_functions = array(
	'html_entity_decode' => 'htmlspecialchars',
	'UNIX_TIMESTAMP' => 'FROM_UNIXTIME',
	'aviaport_old_denormalize' => 'aviaport_old_normalize',
);
