<?php

class bors_class_loader_yaml extends bors_class_loader_meta
{
	static function load($class_name, $class_file)
	{
		$parse = bors_data_yaml::load($class_file);
		if(!$parse)
			return NULL;

		$data = $parse['data'];

		if(!$data)
			bors_throw('Empty YAML class data');

		$funcs = array();

		if($properties = popval($data, 'properties'))
		{
//			var_dump($properties);
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

//		if($table_fields)
//			$data['storage_engine'] = popval($data, 'storage_engine', 'bors_storage_mysql');

//		if(preg_match('/entity/', $class_name)) var_dump($data);
		$class = "class ".popval($data, 'class', $class_name)." extends ".popval($data, 'extends', config('project.name').($properties ? '_object_db' : '_page'))
			."\n{\n";

		if($table_fields)
		{
			$class .= "\tfunction table_fields()\n\t{\n\t\treturn array(\n"
				.self::tr_array($table_fields, 3)
				."\n\t\t);\n\t}\n";
		}

		if(empty($data['class_file']))
			$data['class_file'] = $class_file;

		foreach($data as $key => $value)
		{
			if(is_array($value))
			{
				$value = "array(\n".self::tr_array($value, 3)."\n\t\t)";
				// fields[]: values — это добавляемый к parent массив
				if(preg_match('/^(\w+)\[\]$/', $key, $m))
				{
					$key = $m[1];
					$value = "parent::$key() + $value";
				}

				$class .= "\n\tfunction $key()\n\t{\n\t\treturn $value;\n\t}\n";
				continue;
			}

			$args = '';

			if(preg_match('/^(\w+)\(\)$/', $key, $m)) // function(): ...
				$key = $m[1];
			elseif(preg_match('/^(\w+)&parent\(\)$/', $key, $m)) // function&parent(): ... { ... return parent::function() }
			{
				$function = $m[1];
				$class .= "\n\tfunction $function() { $value; return parent::$function(); }\n";
				continue;
			}
			elseif(preg_match('/^(\w+) \( ([^\)]+) \)$/x', $key, $m)) // function($arg): ...
			{
				$key = $m[1];
				$args = $m[2];
			}
			elseif(preg_match('/^\w+$/', $value))
				$value = "'".addslashes($value)."'";
			else
				$value = "ec('".addslashes($value)."')";

			$class .= "\n\tfunction $key($args) { return $value; }\n";
		}

		if(file_exists($inc_php = str_replace('.yaml', '.inc.php', $class_file)))
			$class .= preg_replace('/^<\?php/', '', file_get_contents($inc_php));

		$class .= "}\n";

//		echo "\n====================\n$class\n======================\n";

//		$generated_name = dirname($class_file)."/".array_pop(explode('_', $class_name)).".php";
		$cached_class_file = config('cache_dir').'/classes/'.str_replace('_', '/', $class_name).'.php';

		mkpath(dirname($cached_class_file), 0750);
		@file_put_contents($cached_class_file, "<?php\n\n".$class);
		@chmod($generated_name, 0640);

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
					$s .= "'".addslashes($key)."' => '".addslashes($val)."',";

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
