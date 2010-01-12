<?php

main(@$argv[1], @$argv[2]);

function main($sqlt_file, $title)
{
	if(!$sqlt_file)
		exit("Укажите файл шаблона!\n");

	$table_name = NULL;
	$fields		= array();
	$methods	= array();
	$keys		= array();

	$map = array(
		'string'	=>	'VARCHAR(255)',
		'text'		=>	'TEXT',
		'int'		=>	'INT',
		'uint'		=>	'INT UNSIGNED',
		'bool'		=>	'TINYINT(1) UNSIGNED',
		'float'		=>	'FLOAT',
	);

	foreach(file($sqlt_file) as $s)
	{
		$s = trim($s);
		if(!$s)
			continue;

		if(preg_match('/^(\w+):$/', $s, $m))
		{
			$table_name = $m[1];
			continue;
		}

		if(preg_match('/^(unique)\s+(.+?)$/', $s, $m))
		{
			$ff = array_map(create_function('$s', 'return trim($s);'), explode(',', $m[2]));
			$keys[] = 'UNIQUE `'.join('__', $ff).'` (`'.join('`,`', $ff).'`)';
			continue;
		}

		if(preg_match('/^(.+)!$/', $s, $m))
		{
			$s = trim($m[1]);
			$is_index = true;
		}
		else
			$is_index = false;

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

		if(preg_match('/^(\w+)\s+(\w+)$/', $s, $m))
		{
			$type = $map[strtolower($m[1])];
			$name = $m[2];
			if($is_index && $is_autoinc)
				$keys[] = 'PRIMARY KEY (`'.$name.'`)';
			elseif($is_index)
				$keys[] = 'KEY `'.$name.'` (`'.$name.'`)';

			$f = '`'.$name.'` '.$type;

			if($is_null)
				$f .= ' NULL';
			else
				$f .= ' NOT NULL';

			if($is_autoinc)
				$f .= ' AUTO_INCREMENT';

			$fields[] = $f;

			$names[] = $name;

			continue;
		}

		exit("Unknown string format: '$s'\n");
	}

//	echo "$table_name\n";
//	print_r($keys);
//	print_r($fields);
//	print_r($names);

$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
	".join(",\n\t", $fields).",

	".join(",\n\t", $keys)."
)
";

	if(!$title)
		$title = "Объект $table_name";


$php = "<?php\n\nclass generated_$table_name extends base_object_db
{
	function title() { return ec('$title'); }
	function main_table() { return '$table_name'; }
	function main_table_fields()
	{
		return array(
			'".join("',\n\t\t\t'", $names)."',
		);
	}
}
";

	file_put_contents(str_replace('.sqlt', '.sql', $sqlt_file), $sql);
	file_put_contents(str_replace('.sqlt', '.php', $sqlt_file), $php);
	echo "Done!\n";
}
