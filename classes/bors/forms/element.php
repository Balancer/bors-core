<?php

class bors_forms_element
{
	private $params, $form;
	function set_params($params) { return $this->params = $params; }
	function set_form($form) { return $this->form = $form; }
	function params() { return $this->params; }
	function param($name, $default=NULL) { return array_key_exists($name, $this->params) ? $this->params[$name] : $default; }
	function form() { return $this->form; }
	function is_form_element() { return true; }
	function is_hidden() { return false; }

	function name() { return $this->param('name'); }

	function value($param_name = 'value')
	{
		$form = $this->form;
		$params = $this->params;

		$name = defval($params, 'name');

		$object = object_property($form, 'object');

		if($val = bors_global::gvar('override_form_values.'.$name))
			return $val;

		$model_class = $form->model_class();
		if($model_class == 'NULL')
			$model_class = NULL;

		if(!$object && $model_class && preg_match('/^\w+$/', $name))
		{
			$obj = bors_foo($model_class);
			$new_val = $obj->get($name."_new");
			if($new_val)
				return $new_val;
		}

		$def  = defval($params, 'def');
		$value = defval($params, $param_name);

		if(!array_key_exists($param_name, $params))
		{
			if(($object && ($object->id() || !$object->storage_engine())))
				$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($object?$object->$name():NULL)) : '';
			elseif($form->attr('is_calling') && ($calling_object = $form->attr('calling_object')))
				$value = $calling_object->get($name);
			elseif($get_value = bors()->request()->data('new_form_'.$name))
				$value = $get_value;
			else
				$value = NULL;
		}

		if(!isset($value) && $form && !$form->attr('no_session_vars'))
			$value = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!isset($value) && isset($def))
			$value = $def;

		if(!empty($params['do_not_show_zero']) && $value == 0)
			$value = '';

		return $value;
	}

	function element_name() { return str_replace('bors_forms_', '', get_class($this)); }

	function property_name() { return $this->params['name']; }

	function label()
	{
		$params = $this->params();

		$label = defval($params, 'label');
		if(!$label)
			$label = defval($params, 'th'); // legacy

		if($label == 'def')
		{
			$x = bors_lib_orm::parse_property($this->form()->attr('class_name'), $params['name']);
			$label = $x['title'];
		}

		return $label;
	}

	function use_tab()
	{
		$params = $this->params();

		return $this->form()->get('has_form_table') && empty($params['no_tab']);
	}

	// Возвращаем заголовок поля в виде табличного блока, если нужно
	function label_html()
	{
		$label = $this->label();

		$comment = defval($this->params, 'comment');

		if($comment)
			$comment = "<br/><small>$comment</small>";

		if($label && $this->use_tab())
			return "<tr><th>{$label}{$comment}</th><td>";

		return '';
	}

	// Возвращаем заголовок поля в виде табличного блока, если нужно
	function label_html2()
	{
		$label = $this->label();

		$comment = defval($this->params, 'comment');

		$label = preg_replace('!^(.+?) // (.+)$!', "$1<br/><small>$2</small>", $label);

		if($comment)
			$label .= "<br/><small>$comment</small>";

		$label_tpl = $this->form()->templater()->get('form_element_label_html');
		return sprintf($label_tpl, $label);
	}

	function css($element_name = NULL)
	{
		if($css = defval($this->params, 'css'))
			return $css;
		if($css = defval($this->params, 'css_class'))
			return $css;
		// Для совместимости. Устарело. В новых проектах не использовать.
		if($css = defval($this->params, 'class'))
			return $css;

		if(!$element_name)
			$element_name = $this->element_name();

		$method = $element_name . '_css';
		return $this->form()->templater()->get($method);
	}

	function css_error()
	{
		$element_name = $this->element_name();
		$method = $element_name . '_css_error';
		return $this->form()->templater()->get($method);
	}

	function list_class()
	{
		if($lc = $this->params['list_class'])
			return $lc;

		if($lc = $this->params['main_class'])
			return $lc;

		if($lc = $this->params['class'])
			return $lc;

		return NULL;
	}


	function html()
	{
		$params = $this->params();

		if(!empty($params['class']))
		{
			$class_name = $params['class'];
			// To avoid infinity cycle if html method not defined
			unset($params['class']);
			return $this->form()->element_html($class_name, $params);
		}

		throw new Exception(sprintf(_("Not defined method 'html' for element '%s'"), get_class($this)));
	}
}
