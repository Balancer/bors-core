<?php

class bors_admin_newstep extends bors_admin_page
{
	function foo() { return bors_foo($this->model_class()); }

	function title()
	{
		return $this->foo()->class_title();
	}

	function form_html()
	{
		$result_html = array();

		$form = new bors_form(NULL);
		bors_form::$_current_form = $form;
		$form->set_attr('has_form_table', true);

		$result_html[] = $form->html_open(array(
			'th' => '-',
			'class' => $this->model_class(),
			'main_class' => $this->model_class(),
			'model_class' => $this->model_class(),
		));

		foreach($this->form_fields() as $name => $args)
		{
			if(is_numeric($name))
			{
				$name = $args;
				$args = array();
			}

			$f = bors_lib_orm::parse_property($this->model_class(), $name);
			if(!$f)
				$f = array();

			$data = array_merge($f, $args);
			$data['th'] = defval($data, 'title', $name);

			if(empty($data['edit_type']))
				$data['edit_type'] = $data['type'];

/*
			if($data['type'] == 'uint' && !empty($data['class']))
			{
				$data['type'] = ''
			}
*/
//			var_dump($name, $data);

			set_def($data, 'name', $name);

			switch($data['edit_type'])
			{
				case 'checkbox_list':
					$html = $form->element_html('checkbox_list', $data);
					break;
				case 'combobox':
					$html = $form->element_html('combobox', $data);
					break;
				case 'multiselect':
					$html = $form->element_html('multiselect', $data);
					break;
				case 'radio':
					$html = $form->element_html('radio', $data);
					break;
				case 'time_auto':
					set_def($data, 'time_on_post', true);
					set_def($data, 'time', true);
					set_def($data, 'seconds', true);
					$html = $form->element_html('date_simple', $data);
					break;
				case 'textarea':
				case 'bbcode':
					$html = $form->element_html('textarea', $data);
					break;
				case 'string':
				case 'keywords_string':
				default:
					$html = $form->element_html('input', $data);
					break;

//					echo "Unknown type '{$data['edit_type']}'/'{$data['type']}' for property '{$name}'";
//					var_dump($data);
//					$html = NULL;
//					break;
			}

			$result_html[] = $html;
		}

//		var_dump($result_html);

//		$result_html[] = bors_forms_submit::html(array('value' => ec('Сохранить')), $form);
		$result_html[] = $form->element_html('submit', array('value' => ec('Сохранить')));

		$result_html[] = $form->html_close();

		return join("\n", $result_html);
	}
}
