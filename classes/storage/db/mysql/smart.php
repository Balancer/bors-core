<?php

class storage_db_mysql_smart extends base_null
{
	function load(&$object, $common_where = NULL, $only_count = false, $args=array())
	{
//		echo "Try load ".get_class($object)."({$object->id()}); common_where=$common_where; only_count=$only_count<br />\n";

		if(!($common_where || $only_count) && (!$object->id() || is_object($object->id())))
			return false;

		$oid = addslashes(isset($args['object_id']) ? $args['object_id'] : $object->id());
		$by_id = !empty($args['by_id']);
//		if(!$oid)
//			debug_exit('empty oid');
		
		$result = array();

//		echo "MySqlStorage.load: <b>{$object->internal_uri()}</b>, size=".sizeof(get_object_vars($object))."; cnt=".(++$count)."<br />";

//		echo get_class($object); print_d($object->fields());

		global $stdbms_cache;
		
		$hash = md5(join('!', array($object->class_name(), $common_where, $only_count)));
//		echo "hash for ".$object->class_name()."/$common_where =$hash<Br/>\n";

		foreach($object->fields() as $db => $tables)
		{
			if(empty($db))
			{
				print_d($object->fields());
				debug_exit("Empty db for {$object->class_name()}");
			}

			$tab_count = 0;
			$select = array();
			$from = '';
			$where = $common_where;
			$first_name = '';
			$added = array();
			$main_id_name = '';

			$dbh = &new DataBase($db);

			$dbhash = $hash.$db;
			if(empty($stdbms_cache[$dbhash]))
			{
			  $is_one_table = (count($tables) == 1) && !preg_match('!JOIN!i', $common_where);
			
			  foreach($tables as $table_name => $fields)
			  {
				if(preg_match('!^(\w+)\((\w+)\)$!', $table_name, $m)) // table(id)
				{
					$table_name	= $m[1];
					$def_id		= $m[2];
				}
				else
					$def_id		= 'id';

				if(empty($main_id_name))
					$main_id_name = $def_id;

				if(preg_match('!^inner\s+(.+?)$!', $table_name, $m))
				{
					$table_name = $m[1];
					$join = ' INNER JOIN `';
				}
				else
					$join = ' LEFT JOIN `';
					
//				echo "<small>Do $table_name => ".print_r($fields, true)." with id = '{$def_id}'</small><br/>\n";

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
					if(preg_match('!^(.+)\|(.+)$!', $field, $m))
					{
						$field		= $m[1];
						$php_func	= '|'.$m[2];
					}
					else
						$php_func 	= '';

//					echo "=== p: $field =|= $php_func ===</br>";

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

//					echo "=== s: '$field' sf: $sql_func ===</br>";
				
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
							$current_tab = '`'.$table_name.'`'; // "`tab".($tab_count++)."`";
							$ids[$current_tab] = $def_id;
//							echo "{$ids}[{$current_tab}] = {$def_id};<br />";
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
								$on = "$current_tab.$id_field = `tab0`.`".$ids['`tab0`']."`";
						 	else
								$on	= make_id_field($current_tab, $id_field);

							$from .= $join.$table_name.'` AS '.$current_tab.' ON ('.$on.')';
						}
					}

					$qfield = $field;
					if(preg_match('!^\w+$!', $field))
						$qfield = "`{$field}`";

					if($sql_func)
						$select[] = "{$sql_func}({$current_tab_prefix}{$qfield}) AS `{$property}{$php_func}`";
					else
						$select[] = $current_tab_prefix.($field == $property ? $qfield : "{$field} AS `{$property}{$php_func}`");
				}
			  }
			
			  if($common_where !== NULL)
			  {
			  	$sel = NULL;
			 	if(@$ids['`tab0`'] && $ids['`tab0`'] != 'id')
				{
					if($is_one_table)
						$sel = $ids['`tab0`'];
					else
						$sel = "`tab0`.{$ids['`tab0`']}";
				}
				
				if($sel)
					$select[] = $sel.' AS id';
			  }
			  else
				$where .= ' LIMIT 1';

			  $stdbms_cache[$dbhash]['select'] = $select;
			  $stdbms_cache[$dbhash]['from'] = $from;
			  $stdbms_cache[$dbhash]['where'] = $where;
			}
			else
			{
			  $select = $stdbms_cache[$dbhash]['select'];
			  $from = $stdbms_cache[$dbhash]['from'];
			  $where = $stdbms_cache[$dbhash]['where'];
			}
			
			$from  = str_replace('%MySqlStorageOID%', $oid, $from);
			$where = str_replace('%MySqlStorageOID%', $oid, $where);

			if($only_count)
			{
				$cnt = intval($dbh->get('SELECT COUNT(*) '.$from.' '.$where, false));
				$dbh->close();
				return $cnt;
			}
			else
			{
				if(!$select)
					return NULL;
				
				$dbh->query('SELECT '.join(',', $select).' '.$from.' '.$where, false);
			}

			$was_loaded = false;
			while($row = $dbh->fetch_row())
			{
				foreach($row as $name => $value)
				{
					if(preg_match('!^(.+)\|(.+)$!', $name, $m))
					{
						$name	= $m[1];
						$value = $this->do_func($m[2], $value);
					}
					
					$object->{"set_$name"}($value, false);

					$was_loaded = true;
				}

				$object->set_loaded($was_loaded);

				if($common_where)
				{
					if($by_id)
					{
//						echo "set {$object->id()}<br />\n";
						$result[$object->id()] = $object;
					}
					else
						$result[] = $object;

					save_cached_object($object);
					$class = get_class($object);
					$object = &new $class(NULL);
				}
			}

//			if($object)
//				$result[] = $object;

			$dbh->close();
		}

		save_cached_object($object);
		return $common_where ? $result : $was_loaded;
	}

	function do_func($func, $str)
	{
//		echo "Do $func('$str')";
		if(!$func)
			return $str;
		
		if(function_exists($func))
			return $func($str);
			
		$func = str_replace('$$$', '$str');
		eval("\$value = $func;");
		return $value;
	}

	function save($object)
	{
		global $back_functions;
		
//		echo "Save ".get_class($object)."({$object->id()})";
		
		if(!$object->id() || is_object($object->id()) || empty($object->changed_fields))
			return false;

		$oid = addslashes($object->id());
			
		foreach($object->fields() as $db => $tables)
		{
			$tab_count = 0;
			$set = array();
			$update = '';
			$where = '';
			$added = array();

			$dbh = &new DataBase($db);

			foreach($tables as $table_name => $fields)
			{
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
						
					if(empty($added[$table_name.'-'.$id_field]))
					{
						$added[$table_name.'-'.$id_field] = true;
						
						$current_tab = "`tab".($tab_count++)."`";
						if(empty($update))
						{
							$update = 'UPDATE `'.$table_name.'` AS '.$current_tab;
							$where = 'WHERE '.make_id_field($current_tab, $id_field, $oid);
						}
						else
							$update .= $join.$table_name.'` AS '.$current_tab.' ON ('.make_id_field($current_tab, $id_field, $oid).')';
					}
				
					if($sql_func)
						$set["raw {$current_tab}.{$field}"] = "{$sql_func}('".addslashes($value)."')";
					else
						$set["{$current_tab}.{$field}"] = $value;
				}
			}
	
			if($update)
				$dbh->query($update.$dbh->make_string_set($set).' '.$where, false);
		}				
		$object->changed_fields = array();
//		exit();
	}

	function create($object, $data = array(), $replace = false)
	{
		global $back_functions;

		$oid = $object->id();
		
		foreach($object->fields() as $db => $tables)
		{
//			echo "Database: $db; tables="; print_r($tables); echo "<br />\n";
			$dbh = &new DataBase($db);

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

					$data[$table_name][$field] = $value;
				}						

				if($oid)
					$data[$table_name][$def_id] = $oid;

				if($replace)
					$dbh->replace($table_name, $data[$table_name]);
				else
					$dbh->insert($table_name, $data[$table_name]);
					
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
