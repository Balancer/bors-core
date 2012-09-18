<?php

class base_config extends bors_object_simple
{
	function target_configure()
	{
		$object = $this->id();

		// Внимание. Установка параметров через атрибуты, а не через данные,
		// так как данные будут перезаписаны при загрузке через ORM.
		foreach(array_merge($this->config_data(), $this->object_data()) as $key => $value)
			$object->set_attr($key, $value);

		foreach($this->config_defaults() as $key => $value)
			if(!$object->is_set($key))
				$object->set_attr($key, $value);
	}

	function template_init()
	{
		$object = $this->id();
		foreach(array_merge($this->template_data(), $this->page_data()) as $key => $value)
		{
			if(strpos($key, '['))
				$object->add_template_data_array($key, $value);
			elseif(preg_match('/^\[(\w+)\]\+/', $key, $m))
				bors_page::merge_template_data_array($m[1], $value);
			else
				$object->add_template_data($key, $value);
		}

		foreach($this->template_data_array() as $key => $value)
			$object->add_template_data_array($key, $value);
	}

	function config_defaults() { return array(); }
	function config_data() { return array(); }
	function object_data() { return array(); }
	function template_data_array() { return array(); }

	function template_data()
	{
		$data = array(
			'success_message' => session_var('success_message'),
			'notice_message'  => session_var('notice_message'),
			'error_message'   => session_var('error_message'),
		);

		if(($post_js = session_var('javascript_post_append')))
		{
			foreach($post_js as $js)
				$this->id()->add_template_data_array('javascript', $js);

			set_session_var('javascript_post_append', NULL);
		}

		if(property_exists($this, 'template_data'))
			$data = array_merge($this->template_data, $data);

		return $data;
	}

	function page_data() { return array(); }

	function object() { return $this->id(); }
}
