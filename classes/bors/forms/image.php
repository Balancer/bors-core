<?php

class bors_forms_image extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		// http://admin.aviaport.ru/digest/stories/253/edit/
		$image_name_field = preg_replace('/_id$/', '', defval($params, 'name', 'image'));
		$image_id_field   = defval($params, 'image_id_field', $image_name_field.'_id');
		$image_class_name_field   = defval($params, 'image_class_name_field', $image_name_field.'_class_name');
		$image_class_id_field   = defval($params, 'image_class_id_field', $image_name_field.'_class_id');

		$image_class_name = defval($params, 'class_name', config('image_class_name', 'bors_image')); //TODO: убедиться в унификации конфига

		$obj = $form->object();

		$html = '';

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
			$html .=  "<tr><th>{$th}</th><td>";

		if($obj && ($image = $obj->$image_name_field()))
		{
			$html .=  "<a href=\"{$image->admin()->url()}\">".$image->thumbnail(defval_ne($params, 'geo', '200x'))->html_code()."</a><br/>\n";
			$html .=  "<input type=\"checkbox\" name=\"file_{$image_name_field}_delete_do\" />&nbsp;".ec('Удалить изображение')."<br/>\n";
		}
		else
		{
			$html .=  "<img src=\"/_bors/i/image-placeholder-200x150.png\" /><br/>\n";
		}

		$html .=  "<input type=\"file\" name=\"{$image_name_field}\"";
		foreach(explode(' ', 'class style') as $p)
			if(!empty($$p))
				$html .=  " $p=\"{$$p}\"";
		$html .=  " /><br/>\n";


//		$form->append_attr('file_vars', $name);
//		$fls[] = "{$image_name_field}={$image_class_name_field}/{$image_class_id_field}({$image_class_name}/{$image_id_field})";

		$form->append_attr('file_vars', "{$image_name_field}={$image_class_name_field}/{$image_class_id_field}({$image_class_name}/{$image_id_field})");

		$html .=  "<input type=\"hidden\" name=\"{$image_name_field}___upload_dir\" value=\"".defval($params, 'upload_dir', config('upload_dir').'/images')."\"/>\n";
		$html .=  "<input type=\"hidden\" name=\"{$image_name_field}___no_subdirs\" value=\"".defval($params, 'no_subdirs', config('no_subdirs'))."\"/>\n";
		if($th)
			$html .=  "</td></tr>\n";

		return $html;
	}
}
