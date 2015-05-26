<?php

class bors_form extends bors_object
{
	var $_attrs = array();
	var $_params;
	static $_current_form = NULL;

	function object() { return $this->id() ? $this->id() : $this->attr('object'); }

	static function current_form() { return self::$_current_form; }

	function append_attr($name, $value)
	{
		$this->_attrs[$name][] = $value;
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

			if((!$class_name || $class_name == 'this') && $calling_object)
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

		if($calling_object)
		{
			if(empty($form_class_name))
				$form_class_name = $calling_object->class_name();

			$form_object_id	= $calling_object->id();
		}

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

			if(!$uri && $object)
				$uri = $object->id();
		}

		if(!empty($ajax_validate))
		{
			$dom_form_id = 'form_'.md5(rand());

			jquery::css('/_bors-3rd/bower_components/validationEngine/css/validationEngine.jquery.css');
			jquery::plugin('/_bors-3rd/bower_components/validationEngine/js/languages/jquery.validationEngine-ru.js');
			jquery::plugin('/_bors-3rd/bower_components/validationEngine/js/jquery.validationEngine.js');
			jquery::on_ready("jQuery('#{$dom_form_id}').validationEngine()");

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

		//TODO: найти все использования и снести в пользу следующего
		if(!empty($dom_form_id))
			$html .= " id=\"$dom_form_id\"";

		if(!empty($dom_id))
			$html .= " id=\"$dom_id\"";

		$html .= ">\n";

		if($object)
			$object_fields = bors_lib_orm::fields($object);
		else
		{
			if($class_name)
				$object_fields = bors_lib_orm::fields(bors_foo($class_name));
			else
				$object_fields = array();
		}

//		PC::dump(bors_lib_orm::all_fields(bors_foo($class_name)), $class_name);

		if(array_key_exists('label', $params))
			$th = defval_ne($params, 'label', '-');
		elseif(array_key_exists('th', $params))
			$th = defval_ne($params, 'th', '-');
		else
			$th = false;

		$table_css_class = defval($params, 'table_css_class', $this->templater()->form_table_css());

		if($fields == 'auto')
			$fields = array_keys(array_filter($object_fields, create_function('$x', 'return defval($x, "is_admin_editable", false) || defval($x, "is_editable", true);')));

		if($th || !empty($fields))
		{
			$html .= "<table class=\"{$table_css_class}\">\n";
			$this->set_attr('has_form_table', true);
		}

		if($th && $th!='-')
			$html .= "<caption>{$th}</caption>\n";

		if($calling_object)
			$edit_properties_append = $calling_object->get('edit_properties', array());

		if(!empty($fields))
		{
			$this->set_attr('has_autofields', true);
			$labels = array();
			if(!is_array($fields))
				$fields = explode(',', $fields);

			$sections = array();
			$last_section = NULL;
			$have_sections = false;

			/*
				Возможные варианты
				'title',
				'type_id' => array('named_list' => 'alternative_list'),
				'xxx' => array('name' => 'property', ...)
			*/

			foreach($fields as $property_name => $append_data)
			{
				if(is_array($append_data))
				{
					if(is_numeric($property_name))
						$property_name = $data['name'];

					$data = array_merge(is_array($f = $object_fields[$property_name]) ? $f : array(), $append_data);
				}
				else
				{
					if(is_numeric($property_name))
						$property_name = $append_data;

					$data = $object_fields[$append_data];
				}

//				var_dump($property_name, $data);

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

				if(!defval($data, 'is_editable', true)  && !defval($data, 'is_admin_editable', false))
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

				if(!empty($data['class']) && !in_array($type, array('image', 'dropdown_id')))
				{
					if($type != 'radio')
						$type = 'dropdown';

					$class = $data['class'];
					$data['main_class'] = $class;
				}

				if(!empty($data['named_list']))
				{
					if(empty($data['type']) || $data['type'] == 'string')
						$type = 'dropdown';
					$class = $data['named_list'];
				}

				if(!empty($data['list']))
				{
					if(empty($data['type']) || $data['type'] == 'string')
						$type = 'dropdown';

					$class = $data['list'];
				}

				$property_name = defval($data, 'property', defval($data, 'name', $property_name));

				if($append = @$edit_properties_append[$property_name])
					$data = array_merge($data, $append);

				if(!$title)
					$title = $property_name;

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

				$edit_type = defval($data, 'edit_type', $type);

				$element = NULL;

				if(class_exists($edit_type) && ($element = new $edit_type) && ($element->is_form_element()))
				{
					$element->set_params($data);
					$element->set_form($this);
				}
				elseif(class_exists($edit_class = "bors_forms_".$edit_type) && ($element = new $edit_class) && ($element->is_form_element()))
				{
					$element->set_params($data);
					$element->set_form($this);
				}

				$is_hidden = false;
				if($element && $element->is_hidden())
					$is_hidden = true;
				elseif($type == 'bool' || $edit_type == 'hidden' || $edit_type == 'bool')
					$is_hidden = true;

				if(!$is_hidden)
					$html .= "\t<tr><th class=\"{$this->templater()->form_table_left_th_css()}\">{$title}</th><td>\n\t\t";

				$data['form'] = $this;
				$data['form_params'] = $params;
				if(empty($data['view']))
					$data['view'] = $params['view'];

//				echo '<xmp>'; var_dump($edit_type, $data); echo '</xmp>';

				if(!$element)
				{
					switch($edit_type)
					{
					case 'checkbox_list':
						$element = $this->element('checkbox_list');
						break;

					case 'string':
					case 'input':
					case 'int':
					case 'uint':
					case 'float':
						$element = $this->element('input');
						break;
					case 'hidden':
						$element= $this->element('hidden');
						break;
					case 'input_date':
					case 'date':
					case 'freedate':
						if($edit_type == 'freedate')
						{
							$data['can_drop'] = true;
							$data['is_integer'] = 8;
							$data['is_fuzzy'] = true;
						}
						if($args = popval($data, 'args'))
							$data = array_merge($data, $args);
						$html .= $this->element_html('date', $data);
						break;
					case 'utime': // UNIX_TIMESTAMP в UTC
						$data['name'] = popval($data, 'property');
						set_def($data, 'is_utc', true);
						set_def($data, 'time', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						if(popval($data, 'subtype') == 'simple')
							$html .= $this->element_html('date_simple', $data);
						else
							$html .= $this->element_html('date', $data);
						break;
					case 'bbcode':
					case 'text':
					case 'textarea':
					case 'markdown':
						$data['rows'] = defval($data, 'rows', $type_arg);
//						$html .= bors_forms_textarea::html($data, $this);
						$html .= $this->element_html('textarea', $data);
						break;
					case '3state':
						$data['list'] = ec('array("NULL"=>"", 1=>"Да", 0=>"Нет");');
						$data['is_int'] = true;
						require_once('function.dropdown.php');
						smarty_function_dropdown($data, $smarty);
						break;

					case 'radio':
						// http://admin2.aviaport.wrk.ru/newses/254690/
						if($list_class = defval($data, 'named_list'))
							$data['list'] = bors_foo($list_class)->named_list();
						else
							$data['list'] = base_list::make($list_class, array(), $data + array('non_empty' => true));
						$html .= $this->element_html('radio', $data);
						break;

					case 'combobox':
						$html .= $this->element_html('combobox', $data);
						break;

					case 'select2':
						$html .= $this->element_html('select2', $data);
						break;

					case 'timestamp_date_droppable':
						$data['name'] = popval($data, 'property');
						set_def($data, 'can_drop', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						if(popval($data, 'subtype') == 'simple')
							$html .= $this->element_html('date_simple', $data);
						else
							$html .= $this->element_html('date', $data);
						break;

					case 'time_simple':
						$data['name'] = popval($data, 'property');
						set_def($data, 'can_drop', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						$data['time'] = true;
						set_def($data, 'seconds', true);
						$html .= $this->element_html('date_simple', $data);
						break;
					case 'time_mixed':
						$data['name'] = popval($data, 'property');
						set_def($data, 'can_drop', true);
						if(!empty($data['args']))
							$data = array_merge($data, $data['args']);
						$data['time'] = true;
						set_def($data, 'seconds', true);
						$html .= $this->element_html('date_mixed', $data);
						break;

					case 'image':
//						$image = bors_load('bors_image', $data['value']);
//WTF?
//						if(!$image)
//							$image = $object;
//						$html .= $image->thumbnail($data['geometry'])->html_code();
						$html .= $this->element_html('image', $data);
						break;

					case 'bool':
						$data['label'] = $title;
						$labels[$property_name] = $data;
						break;

					case 'file_name':
						$data['file'] = $this->object();
						$html .= $this->element_html('file', $data);
						break;

					case 'module':
						set_def($data, 'object', $object);
						set_def($data, 'skip_title', true);
						$html .= bors_module::mod_html($data['module_class'], $data);
						break;

					default:
						if(class_exists($edit_type) && ($element = new $edit_type) && ($element->is_form_element()))
						{
							$element->set_params($data);
							$element->set_form($this);
//							$html .= $element->html();
						}
						elseif(class_exists($edit_class = "bors_forms_".$edit_type) && ($element = new $edit_class) && ($element->is_form_element()))
						{
							$element->set_params($data);
							$element->set_form($this);
//							$html .= $element->html();
						}
						else
							$html .= ec("Неизвестный тип '{$edit_type}' поля '{$property_name}'");
					}
				}

				if($element)
				{
					$element->set_params($data);
					$params = $element->params();
					if($pre = @$params['html_pre'])
						$html .= $pre;

					$html .= $element->html();

					$append_append = @$params['html_append'];
				}

				$html .= $html_append;
				$html .= "\t</td></tr>\n";
			}

			if($labels)
			{
				$html .= "<tr><th>Метки</th><td>";
//				require_once('function.checkbox.php');
				foreach($labels as $name => $data)
					$html .= $this->element_html('checkbox', $data);
				$html .= "</td></tr>\n";
			}
/*
			//TODO: глючит, видимо из-за конфликта параметра xrefs с авторедакторами, типа http://admin.aviaport.wrk.ru/_bors/admin/edit-smart/?object=aviaport_directory_airline_xref_airport__165
			if(
				($object && ($xrefs = $object->get('xrefs')))
				|| ($class_name && ($xrefs = bors_lib_object::get_foo($class_name, 'xrefs')))
			)
			{
				r($class_name);
				foreach($xrefs as $xref)
				{
					if(is_object($xref))
						$xref_obj = $xref;
					else
						$xref_obj = bors_foo($xref);

					$html .= "<tr><th>".$xref->class_title()."</th><td>";
//					$html .= $this->element_html('checkbox_list', array('xref' => $xref->class_name()));
					$html .= "</td></tr>";
				}
			}
*/
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


	static function factory()
	{
		return self::instance(true);
	}

	static function instance($new_form = false)
	{
		static $instance = NULL;
		if($new_form || !$instance)
		{
			$instance = new self(NULL);
			self::$_current_form = $instance;
		}

		return $instance;
	}

	function element($element_name)
	{
		$element_name = 'bors_forms_'.$element_name;
		$element = new $element_name;
		$element->set_form($this);
		return $element;
	}

	function element_html($element_name, $params = array())
	{
		$element = $this->element($element_name);
		$element->set_params($params);
		return $element->html();
	}

	function element_html_smart($params = array())
	{
		$field_type = defval($params, 'type');
		$form_type = defval_ne($params, 'form_type', $field_type);
		switch($form_type)
		{
			case 'text':
				$element_name = 'textarea';
				break;
			case 'string':
				$element_name = 'input';
				break;
			case 'radio':
				$element_name = 'radio';
				break;
			default:
				bors_throw("Unknown element type '$form_type' (type={$params['type']})");
		}

		extract($params);

		if(preg_match)

		set_def($params, 'th', @$title);

		return $this->element_html($element_name, $params);
	}

	function templater()
	{
		if($t = $this->attr('form_template'))
			return $t;

		$view = $this->_params['view'];

		if(!$view)
			$view = bors()->main_object();

		if(!$view)
			bors_throw("Undefined form view");

		$tpl_name = $view->layout()->forms_template_class();
		$form_template = bors_load($tpl_name, NULL);
		if($form_template)
			return $this->set_attr('form_template', $form_template);

		bors_throw("Undefined form template {$tpl_name}");
	}

	function model_class()
	{
		// Такая этажерка — для совместимости
		if($mc = @$this->_params['model_class'])
			return $mc;

		if($mc = @$this->_params['main_class'])
			return $mc;

		if($mc = @$this->_params['class'])
			return $mc;

		if($x = @$this->_params['object'])
			return $x->class_name();

		return NULL;
	}
}
