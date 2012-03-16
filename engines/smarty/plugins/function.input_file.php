<?php

/**
	Передаваемые параметры:
		• name — метод базового объекта, возвращающий объект file
		• value — объект файла (bors_attach или подобный)
		• upload_dir
		• no_subdirs
		• link_type = cross | parent
*/

function smarty_function_input_file($params, &$smarty)
{
	$name = defval($params, 'name', 'attach');
	$file_class_name = defval($params, 'file_class', config('attach_class', 'bors_attach'));
	$file_id_field   = defval($params, 'id_field', $name.'_id');

	$obj = bors_templates_smarty::get_var($smarty, 'form');

	// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
	if($th = defval($params, 'th'))
		echo "<tr><th>{$th}</th><td>";

	if($obj)
	{
		$file = defval($params, 'value');

		if(!$file)
			$file = $obj->$name();

		if($file)
		{
			echo "<a href=\"{$file->admin()->url()}\">".$file->html_code()."</a><br/>\n";
			echo "<input type=\"checkbox\" name=\"file_{$name}_delete_do\" />&nbsp;".ec('Удалить файл')."<br/>\n";
		}
	}

	echo "<input type=\"file\" name=\"{$name}\"";
	foreach(explode(' ', 'class style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";
	echo " /><br/>\n";
	$fls = base_object::template_data('form_file_vars');
	$fls[] = "{$name}={$file_class_name}({$file_id_field})";
	base_object::add_template_data('form_file_vars', $fls);

	echo "<input type=\"hidden\" name=\"{$name}___upload_dir\" value=\"".defval($params, 'upload_dir', config('upload_dir').'/files')."\"/>\n";
	echo "<input type=\"hidden\" name=\"{$name}___no_subdirs\" value=\"".defval($params, 'no_subdirs', config('no_subdirs'))."\"/>\n";
	echo "<input type=\"hidden\" name=\"{$name}___link_type\" value=\"" .defval($params, 'link_type', 'cross')."\"/>\n";
	echo "<input type=\"hidden\" name=\"{$name}___parent\" value=\"" .object_property(defval($params, 'object', $obj), 'internal_uri_ascii')."\"/>\n";
	if($th)
		echo "</td></tr>\n";
}
