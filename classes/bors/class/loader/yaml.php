<?php

class bors_class_loader_yaml extends bors_class_loader_meta
{
	static function load($class_name, $class_file)
	{
		$parse = bors_data_yaml::load($class_file);
		if(!$parse)
			return NULL;

		$data = $parse['data'];

//		echo "Load class $class_name ($class_file)\n";
		$funcs = array();

		if($properties = popval($data, 'properties'))
		{
			$table_fields = array();
			foreach($properties as $p)
			{
				$fields = array();
				if(preg_match('!^(.+) // (.+)$!', $p, $m))
				{
					$fields['title'] = trim($m[2]);
					$p = trim($m[1]);
				}
				if(preg_match('!^(\w+)\[(\w+)\]$!', $p, $m))
				{
					$fields['class'] = trim($m[2]);
					$p = trim($m[1]);
				}
//				$fields['name'] = $p;

				$table_fields[$p] = $fields;
			}

			$data['table_fields'] = $table_fields;
		}

		$table_fields = popval($data, 'table_fields');

		if($table_fields)
			$data['storage_engine'] = popval($data, 'storage_engine', 'bors_storage_mysql');

		$class = "class ".popval($data, 'class', $class_name)." extends ".popval($data, 'extends', $properties ? 'base_object_db' : 'bors_object')
			."\n{";

		if($table_fields)
		{
			$class .= "\tfunction table_fields()\n\t{\n\t\t	return array("
				.self::tr_array($table_fields, 3)
				."\n\t\t);\n\t}\n";
		}

		foreach($data as $key => $value)
		{
			if(is_array($value))
			{
				$class .= "\n\tfunction $key()\n\t{\n\t\treturn array(\n".self::tr_array($value, 3)."\n\t\t);\n\t}\n";
				continue;
			}

			if(preg_match('/^\w+$/', $value))
				$value = "'".addslashes($value)."'";
			else
				$value = "ec('".addslashes($value)."')";

			$class .= "\n\tfunction $key() { return $value; }\n";
		}

		$class .= "}\n";

//		$generated_name = dirname($class_file)."/".array_pop(explode('_', $class_name)).".php";
		$cached_class_file = config('cache_dir').'/classes/'.str_replace('_', '/', $class_name).'.php';

		mkpath(dirname($cached_class_file), 0755);
		@file_put_contents($cached_class_file, "<?php\n\n".$class);
		@chmod($generated_name, 0644);

//		eval($class);
		bors_class_loader::cache_make_info($class_name, $class_file, $cached_class_file);
		require($cached_class_file);
		return $class_file;
	}

	function tr_array(&$data, $tabs)
	{
		$res = array();
		foreach($data as $key => $val)
		{
			$s = str_repeat("\t", $tabs);
			if(is_array($val))
			{
				$s .= "'".addslashes($key)."' => ".self::array2str($val).",";
			}
			else
			{
				if(is_numeric($key))
					$s .= "'".addslashes($val)."',";
				else
					$s .= "'".addslashes($key)."' => ".$val.",";

			}

			$res[] = $s;
		}

		return join("\n", $res);
	}

	function array2str($arr)
	{
		$res = "";
		foreach($arr as $key => $value)
		{
			if($res)
				$res .= ", ";
			$res .= "'".addslashes($key)."' => ";
			if(is_array($value))
				$res .= self::array2str($value);
			else
			{
				if(preg_match('/^\w+$/', $value))
					$res .= "'".addslashes($value)."'";
				else
					$res .= "ec('".addslashes($value)."')";
			}
		}

		return "array($res)";
	}
}
