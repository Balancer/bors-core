<?php

class bors_forms_element
{
	static function value(&$params, &$form)
	{
		$name = defval($params, 'name');
		$def  = defval($params, 'def');
		$value = defval($params, 'value');

		$object = $form->object();

		if(!array_key_exists('value', $params))
		{
			if(($object && ($object->id() || !$object->storage_engine())))
				$value = preg_match('!^\w+$!', $name) ? (isset($value)?$value : ($object?$object->$name():NULL)) : '';
			elseif($form->attr('is_calling') && ($calling_object = $form->attr('calling_object')))
				$value = $calling_object->get($name);
			else
				$value = NULL;
		}

		if(empty($value) && !$form->attr('no_session_vars'))
			$value = session_var("form_value_{$name}");

		set_session_var("form_value_{$name}", NULL);

		if(!isset($value) && isset($def))
			$value = $def;

		if(!empty($params['do_not_show_zero']) && $value == 0)
			$value = '';

		return $value;
	}
}
