<?php

class bors_tools_codegen_sqlt extends bors_object
{
	var $class_name;
	var $view_class_name;
	var $admin_class_name;

	var $class_title;
	var $class_titles;

	var $fields;
	var $keys = array();

	function parse($text)
	{
		foreach(explode("\n", $text) as $s)
		{
			$arg = false;
			$skip_class_type = false;
			$s = trim($s);
			if(!$s)
				continue;

			if(!$this->class_name && preg_match('!^class:\s*(.+?)$!', $s, $m))
			{
				$this->class_name = $m[1];
				continue;
			}

			if(!$this->admin_class_name && preg_match('!^admin_class:\s*(.+?)$!', $s, $m))
			{
				$this->admin_class_name = $m[1];
				continue;
			}

			if(!$this->view_class_name && preg_match('!^view_class:\s*(.+?)$!', $s, $m))
			{
				$this->view_class_name = $m[1];
				continue;
			}

			if(!$this->class_title && preg_match('!^title:\s*(.+?)$!', $s, $m))
			{
				$this->class_title = $m[1];
				continue;
			}

			if(!$this->class_titles && preg_match('!^titles:\s*(.+?)$!', $s, $m))
			{
				$this->class_titles = $m[1];
				continue;
			}

			// Комментарии
			$field_title = false;
			$comment = false;
			if(preg_match('!^(.+?)\s*//\s*(.+?)\s* \-\- \s*(.+?)\s*$!', $s, $m))
			{
				$s = $m[1];
				$field_title = $m[2];
				$comment = $m[3];
			}
			elseif(preg_match('!^(.+?)\s*//\s*(.+?)\s*$!', $s, $m))
			{
				$s = $m[1];
				$field_title = $m[2];
			}

			if(preg_match('/^(\w+):$/', $s, $m))
			{
				$this->table_name = $m[1];
				//TODO: сделать возможность мультитаблиц
				continue;
			}

			if(preg_match('/^(unique)\s+(.+?)$/', $s, $m))
			{
				$ff = array_map(create_function('$s', 'return trim($s);'), explode(',', $m[2]));
				$this->keys[] = 'UNIQUE `'.join('__', $ff).'` (`'.join('`,`', $ff).'`)';
				continue;
			}

			if(preg_match('/^(.+)!$/', $s, $m))
			{
				$s = trim($m[1]);
				$is_index = true;
			}
			else
				$is_index = false;

			if(preg_match('/^(.+)latin1$/i', $s, $m))
			{
				$s = trim($m[1]);
				$is_latin1 = true;
			}
			else
				$is_latin1 = false;

			if(preg_match('/^(.+)NULL$/', $s, $m))
			{
				$s = trim($m[1]);
				$is_null = true;
			}
			else
				$is_null = false;

			if(preg_match('/^(.+)\+\+$/', $s, $m))
			{
				$s = trim($m[1]);
				$is_autoinc = true;
			}
			else
				$is_autoinc = false;

			if(preg_match('/^(\w+)\(((\w+)\[\S+?\])\)$/', $s, $m))
			{
				$class_auto_objects[$m[1]] = $m[3];
				$s = $m[2];
			}
			elseif(preg_match('/^(\w+)\((\w+)\)$/', $s, $m))
			{
				$class_auto_objects[$m[1]] = $m[2];
				$s = $m[2];
			}

			if(preg_match('/^(\w+)\[(\S+)\]$/', $s, $m))
			{
				$s = "enum {$m[1]}";
				$arg = array();
				foreach(explode(',', $m[2]) as $v)
					$arg[] = $v[1] == "'" ? $v : "'".addslashes($v)."'";
				$arg = join(', ', $arg);
			}

			if(preg_match('/^\w+_id$/', $s))
				$s = $skip_class_type = "int $s";

			if(preg_match('/^is_\w+$/', $s))
				$s = $skip_class_type = "bool $s";

			if(preg_match('/^\w+_date$/', $s))
				$s = $skip_class_type = "date $s";

			if(preg_match('/^\w+$/', $s))
				$s = $skip_class_type = "string $s";

			if(preg_match('/^int\s+id$/', $s))
				$skip_class_type = true;

			if(preg_match('/^(\w+)\s+(\w+)$/', $s, $m))
			{
				$field_type = strtolower($m[1]);
				$field_name = $m[2];
			}
			else
			{
				$field_type = NULL;
				$field_name = $s;
			}

			$this->fields[] = array(
				'type' => $field_type,
				'name' => $field_name,
				'field_title' => $field_title,
				'is_null' => $is_null,
				'is_latin1' => $is_latin1,
				'is_autoinc' => $is_autoinc,
				'is_index' => $is_index,
				'arg' => $arg,
				'comment' => $comment,
			);

//			echo $s."\n";
		}

//		print_d($this->fields());
	}

	function make_mysql_create()
	{
		$map = array(
			'string'	=>	'VARCHAR(255)',
			'text'		=>	'TEXT',
			'int'		=>	'INT',
			'uint'		=>	'INT UNSIGNED',
			'bool'		=>	'TINYINT(1) UNSIGNED',
			'float'		=>	'FLOAT',
			'enum'		=>	'ENUM(%)',
		);

		$sql_fields = array();
		$keys		= $this->keys;

		foreach($this->fields as $f)
		{
			$name = $f['name'];

			$sql_type = defval($map, $f['type'], 'VARCHAR(255)');

			if($f['arg'])
				$sql_type = str_replace("%", $f['arg'], $sql_type);

			if($f['is_index'] && $f['is_autoinc'])
				$keys[] = 'PRIMARY KEY (`'.$name.'`)';
			elseif($f['is_index'])
				$keys[] = 'KEY `'.$name.'` (`'.$name.'`)';

			$s = '`'.$name.'` '.$sql_type;

			if($f['is_latin1'])
				$s .= ' CHARACTER SET latin1 COLLATE latin1_general_ci';

			if($f['is_null'])
				$s .= ' NULL';
			else
				$s .= ' NOT NULL';

			if($f['is_autoinc'])
				$s .= ' AUTO_INCREMENT';

			$sql_comment = array();
			$sql_comment[] = $f['field_title'];
			$sql_comment[] = $f['comment'];

			if($sql_comment && $sql_comment[0])
				$s .= " COMMENT '".addslashes(join('. ', $sql_comment))."'";

			$sql_fields[] = $s;
		}

		$sql = "CREATE TABLE IF NOT EXISTS `".addslashes($this->table_name)."` (\n"
			."\t".join(",\n\t", $sql_fields).",\n"
			."\t".join(",\n\t", $keys)."\n"
			.");\n";

		return $sql;
	}
}
