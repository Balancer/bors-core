<?php

class base_config extends base_empty
{
	function __construct(&$object)
	{
		parent::__construct($object);

		foreach($this->config_data() as $key => $value)
			$object->set($key, $value, false);

		foreach($this->config_defaults() as $key => $value)
			if(!$object->is_set($key))
				$object->set($key, $value, false);
	}

	function template_init()
	{
		$object = $this->id();
		foreach($this->template_data() as $key => $value)
		{
			if(strpos($key, '['))
				$object->add_template_data_array($key, $value);
			elseif(preg_match('/^\[(\w+)\]\+/', $key, $m))
				$object->merge_template_data_array($m[1], $value);
			else
				$object->add_template_data($key, $value);
		}

		foreach($this->template_data_array() as $key => $value)
			$object->add_template_data_array($key, $value);
	}

	function config_defaults() { return array(); }
	function config_data() { return array(); }
	function template_data_array() { return array(); }

	function template_data()
	{
		$data = array(
			'success_message' => session_var('success_message'),
			'notice_message'  => session_var('notice_message'),
			'error_message'   => session_var('error_message'),
		);

		set_session_var('success_message', NULL);
		set_session_var('notice_message', NULL);
		set_session_var('error_message', NULL);

		if(($post_js = session_var('javascript_post_append')))
		{
//			print_d($post_js);
			foreach($post_js as $js)
				$this->id()->add_template_data_array('javascript', $js);

			set_session_var('javascript_post_append', NULL);
		}

		return $data;
	}

	function object() { return $this->id(); }
}
