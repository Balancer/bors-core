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
			'class' => $this->model_class()
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
					$html = bors_forms_checkbox_list::html($data, $form);
					break;
				case 'combobox':
					$html = bors_forms_combobox::html($data, $form);
					break;
				case 'multiselect':
					$html = bors_forms_multiselect::html($data, $form);
					break;
				case 'radio':
					$html = bors_forms_radio::html($data, $form);
					break;
				case 'time_auto':
					set_def($data, 'time_on_post', true);
					set_def($data, 'time', true);
					set_def($data, 'seconds', true);
					$html = bors_forms_date_simple::html($data, $form);
					break;
				case 'textarea':
				case 'bbcode':
//					$html = bors_forms_textarea::html($data, $form);
					$html = $form->element_html('textarea', $data);
					break;
				case 'string':
				case 'keywords_string':
				default:
					$html = bors_forms_input::html($data, $form);
					break;

//					echo "Unknown type '{$data['edit_type']}'/'{$data['type']}' for property '{$name}'";
//					var_dump($data);
//					$html = NULL;
//					break;
			}

			$result_html[] = $html;
		}

//		var_dump($result_html);

		$result_html[] = bors_forms_submit::html(array('value' => ec('Сохранить')), $form);

		$result_html[] = $form->html_close();

		return join("\n", $result_html);
	}
}
