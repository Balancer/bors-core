<?php

class bors_classes_loaders_yaml extends bors_classes_loaders_meta
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

		$data['storage_engine'] = popval($data, 'storage_engine', 'bors_storage_mysql');

		$class = "class ".popval($data, 'class', $class_name)." extends ".popval($data, 'extends', $properties ? 'base_object_db' : 'bors_object')."
{
	function table_fields()
	{
		return array(
".self::tr_array(popval($data, 'table_fields'), 3)."
		);
	}
";

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

		$generated_name = dirname($class_file)."/".array_pop(explode('_', $class_name)).".php";
		if(file_exists($generated_name))
			debug_hidden_log('__generated', "File $generated_name already exists for {$class_name}");
		else
		{
			file_put_contents($generated_name, "<?php\n// Этот файт является автоматически сгенерированным.\n// Будьте осторожны при модификациях, чтобы ничего не потерять\n\n".$class);
			chmod($generated_name, 0666);
		}
		eval($class);
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
