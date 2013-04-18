<?php

class bors_forms_element
{
	private $params, $form;
	function set_params($params) { return $this->params = $params; }
	function set_form($form) { return $this->form = $form; }
	function params() { return $this->params; }
	function form() { return $this->form; }

	function value($param_name = 'value')
	{
		$form = $this->form;
		$params = $this->params;

		$name = defval($params, 'name');
		$def  = defval($params, 'def');
		$value = defval($params, $param_name);

		$object = object_property($form, 'object');

		if(!array_key_exists($param_name, $params))
		{
			if(($object && ($object->id() || !$object->storage_engine())))
				$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($object?$object->$name():NULL)) : '';
			elseif($form->attr('is_calling') && ($calling_object = $form->attr('calling_object')))
				$value = $calling_object->get($name);
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

	function css()
	{
		if($css = defval($this->params, 'css'))
			return $css;
		if($css = defval($this->params, 'css_class'))
			return $css;
		// Для совместимости. Устарело. В новых проектах не использовать.
		if($css = defval($this->params, 'class'))
			return $css;

		$element_name = $this->element_name();
		$method = $element_name . '_css';
		return $this->form()->templater()->call($method);
	}

	function css_error()
	{
		$element_name = $this->element_name();
		$method = $element_name . '_css_error';
		return $this->form()->templater()->call($method);
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
}
