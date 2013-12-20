<?php

class bors_forms_file extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$obj = $form->object();

		$html = "";

		// Если указано, то это заголовок строки таблицы: <tr><th>{$th}</th><td>...code...</td></tr>
		if($th = defval($params, 'th'))
		{
			$html .= "<tr><th>{$th}</th><td>";
			if(empty($style))
				$style = "width: 99%";
		}

		$html .= "<input type=\"file\" name=\"$name\"";

		foreach(explode(' ', 'class style') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		if($size = @$params['max_size'])
		{
			$html .= ' data-max-size="'.$size.'"';
			static $first = true;
			if($first)
				jquery::on_ready('
$("form").submit(function(){
	is_ok = true
	$("input[type=file][data-max-size]").each(function(){
		if(typeof this.files[0] !== "undefined") {
			var maxSize = parseInt($(this).data("max-size"),10),
			size = this.files[0].size;
//			alert("max="+maxSize+", size="+size)
			if(size > maxSize) {
				alert("Файл слишком большой")
				return is_ok = false
			}
		}
	})
	return is_ok
});
			');
			$first = false;
		}

		$html .= " />\n";

		if(!empty($file))
			$html .= $file->html();

		if(!empty($id_field))
			$name = "$name=".(empty($class_name_field) ? '' : $class_name_field)."($id_field)";

		$form->append_attr('file_vars', $name);

		if($th)
			$result .=  "</td></tr>\n";

		return $html;
	}
}
