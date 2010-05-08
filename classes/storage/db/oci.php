<?php

class storage_db_oci extends storage_db
{
	function load(&$object)
	{
		$oid = intval($object->id());
		
		$result = array();

		foreach($object->fields_map_db() as $db => $tables)
		{
			if(empty($db))
			{
				print_d($object->fields_map_db());
				debug_exit("Empty db for {$object->class_name()}");
			}

			$tab_count = 0;
			$select = array();
			$from = '';
			$first_name = '';
			$added = array();
			$main_id_name = '';

			$dbh = &new driver_oci($db);

			$is_one_table = (count($tables) == 1);
			
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
							$current_tab = "`tab".($tab_count++)."`";
							$ids[$current_tab] = $def_id;
//							echo "{$ids}[{$current_tab}] = {$def_id};<br />";
							$current_tab_prefix = "{$current_tab}.";
						}
						
						if(empty($from))
						{
							$from = $is_one_table ? "FROM {$table_name}" : "FROM `{$table_name}` AS {$current_tab}";
							$where = 'WHERE '.make_id_field($current_tab, $id_field);
						}
						else
						{
						
							$on	= make_id_field($current_tab, $id_field);

							$from .= $join.$table_name.'` AS '.$current_tab.' ON ('.$on.')';
						}
					}

					$qfield = $field;
					if(preg_match('!^\w+$!', $field))
						$qfield = "{$field}";

					if($sql_func)
						$select[] = "{$sql_func}({$current_tab_prefix}{$qfield}) AS `{$property}{$php_func}`";
					else
						$select[] = $current_tab_prefix.($field == $property ? $qfield : "{$field} AS {$property}{$php_func}");
				}
			  }
			
//			$where .= ' LIMIT 1';

			$from  = str_replace('\'%MySqlStorageOID%\'', $oid, $from);
			$where = str_replace('\'%MySqlStorageOID%\'', $oid, $where);

			$dbh->query('SELECT '.join(',', $select).' '.$from.' '.$where, false);
			$dbh->execute();

			$was_loaded = false;
			while($row = $dbh->fetch())
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
}
