<?php

main(@$argv[1]);

function main($sqlt_file)
{
	if(!$sqlt_file)
		exit("Укажите файл шаблона!\n");

	if(!file_exists($sqlt_file))
		exit("Файл $sqlt_file не найден!\n");

	$table_name = NULL;
	$sql_fields	= array();
	$methods	= array();
	$keys		= array();
	$class_name	= false;
	$admin_class_name	= false;

	$class_title	= false;
	$class_titles	= false;

	$class_fields = array();
	$class_field_names = array();
	$class_auto_objects = array();

	$map = array(
		'string'	=>	'VARCHAR(255)',
		'text'		=>	'TEXT',
		'int'		=>	'INT',
		'uint'		=>	'INT UNSIGNED',
		'bool'		=>	'TINYINT(1) UNSIGNED',
		'float'		=>	'FLOAT',
		'enum'		=>	'ENUM(%)',
	);

	foreach(file($sqlt_file) as $s)
	{
		$arg = false;
		$skip_class_type = false;
		$s = trim($s);
		if(!$s)
			continue;

		if(!$class_name && preg_match('!^class:\s*(.+?)$!', $s, $m))
		{
			$class_name = $m[1];
			continue;
		}

		if(!$admin_class_name && preg_match('!^admin_class:\s*(.+?)$!', $s, $m))
		{
			$admin_class_name = $m[1];
			continue;
		}

		if(!$class_title && preg_match('!^title:\s*(.+?)$!', $s, $m))
		{
			$class_title = $m[1];
			continue;
		}

		if(!$class_titles && preg_match('!^titles:\s*(.+?)$!', $s, $m))
		{
			$class_titles = $m[1];
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
			$type = $map[$field_type];

			if($arg)
				$type = str_replace("%", $arg, $type);

			$name = $m[2];
			if($is_index && $is_autoinc)
				$keys[] = 'PRIMARY KEY (`'.$name.'`)';
			elseif($is_index)
				$keys[] = 'KEY `'.$name.'` (`'.$name.'`)';

			$f = '`'.$name.'` '.$type;

			if($is_latin1)
				$f .= ' CHARACTER SET latin1 COLLATE latin1_general_ci';

			if($is_null)
				$f .= ' NULL';
			else
				$f .= ' NOT NULL';

			if($is_autoinc)
				$f .= ' AUTO_INCREMENT';

			$sql_comment = array();
			$sql_comment[] = $field_title;
			$sql_comment[] = $comment;

			if($sql_comment && $sql_comment[0])
				$f .= " COMMENT '".addslashes(join('. ', $sql_comment))."'";

			$sql_fields[] = $f;
			$names[] = $name;

			$class_field = '';
			$class_field_params = array();
			if($field_title)
				$class_field_params['title'] = $field_title;
			if($field_type && !$skip_class_type)
				$class_field_params['type'] = $field_type;
			if($comment)
				$class_field_params['comment'] = $comment;

			if($class_field_params)
			{
				if(empty($class_field_params['title']))
					$class_field_params['title'] = $field_title;

				$class_field_params_s = array();
				foreach($class_field_params as $k => $v)
				{
					$v = addslashes($v);
					if(preg_match('/^[\w - ~]+$/', $v))
						$v = "'$v'";
					else
						$v = "ec('$v')";
					$class_field_params_s[] = "'$k' => $v";
				}

				$class_field_names[] = "'$name' => array(".join(", ", $class_field_params_s)."),";
			}
			else
				$class_field_names[] = "'$name',";

			if(empty($class_field_params['title']))
				$class_field_params['title'] = $name;

			$class_fields[$name] = $class_field_params;

			continue;
		}

		exit("Unknown string format: '$s'\n");
	}

//	echo "$table_name\n";
//	print_r($keys);
//	print_r($sql_fields);
//	print_r($names);

$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
	".join(",\n\t", $sql_fields).",

	".join(",\n\t", $keys)."
)
";

	if(!$class_title)
		$class_title = "Объект $table_name";

	if(!$class_titles)
		$class_titles = "Объекты $table_name";

	if(!$class_name)
		$class_name = 'generated_'.$table_name;

	$admin_path = str_replace('_','/',$class_name);

	$auto_objects_code = "";
	if($class_auto_objects)
	{
		$auto_objects_code = "function auto_objects()\n\t{\n\t\treturn array(\n";
		foreach($class_auto_objects as $class_name => $field_name)
		{
			if(preg_match('/^(\w+)_id$/', $field_name, $m))
				$auto_objects_code .= "\t\t\t'$m[1]' => '$class_name($field_name)',\n";
			else
				echo "Unknown auto_objects pair: $class_name => $field_name\n";
		}
	}

if(in_array('target_class_name', $names))
{
	$ftargets = "\n\tfunction auto_targets()\n\t{\n\t\treturn array_merge(parent::auto_targets(), array(\n\t\t\t'target' => 'target_class_name(target_object_id))',\n\t\t);\n\t}\n";
	$ftitle = "\tfunction title() { return \$this->target()->class_name().ec(' «').\$this->target()->title().ec('»'); }";
}
else
{
	$ftargets = '';
	$ftitle = "\tfunction title() { return ec('$class_title'); }";
}

$php = "<?php\n\nclass $class_name extends base_object_db
{
$ftitle
	function nav_name() { return ec('".mb_strtolower($class_title)."'); }
	function table_name() { return '$table_name'; }
	function table_fields()
	{
		return array(
			".join("\n\t\t\t", $class_field_names)."
		);
	}
{$auto_objects_code}
{$ftargets}
	function url(\$page=NULL) { return config('main_host_url').'/{$admin_path}/'.\$this->id().'/'; }
}
";

	file_put_contents(str_replace('.sqlt', '.sql', $sqlt_file), $sql, FILE_APPEND);
	file_put_contents(str_replace('.sqlt', '.php', $sqlt_file), $php, FILE_APPEND);

if($admin_class_name)
{
// Генерация админ-класса
$php = "<?php\n\nclass $admin_class_name extends $class_name
{
	function extends_class_name() { return '$class_name'; }
	function acl_edit_sections() { return array('*' => 4); }
	function real_object() { return \$this->__havec('real_object') ? \$this->__lastc() : \$this->__setc(object_load('$class_name', \$this->id())); }
	function is_auto_url_mapped_class() { return true; }
	function config_class() { return '{$admin_class_name}s_config'; }

	function upload_image_file(&\$file, &\$data)
	{
		if(!\$file['tmp_name'])
			return;

		\$img = object_new_instance('bors_image');
		\$img->upload(\$file, '$class_name');

		\$this->set_default_image_id(\$img->id(), true);
		unset(\$data['default_image_id']);
	}

	function check_value_conditions()
	{
		return array(
			'title'	=> ec(\"!=''|Название должно быть указано\"),
		);
	}

	function admin_url(\$page = NULL) { return config('admin_host_url').'/{$admin_path}s/'.\$this->id().'/'.(\$page && \$page != 1 ? \$page.'/' : ''); }
}
";

	file_put_contents(str_replace('.sqlt', '.admin.php', $sqlt_file), $php, FILE_APPEND);

// Генерация общей админки
$php = "<?php

class {$admin_class_name}s_main extends aviaport_admin_paged
{
	function main_class() { return '$admin_class_name'; }
	function config_class() { return '{$admin_class_name}s_config'; }
	function title() { return ec('Администрирование ".mb_strtolower($class_titles)."'); }
	function nav_name() { return ec('".mb_strtolower($class_titles)."'); }
	function is_auto_url_mapped_class() { return true; }

	function items_per_page() { return 25; }
	function order() { return '-modify_time'; }
}
";

	file_put_contents(str_replace('.sqlt', '.admin.main.php', $sqlt_file), $php, FILE_APPEND);

// Генерация HTML общей админки
$html = "{\$this->pages_links_nul()}

<table class=\"btab\">
<tr>
	<th>id</th>
	<th>название</th>
	<th>дата создания</th>
	<th>дата модификации</th>
</tr>
{foreach from=\$items item=\"x\"}
<tr><td>{\$x->id()}</td>
	<td>{\$x->admin()->imaged_titled_link()}</td>
	<td>{\$x->create_time()|short_time}</td>
	<td>{\$x->modify_time()|short_time}</td>
</tr>
{/foreach}
</table>

{\$this->pages_links_nul()}
";

	file_put_contents(str_replace('.sqlt', '.admin.main.html', $sqlt_file), $html, FILE_APPEND);

$php = "<?php

class {$admin_class_name}s_edit extends aviaport_admin_page
{
	function title() { return \$this->id() ? \$this->real_object()->title() : ec('Новое '.".mb_strtolower($class_title)."); }
	function nav_name() { return \$this->id() ? \$this->real_object()->title() : ec('новое'); }
	function config_class() { return '{$admin_class_name}s_config'; }
	function real_object() { return \$this->__havec('real_object') ? \$this->__lastc() : \$this->__setc(object_load('$class_name', \$this->id())); }
}
";

	file_put_contents(str_replace('.sqlt', '.admin.edit.php', $sqlt_file), $php, FILE_APPEND);

$html = "{form class=$admin_class_name id=\$this->id()}

<table class=\"btab w100p\">
";

foreach($class_fields as $f => $x)
	$html .= "<tr><th>{$x['title']}:</th><td>{input name=\"$f\" class=\"w100p\"}</td></tr>\n";

$html .= "<tr><td colSpan=\"2\">{submit value=\"Сохранить\" style=\"width: 100px;\"}</td></tr>
</table>

{go value=\"newpage_admin\"}
{/form}
";

	file_put_contents(str_replace('.sqlt', '.admin.edit.html', $sqlt_file), $html, FILE_APPEND);

$html = "<div class=\"side-menu\">

<ul class=\"sub-dirs\">
<li><a href=\"/{$admin_path}s/\">Главная</a></li>
<li><a href=\"/{$admin_path}s/new/\">Новое&nbsp;".mb_strtolower($class_title)."</a></li>
{if \$real_object}<li><a href=\"{\$real_object->url()}\" target=\"_blank\">Посмотреть на сайте</a></li>{/if}
</ul>

<form style=\"padding: 0 0 10px 10px; margin: 0;\" name=\"goidrm\" onSubmit=\"document.location='/{$admin_path}s/'+forms['goidrm'].elements['id'].value+'/'; return false\">
ID:<input type=\"text\" name=\"id\" size=\"4\" />
<input type=\"submit\" class=\"search-submit\" value=\"Перейти\" />
</form>

</div>
";

	file_put_contents(str_replace('.sqlt', ".right-menu.$class_name.html", $sqlt_file), $html, FILE_APPEND);

$php = "<?php

class {$admin_class_name}s_config extends aviaport_admin_config
{
	function template_data()
	{
		return array_merge(parent::template_data(), array(
			'right_menu' => 'xfile:aviaport/admin/right-menu/$class_name.html',
		));
	}
}
";

	file_put_contents(str_replace('.sqlt', '.config.php', $sqlt_file), $php, FILE_APPEND);

	echo "\t'/{$admin_path}s/new/ => {$admin_class_name}s_edit',\n";
	echo "\t'(/{$admin_path}s/)(\d+)/ => {$admin_class_name}s_edit(2)',\n";

} // Конец генерации админки

	echo "Done!\n";
}
