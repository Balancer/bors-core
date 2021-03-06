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

		// Если нужно, добавляем заголовок поля
		$html .= $this->label_html();
		$image = NULL;

		if($obj)
		{
			$image = $obj->get($image_name_field);
			if(!is_object($image) || !$image->get('object_type') == 'image')
				$image = NULL;

			if(!$image && $obj->get('object_type') == 'image')
				$image = $obj;

			$thumb = NULL;

			if(is_object($image))
				$thumb = $image->thumbnail(defval_ne($params, 'geo', '200x'));

			if($thumb && is_object($thumb))
			{
				$html .=  "<a href=\"{$image->admin()->url()}\">".$thumb->html_code()."</a><br/>\n";
				$html .=  "<input type=\"checkbox\" name=\"file_{$image_name_field}_delete_do\" />&nbsp;".ec('Удалить изображение')."<br/>\n";
			}
			elseif($image)
					$html = "Ошибка создания превью для изображения {$image->debug_title()}";
		}

		if(!$image)
			$html .=  "<img src=\"/_bors/i/image-placeholder-200x150.png\" /><br/>\n";

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

//		if($form->get('has_form_table'))
//			$html .=  "</td></tr>\n";

		return $html;
	}
}
