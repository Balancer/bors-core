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

		$class = "class ".popval($data, 'class', $class_name)." extends ".popval($data, 'extends', 'bors_object')."
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
			$class .= "\tfunction $key() { return '".addslashes($value)."'; }\n";
		}

		$class .= "}\n";

//		file_put_contents('test.class.php', "<?php\n\n".$class);
		eval($class);
	}

	function tr_array(&$data, $tabs)
	{
		$res = array();
		foreach($data as $key => $val)
		{
			$s = str_repeat("\t", $tabs);
			if(is_numeric($key))
				$s .= "'".addslashes($val)."',";
			else
				$s .= "'".addslashes($key)."' => ".$val.",";

			$res[] = $s;
		}

		return join("\n", $res);
	}
}
