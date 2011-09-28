<?php

function smarty_function_input_image($params, &$smarty)
{
	echo bors_forms_image::html($params);
	return;

	//TODO: снести всё до конца, если отрефакторенный код работает
	extract($params);
	$image_name_field = defval($params, 'image', 'image');
	$image_id_field   = defval($params, 'image_id_field', $image_name_field.'_id');
	$image_class_name_field   = defval($params, 'image_class_name_field', $image_name_field.'_class_name');
	$image_class_id_field   = defval($params, 'image_class_id_field', $image_name_field.'_class_id');

	$image_class_name = defval($params, 'class_name', config('image_class_name', 'bors_image')); //TODO: убедиться в унификации конфига

	$obj = $smarty->get_template_vars('form');

	// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
	if($th = defval($params, 'th'))
		echo "<tr><th>{$th}</th><td>";

	if($obj && ($image = $obj->$image_name_field()))
	{
		echo "<a href=\"{$image->admin()->url()}\">".$image->thumbnail(defval_ne($params, 'geo', '200x'))->html_code()."</a><br/>\n";
		echo "<input type=\"checkbox\" name=\"file_{$image_name_field}_delete_do\" />&nbsp;".ec('Удалить изображение')."<br/>\n";
	}
	else
	{
		echo "<img src=\"/_bors/i/image-placeholder-200x150.png\" /><br/>\n";
	}

	echo "<input type=\"file\" name=\"{$image_name_field}\"";
	foreach(explode(' ', 'class style') as $p)
		if(!empty($$p))
			echo " $p=\"{$$p}\"";
	echo " /><br/>\n";

	$fls = base_object::template_data('form_file_vars');
	$fls[] = "{$image_name_field}={$image_class_name_field}/{$image_class_id_field}({$image_class_name}/{$image_id_field})";
	base_object::add_template_data('form_file_vars', $fls);

	echo "<input type=\"hidden\" name=\"{$image_name_field}___upload_dir\" value=\"".defval($params, 'upload_dir', config('upload_dir').'/images')."\"/>\n";
	echo "<input type=\"hidden\" name=\"{$image_name_field}___no_subdirs\" value=\"".defval($params, 'no_subdirs', config('no_subdirs'))."\"/>\n";
	if($th)
		echo "</td></tr>\n";
}
