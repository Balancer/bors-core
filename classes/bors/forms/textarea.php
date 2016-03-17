<?php

class bors_forms_textarea extends bors_forms_element
{
	function html()
	{
		$params = $this->params();

		if(!empty($params['property']))
			$params['name'] = $params['property'];

		$form = $this->form();

		extract($params);

		$object = $form->object();
		$value = $this->value();

		if(empty($rows))
			$rows = 7;

		$class = explode(' ', $this->css());
		if(in_array($name, explode(',', session_var('error_fields'))))
			$class[] = $this->css_error();

		if(empty($cols))
			$cols = 60;

		$versioning = object_property($obj, 'versioning_properties', array());
		if(array_key_exists($name, $versioning))
		{
			$has_versioning = true;
			$previous = $versioning[$name];
			$class[] = 'yellow_box';
		}
		else
			$has_versioning = false;

		$html = '';

		// Если нужно, добавляем заголовок поля
		$html .= $this->label_html();

		static $tmp_id = 0;

		if(!empty($limit))
		{
			if(!$id)
				$id = 'tmp_id_'.($tmp_id++);

			jquery::on_ready("
				$('#$id').keyup(function() {
					t=\$(this);
					l=t.val().length;
				    if(l > $limit)
			            t.val(t.val().substr(0, $limit));
					t.css('background-color', l >= $limit ? '#f99' : 'inherit');
				});");
		}

		if(@$type == 'bbcode')
		{
			if(!$id)
				$id = 'tmp_id_'.($tmp_id++);

			jquery_markitup::appear('#'.$id);
		}
		elseif(@$type == 'markdown')
		{
			$class[] = 'markdownEditor';
//			jquery_markitup_markdown::appear('.markdownEditor');
		}

		$class = join(' ', $class);

		$html .= "<textarea name=\"$name\"";
		foreach(explode(' ', 'class id style rows cols') as $p)
			if(!empty($$p))
				$html .= " $p=\"{$$p}\"";

		$html .= ">".htmlspecialchars($value)."</textarea>\n";

		if($has_versioning)
			$html .= "<br/><small>".ec("Исходное значение: ").$previous."</small>\n";

		if($append)
			$html .= "<br/>".$append;

//		if($form->get('has_form_table'))
//			$html .= "</td></tr>\n";

		return $html;
	}
}
