<?php

class bors_form extends bors_object
{
	var $_attrs = array();
	var $_params;
	static $_current_form = NULL;

	function object() { return $this->id() ? $this->id() : $this->attr('object'); }

	function append_attr($name, $value)
	{
		$form = $this ? $this : self::$_current_form;

		$form->_attrs[$name][] = $value;
	}

	function hidden_attr($name)
	{
		if($val = $this->attr($name))
			if($val != 'NULL')
				return "<input type=\"hidden\" name=\"$name\" value=\"".htmlspecialchars($val)."\" />\n";

		return '';
	}

	function hidden_array($name)
	{
		$form = $this ? $this : self::$_current_form;
		if($vars = popval($form->_attrs, $name, array()))
			return "<input type=\"hidden\" name=\"".str_replace('form_', '', $name)."\" value=\"".join(',', array_unique(array_filter($vars)))."\" />\n";

		return '';
	}

	/**
		Входные параметры:
			class_name	— имя класса объекта, с которым работает форма (устаревшее: class, name)
			object_id	— ID объекта формы (устаревшее id)
			object		— готовый объект формы, при наличии приоритет над class_name/object_id
			fields		— редактируемые поля формы
			calling_object	— объект, отображающий форму. Для навигации и т.п.(?)
			css_class	— класс CSS-стиля формы
			dom_form_id		— DOM ID формы (уст: form_id)
			style		— CSS-стиль формы
	*/

	function html_open($params)
	{
		$this->_params = $params;
		extract($params);

		if(empty($name))
			$name = @$class;

		// Класс страницы с формой, но не объект, редактируемый формой.
		// Для навигации, передачи прав доступа(?) и т.п.
		if(empty($calling_object))
			$calling_object = bors()->main_object();

		if(empty($dom_form_id))
			$dom_form_id = @$form_id;

		if(empty($object) && @$class == 'this') // obsolete
			$object = bors()->main_object();

		if(empty($object) && is_object(@$form)) // obsolete
			$object = $form;

		if(empty($object) && is_object($this->id())) // obsolete
			$object = $this->id();

		if(empty($object))
		{
			$object = NULL;

			if(empty($class_name)) // obsolete
				$class_name = @$class;
			if(empty($class_name)) // obsolete
				$class_name = @$name;
			if(empty($object_id)) // obsolete
				$object_id = @$id;

			if(!$class_name || $class_name == 'this')
			{
				$class_name = $calling_object->class_name();
				$object_id	= $calling_object->id();
				$is_calling = true;
			}
			else
				$is_calling = false;

			if(empty($object_id) || $object_id == 'NULL')
				$object_id = NULL;

			if($class_name == 'NULL')
				$class_name = NULL;

			if($class_name && $object_id)
				$object = bors_load($class_name, $object_id);
		}
		elseif($class_name != 'NULL')
		{
			$class_name	= $object->class_name();
			$object_id	= $object->id();
		}

		if(empty($form_class_name))
			$form_class_name = $calling_object->class_name();

		$form_object_id	= $calling_object->id();

		$this->set_attr('class_name', $class_name);
		$this->set_attr('object', $object);
		$this->set_attr('calling_object', $calling_object);
		$this->set_attr('is_calling', $is_calling);

		if(!isset($uri))
		{
			if($calling_object)
				$uri = bors()->server()->portize($calling_object->called_url());
			else
				$uri = NULL;

			if(!$uri)
				$uri = $object->id();
		}

		if(!empty($ajax_validate))
		{
			$dom_form_id = 'form_'.md5(rand());

			template_jquery();
			template_jquery_plugin_css('formvalidator/css/validationEngine.jquery.css');
			template_jquery_plugin('formvalidator/js/jquery.validationEngine-ru.js');
			template_jquery_plugin('formvalidator/js/jquery.validationEngine.js');
			template_js("jQuery(document).ready(function() { jQuery('#{$dom_form_id}').validationEngine()})");

			$this->set_attr('ajax_validate', $ajax_validate);
		}

		if(!empty($no_session_vars))
			$this->set_attr('no_session_vars', true);

		if(empty($css_class))
			$css_class = @$class;

		if(empty($method))
			$method = 'post';

		$this->set_attr('method', $method);

		if(empty($action))
			$action = $uri;

		if($action == 'this')
			$action = $GLOBALS['main_uri'];

		if($action == 'target')
			$action = $object ? $object->url() : $GLOBALS['main_uri'];

		if(!empty($calling_object))
		{
			set_session_var('post_message', $calling_object->get('post_message'));
			set_session_var('post_message_link_text', $calling_object->get('post_message_link_text'));
			set_session_var('post_message_link_url', $calling_object->get('post_message_link_url'));
		}

		foreach(explode(' ', 'form_class_name form_object_id class_name object_id uri ref act inframe subaction') as $x)
			if(!empty($$x))
				$this->set_attr($x, $$x);

		$html = "<form enctype=\"multipart/form-data\"";

		foreach(explode(' ', 'action method name style enctype onclick onsubmit target') as $p)
			if(!empty($$p) && ($p != 'name' || $$p != 'NULL'))
				$html .= " $p=\"{$$p}\"";

		foreach(array('css_class' => 'class') as $v => $p)
			if(!empty($$v) && ($$v != 'NULL'))
				$html .= " $p=\"{$$v}\"";

		if(!empty($dom_form_id))
			$html .= " id=\"$dom_form_id\"";

		$html .= ">\n";

		if($object)
			$object_fields = bors_lib_orm::fields($object);
		else
		{
			if($class_name)
				$object_fields = bors_lib_orm::fields(new $class_name(NULL));
			else
				$object_fields = array();
		}

		if(array_key_exists('th', $params))
			$th = defval_ne($params, 'th', '-');
		else
			$th = false;

		$table_css_class = defval($params, 'table_css_class', 'btab w100p');

		if($fields == 'auto')
			$fields = array_keys(array_filter($object_fields, create_function('$x', 'return defval($x, "is_editable", true);')));

		if($th || !empty($fields))
		{
			$html .= "<table class=\"{$table_css_class}\">\n";
			$this->set_attr('has_form_table', true);
		}

		if($th && $th!='-')
			$html .= "<caption>{$th}</caption>\n";

		if(!empty($fields))
		{
			$this->set_attr('has_autofields', true);
			$labels = array();
			if(!is_array($fields))
				$fields = explode(',', $fields);

			$sections = array();
			$last_section = NULL;
			$have_sections = false;

			foreach($fields as $property_name => $data)
			{
				if(is_array($data))
					$property_name = $data['name'];
				else
					$data = $object_fields[$data];


				if($current_section = defval($data, 'form_section'))
					$have_sections = true;

				$el = array('section' => $current_section, 'data' => $data, 'property' => $property_name);
				$inserted = false;
				if($current_section !== $last_section && $current_section != '')
				{
					for($i = count($sections)-1; $i>=0; $i--)
					{
						if($sections[$i]['section'] == $current_section)
						{
							// ('н', 'н', '', 'ч') ищем 'н' => 1
							$sections = array_merge(array_slice($sections, 0, $i+1),
								array($el),
								array_slice($sections, $i+1, count($sections) - $i - 1));
							$inserted = true;
							$last_section = $current_section;
							break;
						}
					}
					if($inserted)
						break;

				}

				$sections[] = $el;
				$last_section = $current_section;
			}

//			print_dd($sections);
//			foreach($fields as $property_name => $data)

			$last_section = NULL;

			foreach($sections as $x)
			{
				$section_name = $x['section'];
				$data = $x['data'];
				$property_name = $x['property'];

				if($have_sections && $last_section != $section_name)
					$html .= "<tr><th class=\"subcaption\" colspan=\"2\">".($section_name?$section_name:'&nbsp;')."</th></tr>\n";

				$last_section = $section_name;
//				echo "prop_name = ",var_dump($property_name), "data=",var_dump($data)."<br/>\n";

				if(!$data)
					foreach($object_fields as $f)
						if($f['name'] == $property_name)
							$data = $f;

				if(!defval($data, 'is_editable', true))
					continue;

				$type = $data['type'];
				$type_arg = NULL;

				if(preg_match('/^(\w+):(\w+)$/', $type, $m))
				{
					$type = $data['type'] = $m[1];
					$type_arg = $m[2];
				}

				$title = $data['title'];
				if($comment = @$data['comment'])
					$title .="<br/><small class=\"gray\">{$comment}</small>";

				if(!empty($data['class']) && $type != 'image')
				{
					if($type != 'radio')
						$type = 'dropdown';

					$class = $data['class'];
				}

				if(!empty($data['named_list']))
				{
					if(empty($data['type']) || $data['type'] == 'string')
						$type = 'dropdown';
					$class = $data['named_list'];
				}

				$property_name = defval($data, 'property', $data['name']);

				if(!$title)
					$title = $property_name;

				if($type != 'bool')
					$html .= "\t<tr><th class=\"w33p\">{$title}</th><td>\n\t\t";

				if(!empty($data['arg']))
					$data['value'] = object_property_args($object, $property_name, array($data['arg']));
//				else
//					$data['value'] = object_property($object, $property_name);

				$def_w = preg_match('/(w\d+p)/', $table_css_class, $m) ? $m[1] : NULL;
				$data['class'] = defval($data, 'form_css_class', $def_w);

				if(!empty($data['property']))
					$data['name'] = $data['property'];

				$type = defval($data, 'form_type', $type);

//				echo "<b>property=$property_name</b>, title=$title, type=$type, data=".print_dd($data, true).", field=".print_dd($field, true)."<br/>\n";
				if(!empty($property_name))
					$data['name'] = $property_name;

				$html_append = '';

				switch($type)
				{
					case 'string':
					case 'input':
					case 'int':
					case 'uint':
					case 'float':
						$html .= bors_forms_input::html($data, $this);
						break;
					case 'input_date':
					case 'date':
					case 'freedate':
						if($type == 'freedate')
						{
							$data['can_drop'] = true;
							$data['is_integer'] = 8;
						}
						if($args = popval($data, 'args'))
							$data = array_merge($data, $args);
						$html .= bors_forms_date::html($data, $this);
						break;
					case 'utime': // UNIX_TIMESTAMP в UTC
						$data['name'] = popval($data, 'property');
						set_def($data, 'is_utc', true);
						set_def($data, 'time', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						if(popval($data, 'subtype') == 'simple')
							$html .= bors_forms_date_simple::html($data, $this);
						else
						{
							$html .= bors_forms_date::html($data, $this);
						}
						break;
					case 'bbcode':
					case 'text':
					case 'textarea':
						$data['rows'] = defval($data, 'rows', $type_arg);
						$html .= bors_forms_textarea::html($data, $this);
						break;
					case '3state':
						$data['list'] = ec('array("NULL"=>"", 1=>"Да", 0=>"Нет");');
						$data['is_int'] = true;
						require_once('function.dropdown.php');
						smarty_function_dropdown($data, $smarty);
						break;

					case 'radio':
						$data['list'] = base_list::make($class, array(), $data + array('non_empty' => true));
						$html .= bors_forms_radio::html($data, $this);
						break;

					case 'dropdown':
					case 'dropdown_id':
					case 'dropdown_edit':
						if($type == 'dropdown_id')
						{
							$saveclass = @$data['class'];
							$data['class'] = 'wa';
							$data['input_name'] = '_'.$data['name'];
							if($chars = defval($data, 'form_chars'))
								$data['maxlength'] = $data['size'] = $chars;
							$this->append_attr('override_fields', $data['name']);
							$html .= "ID:";
							$html .= bors_forms_input::html($data, $this);
							template_jquery();
							template_js("\$(function() {
	\$('select[name={$data['name']}]').change(function(){
		\$('input[name={$data['input_name']}]').val(\$(this).val())
	});
});");
							unset($data['maxlength'], $data['size']);
							$data['class'] = $saveclass;
						}

						if($type == 'dropdown_edit')
						{
							$saveclass = @$data['class'];
							$data['class'] = 'w50p';
							$data['input_name'] = '_'.$data['name'];
							$this->append_attr('override_fields', $data['name']);
							$html_append = bors_forms_input::html($data, $this);
							template_jquery();
							template_js("\$(function() {
	\$('select[name={$data['name']}]').change(function(){
		\$('input[name={$data['input_name']}]').val(\$(this).val())
	});
});");
						}

						if(array_key_exists('named_list', $data))
						{
							if(preg_match('/^(\w+):(\w+)$/', $data['named_list'], $m))
							{
								$list_class_name = $m[1];
								$id = $m[2];
							}
							else
							{
								$list_class_name = $data['named_list'];
								$id = NULL;
							}
							$list = new $list_class_name($id);	//TODO: статический вызов тут не прокатит, пока не появится повсеместный PHP-5.3.3.
							$data['list'] = $list->named_list();
						}
						else
							$data['list'] = base_list::make($class, array(), $data);

						// Смешанная проверка для тестирования на http://ucrm.wrk.ru/admin/persons/9/
						if($data['is_int'] = defval($data, 'is_int', true))
							foreach($data['list'] as $k => $v)
								$data['is_int'] &= !$k || is_numeric($k);

						$html .= bors_forms_dropdown::html($data, $this);
						break;

					case 'timestamp_date_droppable':
						$data['name'] = popval($data, 'property');
						set_def($data, 'can_drop', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						if(popval($data, 'subtype') == 'simple')
							$html .= bors_forms_date_simple::html($data, $this);
						else
							$html .= bors_forms_date::html($data, $this);
						break;

					case 'image':
//						$image = bors_load('bors_image', $data['value']);
//WTF?
//						if(!$image)
//							$image = $object;
//						$html .= $image->thumbnail($data['geometry'])->html_code();
						$html .= bors_forms_image::html($data, $this);
						break;

					case 'bool':
						$data['label'] = $title;
						$labels[$property_name] = $data;
						break;

					case 'file_name':
						$data['file'] = $this->object();
						$html .= bors_forms_file::html($data, $this);
						break;

					default:
						$html .= ec("Неизвестный тип '{$type}'");
//						print_dd($data);
//						echo defval($data, 'value');
//						echo defval($data, 'value');
				}

				$html .= $html_append;
				$html .= "\t</td></tr>\n";
			}

			if($labels)
			{
				$html .= "<tr><th>Метки</th><td>";
//				require_once('function.checkbox.php');
				foreach($labels as $name => $data)
					$html .= bors_forms_checkbox::html($data, $this);
//					smarty_function_checkbox($data, $smarty);
				$html .= "</td></tr>\n";
			}

			if(
				($object && ($xrefs = $object->get('xrefs')))
				|| ($class_name && ($xrefs = bors_lib_object::get_foo($class_name, 'xrefs')))
			)
			{
				foreach($xrefs as $xref)
				{
					$html .= "<tr><th>".call_user_func(array($xref, 'class_title'))."</th><td>";
					$html .= bors_forms_checkbox_list::html(array(
						'xref' => $xref,
//						'delim' => ' ',
					), $this);
					$html .= "</td></tr>";
				}
			}
		}

		return $html;
	}

	function html_close()
	{
		$html = '';
		// === Закрытие формы ===
		if($this->attr('has_form_table'))
			$html .= "</table>\n";

//		if($act == 'skip_all')
//		{
//			unset($uri);
//		}

		if(empty($this->_params['class_name']))
		{
//			$this->_params['class_name'] = $name;
			$go2 = $uri;
		}
		else
			$go2 = 'newpage_admin';

		if($class_name && !$id)
			$go2 = 'newpage_admin';

		if(empty($this->_params['go']))
			$this->_params['go'] = $go2;

		foreach(explode(' ', 'go class_name form_class_name') as $name)
			$$name = $this->attr($name);

		foreach(explode(' ', 'form_class_name class_name object_id uri ref act inframe subaction') as $name)
			$html .= $this->hidden_attr($name);

		foreach(explode(' ', 'time_vars file_vars linked_targets override_fields saver_prepare_classes') as $name)
			$html .= $this->hidden_array($name);

		if($this->attr('method') != 'get')
			foreach(explode(' ', 'checkboxes checkboxes_list') as $name)
				$html .= $this->hidden_array($name);

		if(!$this->attr('form_have_go') && $go)
			$html .= "<input type=\"hidden\" name=\"go\" value=\"$go\" />\n";

		$html .= "</form>\n";
		set_session_var('error_fields', NULL);

		return $html;
	}
}
